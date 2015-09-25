<?php
include('connection.php');
include_once('classes/class.notification.php');
$objNotification = new Notification();

$stmt = $con->query("select a.*,c.cabid,c.MobileNumber as OwnerNumber,c.fromshortname,c.toshortname from registeredusers a, acceptedrequest b, cabopen c where a.PushNotification != 'off' and a.MobileNumber = b.MemberNumber and b.cabid = c.cabid and c.cabid IN (SELECT cabid from cabopen where NOW() > DATE_ADD(ExpEndDateTime, INTERVAL 4 HOUR) AND RateNotificationSend = 0 and CabStatus = 'A')");
$no_of_users = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

if ($no_of_users > 0) {
    while ($row = $stmt->fetch()) {
        $gcm_array = array();
        $apns_array = array();
        $CabID = $row['cabid'];
        $MemberName = $row['FullName'];
        $MemberNumber = (string)$row['MobileNumber'];
        $FromShortAddress = $row['fromshortname'];
        $ToShortAddress = $row['toshortname'];
        $FriendPlatform = $row['Platform'];
        $OwnerNumber = (string)$row['OwnerNumber'];

        $RateNotificationMessage = "Trip from " . $FromShortAddress . " to  " . $ToShortAddress . " completed? Help us improve by rating the cab service.";

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
            $objNotification->setVariables($gcm_arrayF, $body);
            $res = $objNotification->sendGCMNotification();
        } else {
            $apns_array[] = $row['DeviceToken'];
            $objNotification->setVariables($apns_array, $body);
            $objNotification->sendIOSNotification();
        }
        $sql12 = "UPDATE cabopen set ratenotificationsend = '1' where cabid = '$CabID'";
        $stmt12 = $con->prepare($sql12);
        $res12 = $stmt12->execute();
    }

    $stmt1 = $con->query("SELECT * FROM registeredusers WHERE Trim(MobileNumber) = Trim('$OwnerNumber') and PushNotification != 'off'");
    $OwnerExists = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

    if ($OwnerExists > 0) {
        $gcm_array = array();
        $apns_array = array();
        while ($row = $stmt1->fetch()) {
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

} else {
    echo "no one in database";
}
?>