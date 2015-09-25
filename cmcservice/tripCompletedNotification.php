<?php
include('connection.php');
include_once('classes/class.notification.php');
$objNotification = new Notification();

$stmt = $con->query("select a.*,c.cabid,c.fromshortname,c.toshortname from registeredusers a, acceptedrequest b, cabopen c where a.PushNotification != 'off' and a.MobileNumber = b.MemberNumber and b.cabid = c.cabid and c.cabid IN (SELECT cabid from cabopen where NOW() > DATE_ADD(ExpEndDateTime, INTERVAL 4 HOUR) AND RateNotificationSend = 0 and CabStatus = 'A')");
$no_of_users = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

if ($no_of_users > 0) {
    while ($row = $stmt->fetch()) {
        $gcm_array = array();
        $apns_array = array();
        $CabID = $row['cabid'];
        $MemberName = $row['FullName'];
        $MemberNumber = $row['MobileNumber'];
        $FromShortAddress = $row['fromshortname'];
        $ToShortAddress = $row['toshortname'];
        $FriendPlatform = $row['Platform'];

        $RateNotificationMessage = "Your trip from " . $FromShortAddress . " to  " . $ToShortAddress . " seems to be completed. Please help us improve our services by rating the cab.";

        $NotificationType = "Cab_Rating";
        $man = "INSERT INTO notifications(NotificationType,SentMemberName, SentMemberNumber, ReceiveMemberName, ReceiveMemberNumber, Message, CabId, DateTime) VALUES ('$NotificationType','System','','$MemberName','$MemberNumber','$RateNotificationMessage','$CabID',now())";
        $manstmt = $con->prepare($man);
        $manres = $manstmt->execute();
        $notificationId = $con->lastInsertId();

        $body = array('gcmText' => $RateNotificationMessage, 'pushfrom' => 'tripcompleted', 'notificationId' => $notificationId);

        if ($FriendPlatform == "A") {
            $gcm_array[] = $row['DeviceToken'];
            $objNotification->setVariables($gcm_array, $body);
            $res = $objNotification->sendGCMNotification();
        } else {
            $apns_array[] = $row['DeviceToken'];
            $objNotification->setVariables($apns_array, $body);
            $res = $objNotification->sendIOSNotification();
        }
        $sql12 = "UPDATE cabopen set ratenotificationsend = '1' where cabid = '$CabID'";
        $stmt12 = $con->prepare($sql12);
        $res12 = $stmt12->execute();
    }
} else {
    echo "no one in database";
}

$sql123 = "UPDATE cabopen set ratenotificationsend = '1', cabstatus = 'C' where NOW() > DATE_ADD(expenddatetime, INTERVAL 12 HOUR) AND cabstatus = 'A'";
$stmt123 = $con->prepare($sql123);
$res123 = $stmt123->execute();


$stmt1 = $con->query("select a.*,c.cabid,c.fromshortname,c.toshortname from registeredusers a, acceptedrequest b, cabopen c where a.PushNotification != 'off' and a.MobileNumber = b.MemberNumber and b.cabid = c.cabid and c.cabid IN (SELECT cabid from cabopen where TIMESTAMPDIFF(MINUTE, NOW(), ExpStartDateTime) = 10  AND RateNotificationSend = 0 and CabStatus = 'A')");
$no_of_users = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

if ($no_of_users > 0) {
    while ($row = $stmt1->fetch()) {
        $gcm_array = array();
        $apns_array = array();

        $CabID = $row['cabid'];
        $MemberName = $row['FullName'];
        $MemberNumber = $row['MobileNumber'];
        $FriendPlatform = $row['Platform'];
        $FromShortAddress = $row['fromshortname'];
        $ToShortAddress = $row['toshortname'];
        $UpcomingTripNotification = "You have an upcoming trip from " . $FromShortAddress . " to " . $ToShortAddress . ". Click here to view details.";

        $NotificationType = "Cab_Rating";
        $man = "INSERT INTO notifications(NotificationType,SentMemberName, SentMemberNumber, ReceiveMemberName, ReceiveMemberNumber, Message, CabId, DateTime) VALUES ('$NotificationType','System','','$MemberName','$MemberNumber','$UpcomingTripNotification','$CabID',now())";
        $manstmt = $con->prepare($man);
        $manres = $manstmt->execute();
        $notificationId = $con->lastInsertId();

        $body = array('gcmText' => $UpcomingTripNotification, 'pushfrom' => 'upcomingtrip', 'notificationId' => $notificationId);

        if ($FriendPlatform == "A") {
            $gcm_array[] = $row['DeviceToken'];
            $objNotification->setVariables($gcm_array, $body);
            $res = $objNotification->sendGCMNotification();
        } else {
            $apns_array[] = $row['DeviceToken'];
            $objNotification->setVariables($apns_array, $body);
            $res = $objNotification->sendIOSNotification();
        }
    }
} else {
    echo "no one in database";
}
?>