<?php
include('connection.php');
include('includes/functions.php');
include_once('classes/class.notification.php');
$objNotification = new Notification();

$paymentFailed = 0;
$curlFailed = 0;
$error = 0;

$error = checkPostForBlank (array('sendercell', 'receivercell', 'amount', 'fee'));

if (!$error) {
    $amount             = $_POST['amount'];
    $sendercell         = $_POST['sendercell'];
    $receivercell       = $_POST['receivercell'];
    $fee                = $_POST['fee'];
    $orderid            = microtime();
    $merchantname       = MERCHANT_NAME;
    $mid                = MID;
    $merchantTransfer   = 1;
    $paidTo             = 1;

    $sendercellNew = '0091'.$sendercell;
    $receivercellNew = '0091'.$receivercell;
    $token          = getMobikwikToken($sendercellNew);

    $serviceCharge  = 5;
    $serviceTax  = (14/100) * 5;
    $totalDeductible = ceil($serviceCharge + $serviceTax);

    $stmt = $con->query("SELECT mobileNumber FROM cabOwners");
    $ownNumbers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (in_array($receivercellNew, $ownNumbers)){
        $merchantTransfer = 1;
        $paidTo = 3;
    }

    if ($merchantTransfer) {
        $result = mobikwikTransfers($amount, $fee, $merchantname, $mid, $orderid, MERCHANT_NUMBER, $sendercell, $token);
    } else {
        $result = mobikwikTransfers($amount, $fee, $merchantname, $mid, $orderid, $receivercell, $sendercell, $token);
    }

    
    if ($result === FALSE) {
        $paymentFailed = 1;
        $curlFailed = 1;
        
    } else {
        $resp = simplexml_load_string($result);
        
        if ($resp->status =='SUCCESS'){

            mobikwikTokenRegenerate($sendercellNew);

            $sql = "INSERT INTO paymentLogs(mobileNumberFrom, mobileNumberTo, transactionId, amount, transactionDate, paidTo, cabId, status, serviceCharge, serviceTax) VALUES ('" . $sendercellNew . "', '" . $receivercellNew . "', '" . $resp->refId . "', '" . $resp->amount . "', now(), $paidTo, '" . $_POST['cabId'] . "', '" . $resp->status . "', '" . $serviceCharge . "', '" . $serviceTax . "')";
            $nStmt = $con->prepare($sql);
            $nStmt->execute();

        // Start Merchant Transaction
            if ($merchantTransfer) {

                $merchantOrderid = microtime() . $_POST['cabId'];
                $merchantToken = getMobikwikToken($receivercellNew);
                $merchantTransferResp = mobikwikTransfers($totalDeductible, $fee, $merchantname, $mid, $merchantOrderid, MERCHANT_NUMBER, $receivercell, $merchantToken);

                $merchantResp = simplexml_load_string($merchantTransferResp);

                if ($merchantResp->status == 'SUCCESS') {

                    mobikwikTokenRegenerate($receivercellNew);

                    $sql = "INSERT INTO paymentLogs (mobileNumberFrom, mobileNumberTo, transactionId, amount, transactionDate, paidTo, cabId, status, serviceCharge, serviceTax) VALUES ('" . $receivercellNew . "', '" . MERCHANT_NUMBER . "', '" . $merchantResp->refId . "', '" . $merchantResp->amount . "', now(), 2, '" . $_POST['cabId'] . "', '" . $merchantResp->status . "', '" . $serviceCharge . "', '" . $serviceTax . "')";
                    $nStmt = $con->prepare($sql);
                    $nStmt->execute();
                }
            }

        // End Merchant Transaction

            $jsonResp = array('status'=>"success", 'statuscode'=>(string)$resp->statuscode, 'statusdescription'=>(string)$resp->statusdescription, 'amount'=>(string)$resp->amount, 'orderid'=>(string)$resp->orderid, 'refId'=>(string)$resp->refId, 'checksum'=>(string)$resp->checksum, 'message'=>'Payment Received');

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
                $Message = "Payment received.";

                $paramsReceiver = array('NotificationType' => $NotificationType, 'SentMemberName' => 'system', 'SentMemberNumber' => '', 'ReceiveMemberName'=>$receiverName, 'ReceiveMemberNumber'=>$receiverMobileNumber, 'Message'=>$Message, 'CabId'=>$_POST['cabId'], 'DateTime'=>'now()');

                $notificationId = $objNotification->logNotification($paramsReceiver);

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

                    $stmt = $con->query("SELECT Distance, perKmCharge, FromShortName, ToShortName FROM cabopen WHERE CabId = '".$_POST['cabId']."'");
                    $cabDetail = $stmt->fetch();
                    $ride  = $cabDetail['FromShortName'].' To '.$cabDetail['ToShortName'];

                    sendPaymentMailMember ($row['FullName'], $row['Email'], $ride, $cabDetail['distance'], $cabDetail['perKmCharge'],  $amount );

                }
            }
        // End Sending Payment Email to member

        } else {
            $paymentFailed = 1;
        }
    }

    $sql = "UPDATE acceptedrequest set hasBoarded = 1 where CabId = '" . $_POST['cabId'] . "'";
    $stmt = $con->prepare($sql);
    $res = $stmt->execute();

    if ($curlFailed || $paymentFailed) {
        $jsonResp = array('status'=>'fail', 'statuscode'=>(string)$resp->statuscode, 'statusdescription'=>(string)$resp->statusdescription, 'message'=>'Payment failed, please settle Rs.'.$amount.' in cash');
    }

    http_response_code(200);
    header('Content-Type: application/json');
    echo json_encode($jsonResp);
    exit;

} else {
    http_response_code(200);
    header('Content-Type: application/json');
    echo '{"status":"failed", "message":"Invalid Params."}';
    exit;
}
