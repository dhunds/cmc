<?php
include('connection.php');
include_once('classes/class.notification.php');
$objNotification = new Notification();

$poolid = $_POST['poolid'];
$MemberNumber = trim($_POST['usernumber']);
$userId = $_POST['userId'];

$OwnerNumber = '';
$OwnerName = '';
$ClubName = '';

$stmtO = $con->query("SELECT * FROM userpoolsmaster WHERE PoolId = '$poolid'");
$OwnerExists = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

if ($OwnerExists > 0) {
    while ($row = $stmtO->fetch()) {
        $OwnerNumber = trim((string)$row['OwnerNumber']);
        $ClubName = $row['PoolName'];
        $ownerId = $row['ownerUserId'];;
    }

    if ($OwnerNumber != '') {
        $stmtI = $con->query("SELECT * FROM registeredusers WHERE userId = '$ownerId' and PushNotification != 'off'");
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
$stmtF = $con->query("SELECT * FROM registeredusers WHERE userId = '$userId' and PushNotification != 'off'");
$FriendExists = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

if ($FriendExists > 0) {
    while ($row = $stmtF->fetch()) {
        $MemberDeviceToken = $row['DeviceToken'];
        $MemberPlatform = $row['Platform'];
        $MemberName = $row['FullName'];
    }
    $Message = $OwnerName . " has removed you from the group " . $ClubName;
    $gcm_arrayF = array();
    $apns_arrayF = array();
    if ($MemberPlatform == "A") {
        $gcm_arrayF[] = $MemberDeviceToken;
    } else {
        $apns_arrayF[] = $MemberDeviceToken;
    }

    $NotificationType = "PoolId_Removed";
    $manFriend = "INSERT INTO notifications(NotificationType, SentMemberName, SentMemberNumber, ReceiveMemberName, ReceiveMemberNumber, Message, PoolId, DateTime, sentMemberUserId, receivedMemberUserId) VALUES ('$NotificationType','$OwnerName','$OwnerNumber','$MemberName','$MemberNumber','$Message','$poolid',now(), $ownerId, $userId)";
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

    $sql21 = "DELETE FROM userpoolsslave WHERE TRIM(PoolId) = '$poolid' AND TRIM(memberUserId) = '$userId'";
    $stmt21 = $con->prepare($sql21);
    $res21 = $stmt21->execute();

    if ($res21 === true) {
        echo "SUCCESS";
    } else {
        echo "FAILURE";
    }
} else {
    $sql21 = "DELETE FROM userpoolsslave WHERE TRIM(PoolId) = '$poolid' AND TRIM(memberUserId) = '$userId'";
    $stmt21 = $con->prepare($sql21);
    $res21 = $stmt21->execute();

    if ($res21 === true) {
        echo "SUCCESS";
    } else {
        echo "FAILURE";
    }
}
?>