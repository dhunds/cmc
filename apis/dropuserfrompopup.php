<?php
include('connection.php');
include_once('classes/class.notification.php');
$objNotification = new Notification();

$CabId = $_POST['CabId'];
$OwnerName = $_POST['OwnerName'];
$OwnerNumber = $_POST['OwnerNumber'];
$MemberName = $_POST['MemberName'];
$MemberNumber = $_POST['MemberNumber'];
$ownerUserId = $_POST['ownerUserId'];
$memberUserId = $_POST['memberUserId'];
$Message = $_POST['Message'];

$sql2 = "DELETE FROM cabmembers WHERE (CabId = '$CabId' AND memberUserId = $memberUserId)";
$stmt2 = $con->prepare($sql2);
$res2 = $stmt2->execute();

if ($res2 === true) {
    $sql3 = "DELETE FROM acceptedrequest WHERE (CabId = '$CabId' AND memberUserId = $memberUserId)";
    $stmt3 = $con->prepare($sql3);
    $res3 = $stmt3->execute();

    $upsql2 = "UPDATE cabopen SET RemainingSeats= (RemainingSeats+1) WHERE CabId = '$CabId'";
    $upstmt2 = $con->prepare($upsql2);
    $upres2 = $upstmt2->execute();

    $NotificationType = "CabId_Dropped";

    $params = array('NotificationType' => $NotificationType, 'SentMemberName' => $OwnerName, 'SentMemberNumber' => $OwnerName, 'ReceiveMemberName' => $MemberName, 'ReceiveMemberNumber' => $OwnerNumber, 'Message' => $Message, 'CabId' => $CabId, 'DateTime' => 'now()');
    $notificationId = $objNotification->logNotification($params);

    if ($res3 === true) {
        $stmt = $con->query("SELECT * FROM registeredusers WHERE PushNotification != 'off' and userId = '$memberUserId'");
        //$no_of_users = $stmt->rowCount();
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
} else {
    echo "error";
}
