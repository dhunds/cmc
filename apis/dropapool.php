<?php
include('connection.php');
include_once('classes/class.notification.php');
include_once('includes/functions.php');
$objNotification = new Notification();

$CabId = $_POST['CabId'];
$SentMemberName = $_POST['SentMemberName'];
$SentMemberNumber = $_POST['SentMemberNumber'];
$ReceiveMemberName = $_POST['ReceiveMemberName'];
$ReceiveMemberNumber = $_POST['ReceiveMemberNumber'];
$Message = $_POST['Message'];

$sql2 = "UPDATE cabmembers SET DropStatus = 'Yes' WHERE CabId = '$CabId' AND MemberNumber = $SentMemberNumber";
$stmt2 = $con->prepare($sql2);
$res2 = $stmt2->execute();

if ($res2 === true) {
    $sql2345 = "UPDATE acceptedrequest SET Status= 'Dropped' WHERE CabId='$CabId' AND Trim(MemberNumber)='$SentMemberNumber'";
    $stmt2345 = $con->prepare($sql2345);
    $res2345 = $stmt2345->execute();

    $sql987 = "SELECT * FROM cabopen WHERE CabId = '$CabId'";
    $result987 = $con->query($sql987);

    if ($result987 !== false) {
        $cols = $result987->columnCount();

        foreach ($result987 as $row987) {
            $TotalSeats = $row987['Seats'];
            $RemainingSeats = $row987['RemainingSeats'];
        }
    }

    if ($TotalSeats == $RemainingSeats) {

    } else {
        $updatedRemainingSeats = $RemainingSeats + 1;
        $upsql2 = "UPDATE cabopen SET RemainingSeats= '$updatedRemainingSeats' WHERE CabId = '$CabId'";
        $upstmt2 = $con->prepare($upsql2);
        $upres2 = $upstmt2->execute();
    }
    $NotificationType = "CabId_Left";
    $params = array('NotificationType' => $NotificationType, 'SentMemberName' => $SentMemberName, 'SentMemberNumber' => $SentMemberNumber, 'ReceiveMemberName'=>$ReceiveMemberName, 'ReceiveMemberNumber'=>$ReceiveMemberNumber, 'Message'=>$Message, 'CabId'=>$CabId, 'DateTime'=>'now()');
    $notificationId = $objNotification->logNotification($params);

    $stmt = $con->query("SELECT * FROM registeredusers WHERE MobileNumber = '$ReceiveMemberNumber' and PushNotification != 'off'");
    $no_of_users = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

    if ($no_of_users > 0) {
        while ($row = $stmt->fetch()) {
            $gcm_array[] = $row['DeviceToken'];
        }

        $body = array('gcmText' => $Message, 'pushfrom' => 'CabId_', 'CabId' => $CabId, 'notificationId' => $notificationId);
        $objNotification->setVariables($gcm_array, $body);
        $objNotification->sendGCMNotification();

        echo "pool successfully drop";
    } else {
        echo "no one in database";
    }
} else {
    echo "error";
}
?>