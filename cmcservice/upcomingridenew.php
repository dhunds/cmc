<?php
include('connection.php');
include_once('classes/class.notification.php');
$objNotification = new Notification();

$stmt = $con->query("SELECT cabid,MobileNumber,fromshortname,toshortname from cabopen where TIMESTAMPDIFF(MINUTE, NOW(), ExpStartDateTime) < 10  AND uptripnotification = 0 and CabStatus = 'A'");
$CabsExists = $con->query("SELECT FOUND_ROWS()")->fetchColumn();
if ($CabsExists > 0) {
    while ($row = $stmt->fetch()) {
        $CabID = $row['cabid'];
        $FromShortAddress = $row['fromshortname'];
        $ToShortAddress = $row['toshortname'];
        $OwnerNumber = (string)$row['MobileNumber'];


        $UpcomingTripNotification = "You have an upcoming trip from " . $FromShortAddress . " to " . $ToShortAddress . ". Click here to view details.";

        $stmt1 = $con->query("select a.* from registeredusers a, acceptedrequest b where a.PushNotification != 'off' and Trim(a.MobileNumber) = Trim(b.MemberNumber) and b.cabid = '$CabID'");
        $no_of_users = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

        if ($no_of_users > 0) {
            while ($row1 = $stmt1->fetch()) {
                $gcm_array = array();
                $apns_array = array();
                $FriendPlatform = $row1['Platform'];
                $MemberName = $row1['FullName'];
                $MemberNumber = (string)$row1['MobileNumber'];

                $body = array('gcmText' => $UpcomingTripNotification, 'pushfrom' => 'upcomingtrip', 'CabId' => $CabID);

                if ($FriendPlatform == "A") {
                    $gcm_array[] = $row['DeviceToken'];
                    $objNotification->setVariables($gcm_array, $body);
                    $objNotification->sendGCMNotification();
                } else {
                    $apns_array[] = $row['DeviceToken'];
                    $objNotification->setVariables($apns_array, $body);
                    $objNotification->sendIOSNotification();
                }

                $NotificationType = "Upcoming Trip";
                $cronsql = "INSERT INTO cronnotifications(NotificationType,SentMemberName, SentMemberNumber, ReceiveMemberName, ReceiveMemberNumber, Message, CabId, DateTime) VALUES ('$NotificationType','System','','$MemberName','$MemberNumber','$UpcomingTripNotification','$CabID',now())";
                $cronstmt = $con->prepare($cronsql);
                $cronres = $cronstmt->execute();
            }
        }
    }
    $sql12 = "UPDATE cabopen set uptripnotification = '1' where cabid = '$CabID'";
    $stmt12 = $con->prepare($sql12);
    $res12 = $stmt12->execute();
} else {
    echo "no one in database";
}