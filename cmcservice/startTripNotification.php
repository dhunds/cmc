<?php
include('connection.php');
include_once('classes/class.notification.php');
$objNotification = new Notification();

if (isset($_POST['cabId']) && $_POST['cabId'] != '') {

    $CabID = $_POST['cabId'];
    $stmt = $con->query("SELECT OwnerName, FromShortName, ToShortName from cabopen where CabId='" . $CabID . "'");
    $CabsExists = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

    if ($CabsExists > 0) {
        $row = $stmt->fetch();
        $ownerName = $row['OwnerName'];
        $FromShortAddress = $row['FromShortName'];
        $ToShortAddress = $row['ToShortName'];
        $tripNotification = $ownerName . " has started trip from " . $FromShortAddress . ". To track the location open iShareRyde";
        $stmt1 = $con->query("select a.* from registeredusers a, acceptedrequest b where a.PushNotification != 'off' and Trim(a.MobileNumber) = Trim(b.MemberNumber) and b.cabid = '$CabID'");
        $no_of_users = $con->query("SELECT FOUND_ROWS()")->fetchColumn();
        if ($no_of_users > 0) {
            while ($row = $stmt1->fetch()) {
                $gcm_array = array();
                $apns_array = array();
                $FriendPlatform = $row['Platform'];
                $MemberName = $row['FullName'];
                $MemberNumber = (string)$row['MobileNumber'];

                $NotificationType = "Trip started";
                $cronsql = "INSERT INTO cronnotifications(NotificationType,SentMemberName, SentMemberNumber, ReceiveMemberName, ReceiveMemberNumber, Message, CabId, DateTime) VALUES ('$NotificationType','System','','$MemberName','$MemberNumber','$tripNotification','$CabID',now())";
                $cronstmt = $con->prepare($cronsql);
                $cronres = $cronstmt->execute();

                $man = "INSERT INTO notifications(NotificationType,SentMemberName, SentMemberNumber, ReceiveMemberName, ReceiveMemberNumber, Message, CabId, DateTime) VALUES ('$NotificationType','System','','$MemberName','$MemberNumber','$tripNotification','$CabID',now())";
                $manstmt = $con->prepare($man);
                $manres = $manstmt->execute();
                $notificationId = $con->lastInsertId();

                $body = array('gcmText' => $tripNotification, 'pushfrom' => 'TripStart', 'notificationId' => $notificationId, 'CabId' => $CabID);

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
        }
        $sql12 = "UPDATE cabopen set uptripnotification = '1', status=1 where CabId = '" . $CabID . "'";
        $stmt12 = $con->prepare($sql12);
        $res12 = $stmt12->execute();
    }
}