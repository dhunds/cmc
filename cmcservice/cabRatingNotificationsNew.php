<?php
include('connection.php');
include_once('classes/class.notification.php');
$objNotification = new Notification();

$stmt = $con->query("SELECT CabId,MobileNumber,FromShortName,ToShortName from cabopen where NOW() > DATE_ADD(ExpEndDateTime, INTERVAL 1 HOUR) AND RateNotificationSend = 0 and CabStatus = 'A' AND rideType !=1");
$CabsExists = $con->query("SELECT FOUND_ROWS()")->fetchColumn();
if ($CabsExists > 0) {
    while ($row = $stmt->fetch()) {
        $CabID = $row['CabId'];
        $FromShortAddress = $row['FromShortName'];
        $ToShortAddress = $row['ToShortName'];
        $OwnerNumber = (string)$row['MobileNumber'];
    }
    $RateNotificationMessage = "Please rate your ride from " . $FromShortAddress . " to  " . $ToShortAddress . ".";

    $stmtOwner = $con->query("SELECT * FROM registeredusers WHERE Trim(MobileNumber) = Trim('$OwnerNumber') and PushNotification != 'off'");
    $OwnerExists = $con->query("SELECT FOUND_ROWS()")->fetchColumn();
    if ($OwnerExists > 0) {
        $gcm_array = array();
        $apns_array = array();
        while ($row = $stmtOwner->fetch()) {
            $OwnerDeviceToken = $row['DeviceToken'];
            $Platform = $row['Platform'];
            $OwnerName = $row['FullName'];
        }

        $NotificationType = "Cab_Rating";
        $man = "INSERT INTO notifications(NotificationType,SentMemberName, SentMemberNumber, ReceiveMemberName, ReceiveMemberNumber, Message, CabId, DateTime) VALUES ('$NotificationType','System','','$OwnerName','$OwnerNumber','$RateNotificationMessage','$CabID',now())";
        $manstmt = $con->prepare($man);
        $manres = $manstmt->execute();
        $notificationId =  $con->lastInsertId();

        $cronsql = "INSERT INTO cronnotifications(NotificationType,SentMemberName, SentMemberNumber, ReceiveMemberName, ReceiveMemberNumber, Message, CabId, DateTime) VALUES ('$NotificationType','System','','$MemberName','$MemberNumber','$RateNotificationMessage','$CabID',now())";
        $cronstmt1 = $con->prepare($cronsql);
        $cronres1 = $cronstmt1->execute();

        $body = array('gcmText' => $RateNotificationMessage, 'pushfrom' => 'Cab_Rating', 'notificationId' => $notificationId);

        if ($Platform == "A") {
            $gcm_array[] = $OwnerDeviceToken;
            $objNotification->setVariables($gcm_array, $body);
            $res = $objNotification->sendGCMNotification();
        } else {
            $apns_array[] = $OwnerDeviceToken;
            $objNotification->setVariables($apns_array, $body);
            $objNotification->sendIOSNotification();
        }
    }
    $stmt1 = $con->query("select a.* from registeredusers a, acceptedrequest b where a.PushNotification != 'off' and Trim(a.MobileNumber) = Trim(b.MemberNumber) and b.CabId = '$CabID'");
    $no_of_users = $con->query("SELECT FOUND_ROWS()")->fetchColumn();
    if ($no_of_users > 0) {
        while ($row = $stmt1->fetch()) {
            $gcm_array = array();
            $apns_array = array();
            $FriendPlatform = $row['Platform'];
            $MemberName = $row['FullName'];
            $MemberNumber = (string)$row['MobileNumber'];

            $NotificationType = "Cab_Rating";
            $man = "INSERT INTO notifications(NotificationType,SentMemberName, SentMemberNumber, ReceiveMemberName, ReceiveMemberNumber, Message, CabId, DateTime) VALUES ('$NotificationType','System','','$MemberName','$MemberNumber','$RateNotificationMessage','$CabID',now())";
            $manstmt = $con->prepare($man);
            $manres = $manstmt->execute();
            $notificationId =  $con->lastInsertId();

            $cronsql = "INSERT INTO cronnotifications(NotificationType,SentMemberName, SentMemberNumber, ReceiveMemberName, ReceiveMemberNumber, Message, CabId, DateTime) VALUES ('$NotificationType','System','','$MemberName','$MemberNumber','$RateNotificationMessage','$CabID',now())";
            $cronstmt = $con->prepare($cronsql);
            $cronres = $cronstmt->execute();

            $body = array('gcmText' => $RateNotificationMessage, 'pushfrom' => 'Cab_Rating', 'notificationId' => $notificationId);
            if ($FriendPlatform == "A") {
                $gcm_array[] = $row['DeviceToken'];
                $objNotification->setVariables($gcm_array, $body);
                $res = $objNotification->sendGCMNotification();
            } else {
                $apns_array[] = $row['DeviceToken'];
                $objNotification->setVariables($apns_array, $body);
                $objNotification->sendIOSNotification();
            }
        }
    }

    $sql = "UPDATE cabopen set RateNotificationSend = 1 where CabId = '$CabID'";
    $stmt = $con->prepare($sql);
    $stmt->execute();
} else {
    echo "no one in database";
}