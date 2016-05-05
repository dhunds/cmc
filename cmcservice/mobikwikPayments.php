<?php
include('connection.php');
include('includes/functions.php');
include_once('classes/class.notification.php');
$objNotification = new Notification();

$paymentFailed = 0;
$curlFailed = 0;
$error = 0;

$error = checkPostForBlank (array('sendercell', 'receivercell', 'amount', 'fee', 'orderid', 'token', 'mid', 'merchantname'));

if (!$error) {
    $fare           = $_POST['amount'];
    $sendercell     = $_POST['sendercell'];
    $receivercell   = $_POST['receivercell'];
    $fee            = $_POST['fee'];
    $orderid        = $_POST['orderid'];
    $token          = $_POST['token'];
    $merchantname   = $_POST['merchantname'];
    $mid            = $_POST['mid'];

    $serviceCharge  = 5;
    $serviceTax  = ceil((14/100) * 5);
    $totalDeductible = $serviceCharge + $serviceTax;

    $amount = $fare - $totalDeductible;

	$result = mobikwikTransfers($amount, $fee, $merchantname, $mid, $orderid, $receivercell, $sendercell, $token);
    
    if ($result === FALSE) {
        $paymentFailed = 1;
        $curlFailed = 1;
        
    } else {
        $resp = simplexml_load_string($result);
        
        if ($resp->status =='SUCCESS'){
            $cell = '0091'.substr(trim($_POST['cell']), -10);
            $sql = "INSERT INTO paymentLogs(mobileNumberFrom, mobileNumberTo, transactionId, amount, transactionDate, cabId, status) VALUES ('" . $sendercell . "', '" . $resp->receivercell . "', '" . $resp->refId . "', '" . $resp->amount . "', now(), '" . $_POST['cabId'] . "', '" . $resp->status . "')";
            $nStmt = $con->prepare($sql);
            $nStmt->execute();

        // Start Merchant Transaction
            $merchantOrderid = microtime();

            $merchantTransferResp = mobikwikTransfers($totalDeductible, $fee, $merchantname, $mid, $merchantOrderid, $receivercell, $sendercell, $token);

            $merchantResp = simplexml_load_string($merchantTransferResp);

            if ($merchantResp->status =='SUCCESS') {
                $sql = "INSERT INTO paymentLogs (mobileNumberFrom, mobileNumberTo, transactionId, amount, transactionDate, cabId, status, serviceCharge, serviceTax) VALUES ('" . $sendercell . "', '" . MERCHANT_NUMBER . "', '" . $merchantResp->refId . "', '" . $merchantResp->amount . "', now(), '" . $_POST['cabId'] . "', '" . $merchantResp->status . "', '" . $serviceCharge . "', '" . $serviceTax . "')";
                $nStmt = $con->prepare($sql);
                $nStmt->execute();
            }

        // End Merchant Transaction

            $jsonResp = array('status'=>"success", 'statuscode'=>(string)$resp->statuscode, 'statusdescription'=>(string)$resp->statusdescription, 'amount'=>(string)$resp->amount, 'orderid'=>(string)$resp->orderid, 'refId'=>(string)$resp->refId, 'checksum'=>(string)$resp->checksum);

        //Send Notification
            $stmt = $con->query("SELECT MobileNumber, FullName, DeviceToken, Platform FROM registeredusers WHERE MobileNumber = '0091".$_POST['receivercell']."'");
            $receiverExists = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

            if ($receiverExists) {
                $row = $stmt->fetch();

                $receiverName = $row['FullName'];
                $receiverMobileNumber = $row['MobileNumber'];
                $receiverPlatform = $row['Platform'];
                $receiverDeviceToken = $row['DeviceToken'];

                $NotificationType = "Payment_Received";
                $Message = "Payment Failed, please settle in cash";

                $paramsReceiver = array('NotificationType' => $NotificationType, 'SentMemberName' => 'system', 'SentMemberNumber' => '', 'ReceiveMemberName'=>$receiverName, 'ReceiveMemberNumber'=>$receiverMobileNumber, 'Message'=>$Message, 'CabId'=>$_POST['cabId'], 'DateTime'=>'now()');

                $notificationId = $objNotification->logNotification($params);

                $body = array('gcmText' => $Message, 'pushfrom' => $NotificationType, 'notificationId' => $notificationId);

                if ($receiverPlatform == 'A') {
                    $gcm_array = [];
                    $gcm_array[] = $receiverDeviceToken;
                    $objNotification->setVariables($gcm_array, $body);
                    $res = $objNotification->sendGCMNotification();
                } else {
                    $apns_array = [];
                    $apns_array[] = $receiverDeviceToken;
                    $objNotification->setVariables($apns_array, $body);
                    $objNotification->sendIOSNotification();
                }
            }
        // End Sending Notification


        // Send Payment Email to member
            $stmt = $con->query("SELECT FullName, Email FROM registeredusers WHERE MobileNumber = '0091".$_POST['sendercell']."'");
            $senderExists = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

            if ($senderExists) {
                $row = $stmt->fetch();

                if($row['Email'] !='') {
                    require_once 'mail.php';

                    $stmt = $con->query("SELECT Distance, perKmCharge, FromShortName, ToShortName FROM registeredusers WHERE CabId = '".$_POST['cabId']."'");
                    $cabDetail = $stmt->fetch();
                    $ride  = $cabDetail['FromShortName'].' To '.$cabDetail['ToShortName'];

                    sendPaymentMailMember ($row['FullName'], $row['Email'], $ride, $cabDetail['Distance'], $cabDetail['perKmCharge'],  $fare );

                }
            }
        // End Sending Payment Email to member

        } else {
            $paymentFailed = 1;
        }
    }

    $sql = "UPDATE acceptedrequest set hasBoarded = 1 where CabId = '" . $_POST['cabId'] . "'";
    $stmt = $con->prepare($sql12);
    $res = $stmt12->execute();

    if ($curlFailed || $paymentFailed) {
        http_response_code(500);
        header('Content-Type: application/json');
        echo '{"status":"failed", "message":"Payment Failed, please settle Rs. "'.$fare.'" in cash"}';
        exit;
    } else {
        http_response_code(200);
        header('Content-Type: application/json');
        echo json_encode($jsonResp);
        exit;
    }

} else {
    http_response_code(500);
    header('Content-Type: application/json');
    echo '{"status":"failed", "message":"Invalid Params."}';
    exit;
}
