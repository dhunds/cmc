<?php
include('connection.php');
include_once('classes/class.notification.php');
$objNotification = new Notification();

$poolid = $_POST['poolid'];
$MemberNumber = trim($_POST['MemberNumber']);
$memberUserId = trim($_POST['memberUserId']);


$OwnerNumber = '';
$OwnerName = '';
$ClubName = '';
$OwnerDeviceToken = '';
$OwnerPlatform = '';

$stmtO = $con->query("SELECT * FROM userpoolsmaster WHERE PoolId = '$poolid'");
$OwnerExists = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

if ($OwnerExists > 0) {
    while ($row = $stmtO->fetch()) {
        $OwnerNumber = trim((string)$row['OwnerNumber']);
        $ownerUserId = trim((string)$row['ownerUserId']);

        $ClubName = $row['PoolName'];
    }

    if ($OwnerNumber != '') {
        $stmtI = $con->query("SELECT * FROM registeredusers WHERE ownerUserId = '$ownerUserId' and PushNotification != 'off'");
        $OwnRegExists = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

        if ($OwnRegExists > 0) {
            while ($row = $stmtI->fetch()) {
                $OwnerName = $row['FullName'];
                $OwnerDeviceToken = $row['DeviceToken'];
                $OwnerPlatform = $row['Platform'];
            }
        }
    }
}

$stmtF = $con->query("SELECT * FROM registeredusers WHERE userId = '$memberUserId' and PushNotification != 'off'");
$FriendExists = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

if ($FriendExists > 0) {

    while ($row = $stmtF->fetch()) {

        $MemberName = $row['FullName'];
    }
    $Message = $MemberName . " has left the group " . $ClubName;
    $gcm_arrayF = array();
    $apns_arrayF = array();

    if ($OwnerPlatform == "A") {
        $gcm_arrayF[] = $OwnerDeviceToken;
    } else {
        $apns_arrayF[] = $OwnerDeviceToken;
    }

    $NotificationType = "PoolId_Left";
    $manFriend = "INSERT INTO notifications(NotificationType, SentMemberName, SentMemberNumber, ReceiveMemberName, ReceiveMemberNumber, Message, PoolId, DateTime) VALUES ('$NotificationType','$MemberName','$MemberNumber','$OwnerName','$OwnerNumber','$Message','$poolid',now())";
    $manstmtFriend = $con->prepare($manFriend);
    $manresFriend = $manstmtFriend->execute();

    if ($manresFriend === true) {
        $notificationId =  $con->lastInsertId();
        $body = array('gcmText' => $Message, 'pushfrom' => 'PoolId_', 'notificationId' => $notificationId);
        if (count($gcm_arrayF) > 0) {
            $objNotification->setVariables($gcm_arrayF, $body);
            $res = $objNotification->sendGCMNotification();
        }

        if (count($apns_arrayF) > 0) {
            $objNotification->setVariables($apns_arrayF, $body);
            $objNotification->sendIOSNotification();
        }
    }

    $sql21 = "DELETE FROM userpoolsslave WHERE TRIM(PoolId) = '$poolid' AND TRIM(memberUserId) = '$memberUserId'";
    $stmt21 = $con->prepare($sql21);
    $res21 = $stmt21->execute();

    if ($res21 === true) {
        echo "SUCCESS";
    } else {
        echo "FAILURE";
    }
} else {
    echo "FAILURE";
}
