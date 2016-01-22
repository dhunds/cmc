<?php
include('connection.php');
include_once('classes/class.notification.php');

$objNotification = new Notification();
$poolid = $_POST['poolid'];

$OwnerNumber = '';
$OwnerName = '';
$ClubName = '';
$stmtO = $con->query("SELECT * FROM userpoolsmaster WHERE PoolId = '$poolid'");
$OwnerExists = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

if ($OwnerExists > 0) {
    while ($row = $stmtO->fetch()) {
        $OwnerNumber = trim((string)$row['OwnerNumber']);
        $ClubName = $row['PoolName'];
    }

    if ($OwnerNumber != '') {
        $stmtI = $con->query("SELECT * FROM registeredusers WHERE MobileNumber = '$OwnerNumber' and PushNotification != 'off'");
        $OwnRegExists = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

        if ($OwnRegExists > 0) {
            while ($row = $stmtI->fetch()) {
                $OwnerName = $row['FullName'];
            }
        }
    }
}

$stmtC = $con->query("SELECT * FROM userpoolsslave WHERE PoolId = '$poolid'");
$MembersExists = $con->query("SELECT FOUND_ROWS()")->fetchColumn();
$IsSuccess = false;

if ($MembersExists > 0) {
    $con->beginTransaction();
    while ($row = $stmtC->fetch()) {

        $PoolSubId = $row['PoolSubId'];
        $MemberName = $row['MemberName'];
        $MemberNumber = trim((string)$row['MemberNumber']);

        $stmtF = $con->query("SELECT * FROM registeredusers WHERE MobileNumber = '$MemberNumber' and PushNotification != 'off'");
        $FriendExists = $con->query("SELECT FOUND_ROWS()")->fetchColumn();
        if ($FriendExists > 0) {
            $Message = $OwnerName . " has deleted the group " . $ClubName;
            while ($row = $stmtF->fetch()) {
                $MemberDeviceToken = $row['DeviceToken'];
                $MemberPlatform = $row['Platform'];
                $MemberName = $row['FullName'];
            }
            $gcm_arrayF = array();
            $apns_arrayF = array();
            if ($MemberPlatform == "A") {
                $gcm_arrayF[] = $MemberDeviceToken;
            } else {
                $apns_arrayF[] = $MemberDeviceToken;
            }

            $NotificationType = "PoolId_ClubDeleted";
            $manFriend = "INSERT INTO notifications(NotificationType, SentMemberName, SentMemberNumber, ReceiveMemberName, ReceiveMemberNumber, Message, PoolId, DateTime) VALUES ('$NotificationType','$OwnerName','$OwnerNumber','$MemberName','$MemberNumber','$Message','$poolid',now())";
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
        }

        $sql21 = "DELETE FROM userpoolsslave WHERE PoolId = '$poolid' and PoolSubId = '$PoolSubId'";
        $stmt21 = $con->prepare($sql21);
        $res21 = $stmt21->execute();
        if ($res21 === true) {
            $IsSuccess = true;
        } else {
            $IsSuccess = false;
            break;
        }
    }
    if ($IsSuccess) {
        $sql2 = "DELETE FROM userpoolsmaster WHERE PoolId = '$poolid'";
        $stmt2 = $con->prepare($sql2);
        $res2 = $stmt2->execute();

        if ($res2 === true) {
            echo "club deleted";
            $con->commit();
        } else {
            $con->rollBack();
            echo "Error";
        }
    } else {
        $con->rollBack();
        echo "Error";
    }
} else {
    $sql2 = "DELETE FROM userpoolsmaster WHERE PoolId = '$poolid'";
    $stmt2 = $con->prepare($sql2);
    $res2 = $stmt2->execute();

    if ($res2 === true) {
        echo "club deleted";
    } else {
        echo "Error";
    }
}
