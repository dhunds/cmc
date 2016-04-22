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
	$string = "'".$amount ."''". $fee ."''". $merchantname ."''". $mid ."''". $orderid ."''". $receivercell ."''". $sendercell ."''". $token ."'";

    $checksum = hash_hmac('sha256', $string, API_SECRET);

	$result = mobikwikTransfers($amount, $fee, $merchantname, $mid, $orderid, $receivercell, $sendercell, $token, $checksum);
    
    if ($result === FALSE) {
        $paymentFailed = 1;
        $curlFailed = 1;
        
    } else {
        $resp = simplexml_load_string($result);
        
        if ($resp->status =='SUCCESS'){
            $cell = '0091'.substr(trim($_POST['cell']), -10);
            $sql = "INSERT INTO driverPayments(mobileNumberFrom, mobileNumberTo, transactionId, amount, transactionDate, cabId, status) VALUES ('" . $sendercell . "', '" . $resp->receivercell . "', '" . $resp->refId . "', '" . $resp->amount . "', now(), '" . $_POST['cabId'] . "', '" . $resp->status . "')";
            $nStmt = $con->prepare($sql);
            $nStmt->execute();

            $jsonResp = array('status'=>(string)$resp->status, 'statuscode'=>(string)$resp->statuscode, 'statusdescription'=>(string)$resp->statusdescription, 'amount'=>(string)$resp->amount, 'orderid'=>(string)$resp->orderid, 'refId'=>(string)$resp->refId, 'checksum'=>(string)$resp->checksum);
        } else {
        	$jsonResp = array('status'=>(string)$resp->status, 'statuscode'=>(string)$resp->statuscode, 'statusdescription'=>(string)$resp->statusdescription);

            $paymentFailed = 1;
        }
    }

    $sql = "UPDATE acceptedrequest set hasBoarded = 1 where CabId = '" . $_POST['cabId'] . "'";
    $stmt = $con->prepare($sql12);
    $res = $stmt12->execute();

    if ($paymentFailed) {

    // Notification Settings
        $stmt = $con->query("SELECT MobileNumber, FullName, DeviceToken, Platform FROM registeredusers WHERE MobileNumber = '0091".$_POST['sendercell']."'");
        $senderExists = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

        if ($senderExists) {
            $row = $stmt->fetch();
            
            $senderName = $row['FullName'];
            $senderMobileNumber = $row['MobileNumber'];
            $senderPlatform = $row['Platform'];
            $senderDeviceToken = $row['DeviceToken'];
        }

        $stmt = $con->query("SELECT MobileNumber, FullName, DeviceToken, Platform FROM registeredusers WHERE MobileNumber = '0091".$_POST['receivercell']."'");
        $receiverExists = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

        if ($receiverExists) {
            $row = $stmt->fetch();
            
            $receiverName = $row['FullName'];
            $receiverMobileNumber = $row['MobileNumber'];
            $receiverPlatform = $row['Platform'];
            $receiverDeviceToken = $row['DeviceToken'];
        }

        $NotificationType = "Peer_transfer_failed";
        $Message = "Payment Failed, please settle in cash";

        $paramsSender = array('NotificationType' => $NotificationType, 'SentMemberName' => 'system', 'SentMemberNumber' => '', 'ReceiveMemberName'=>$senderName, 'ReceiveMemberNumber'=>$senderMobileNumber, 'Message'=>$Message, 'CabId'=>$_POST['cabId'], 'DateTime'=>'now()');

        $paramsReceiver = array('NotificationType' => $NotificationType, 'SentMemberName' => 'system', 'SentMemberNumber' => '', 'ReceiveMemberName'=>$receiverName, 'ReceiveMemberNumber'=>$receiverMobileNumber, 'Message'=>$Message, 'CabId'=>$_POST['cabId'], 'DateTime'=>'now()');

        $notificationId = $objNotification->logNotification($params);

        // Send Sender Notification
        $body = array('gcmText' => $Message, 'pushfrom' => $NotificationType, 'notificationId' => $notificationId);
        if ($senderPlatform == 'A') {
            $gcm_array[] = $senderDeviceToken;
            $objNotification->setVariables($gcm_array, $body);
            $res = $objNotification->sendGCMNotification();
        } else {
            $apns_array[] = $senderDeviceToken;
            $objNotification->setVariables($apns_array, $body);
            $objNotification->sendIOSNotification();
        }

        //Send Receiver Notification
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
    // End Notification Settings

        if ($curlFailed) {
            http_response_code(500);
            header('Content-Type: application/json');
            echo '{"status":"failed", "message":"An Error occured, Please try later."}';
            exit;
        } else {
            http_response_code(200);
            header('Content-Type: application/json');
            echo json_encode($jsonResp);
            exit;
        }

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
