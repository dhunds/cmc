<?php
include('connection.php');
include('includes/functions.php');
include_once('classes/class.notification.php');
$objNotification = new Notification();

if (isset($_POST['cabId']) && $_POST['cabId'] !='') {

    $sql = "UPDATE cabopen set CabStatus = 'I' where CabId = '" . $_POST['cabId'] . "'";
    $stmt = $con->prepare($sql);
    $res = $stmt->execute();

    $stmt = $con->query("SELECT * FROM cabopen WHERE CabId = '" . $_POST['cabId'] . "'");
    $cabDetail = $stmt->fetch();

    $stmt = $con->query("SELECT a.FullName, a.MobileNumber, a.DeviceToken, b.distance FROM registeredusers a, acceptedrequest b WHERE  a.MobileNumber = b.MemberNumber AND b.CabId='".$_POST['cabId']."' AND b.hasBoarded=1");

    $membersJoined = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

    if ($membersJoined) {

        while ($row = $stmt->fetch()) {

            $NotificationType = "Cab_Rating";
            $Message = "Trip from " . $cabDetail['FromShortName'] . " to  " . $cabDetail['ToShortName'] . " completed. Help us improve by rating the cab service.";

            $params = array('NotificationType' => $NotificationType, 'SentMemberName' => 'system', 'SentMemberNumber' => '', 'ReceiveMemberName'=>$row['FullName'], 'ReceiveMemberNumber'=>(string)$row['MobileNumber'], 'Message'=>$Message, 'CabId'=>$cabDetail['CabId'], 'DateTime'=>'now()');

            sendOwnerRatingNotification ($objNotification, $params, $row['DeviceToken'], $row['Platform'], $row['PushNotification']);

        // Send Mail
            $owner = getUserByMobileNumber($cabDetail['MobileNumber']);

            if($owner['Email'] !='') {
                require_once 'mail.php';

                $ride  = $cabDetail['FromShortName'].' To '.$cabDetail['ToShortName'];
                sendPaymentMailOwner($owner['FullName'], $owner['Email'], $cabDetail['CabId'], $ride, $row['distance']);
            }
        }

        $sql = "UPDATE cabopen set ratenotificationsend = '1' where CabId = '".$cabDetail['CabId']."'";
        $stmt = $con->prepare($sql);
        $stmt->execute();
    }

    http_response_code(200);
    header('Content-Type: application/json');
    echo '{status:"success", message:"Updated sucessfully"}';
    exit;

} else {
    http_response_code(200);
    header('Content-Type: application/json');
    echo '{"status":"failed", "message":"Invalid Params."}';
    exit;
}
