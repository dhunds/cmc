<?php
include('connection.php');
include_once('classes/class.notification.php');
include_once('includes/offers.php');
$objNotification = new Notification();


if (isset($_POST['cabId']) && $_POST['cabId'] != '') {
    $CabID = $_POST['cabId'];
    $sql = "UPDATE cabopen set CabStatus = 'A', status=2 where CabId = '" . $CabID . "'";
    $stmt = $con->prepare($sql);
    $res = $stmt->execute();

// Sending notifications
    $stmt = $con->query("SELECT MobileNumber, FromShortName, ToShortName from cabopen where CabId='" . $CabID . "' AND RateNotificationSend = 0 AND CabStatus = 'A' AND rideType !=1");
    $CabsExists = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

    if ($CabsExists > 0) {
        while ($row = $stmt->fetch()) {
            $FromShortAddress = $row['FromShortName'];
            $ToShortAddress = $row['ToShortName'];
            $OwnerNumber = (string)$row['MobileNumber'];
        }

        $RateNotificationMessage = "Trip from " . $FromShortAddress . " to  " . $ToShortAddress . " completed. Help us improve by rating the cab service.";

        $RateNotificationMessageOwner = "Trip from " . $FromShortAddress . " to  " . $ToShortAddress . " completed. Click here to send the fare split to your friends.";

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
            $notificationId = $con->lastInsertId();

            $cronsql = "INSERT INTO cronnotifications(NotificationType,SentMemberName, SentMemberNumber, ReceiveMemberName, ReceiveMemberNumber, Message, CabId, DateTime) VALUES ('$NotificationType','System','','$MemberName','$MemberNumber','$RateNotificationMessage','$CabID',now())";
            $cronstmt1 = $con->prepare($cronsql);
            $cronres1 = $cronstmt1->execute();

            $body = array('gcmText' => $RateNotificationMessageOwner, 'pushfrom' => 'ownerTripCompleted', 'notificationId' => $notificationId, 'CabId' => $CabID);

            if ($Platform == "A") {
                $gcm_array[] = $OwnerDeviceToken;
                $objNotification->setVariables($gcm_array, $body);
                $res = $objNotification->sendGCMNotification();

                $body['gcmText'] = $RateNotificationMessage;
                $body['pushfrom'] = 'Cab_Rating';
                $objNotification->setVariables($gcm_array, $body);
                $res = $objNotification->sendGCMNotification();
            } else {
                $apns_array[] = $OwnerDeviceToken;
                $objNotification->setVariables($apns_array, $body);
                $res = $objNotification->sendIOSNotification();

                $body['gcmText'] = $RateNotificationMessage;
                $body['pushfrom'] = 'Cab_Rating';
                $objNotification->setVariables($apns_array, $body);
                $res = $objNotification->sendIOSNotification();
            }

            $stmt1 = $con->query("select MemberName from acceptedrequest where cabid = '$CabID'");
            $found = $con->query("SELECT FOUND_ROWS()")->fetchColumn();
            if ($found >0){
                $resp = claimFirstRideBonus($OwnerNumber, $objNotification, 1);
            }
        }
        $stmt1 = $con->query("select a.* from registeredusers a, acceptedrequest b where a.PushNotification != 'off' and Trim(a.MobileNumber) = Trim(b.MemberNumber) and b.cabid = '$CabID'");
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
                $notificationId = $con->lastInsertId();

                $cronsql = "INSERT INTO cronnotifications(NotificationType,SentMemberName, SentMemberNumber, ReceiveMemberName, ReceiveMemberNumber, Message, CabId, DateTime) VALUES ('$NotificationType','System','','$MemberName','$MemberNumber','$RateNotificationMessage','$CabID',now())";
                $cronstmt = $con->prepare($cronsql);
                $cronres = $cronstmt->execute();

                $body = array('gcmText' => $RateNotificationMessageOwner, 'pushfrom' => 'tripcompleted', 'notificationId' => $notificationId, 'CabId' => $CabID);

                if ($FriendPlatform == "A") {
                    $gcm_array[] = $row['DeviceToken'];
                    $objNotification->setVariables($gcm_array, $body);
                    $res = $objNotification->sendGCMNotification();

                    $body['gcmText'] = $RateNotificationMessage;
                    $body['pushfrom'] = 'Cab_Rating';
                    $objNotification->setVariables($gcm_array, $body);
                    $res = $objNotification->sendGCMNotification();
                } else {
                    $apns_array[] = $row['DeviceToken'];
                    $objNotification->setVariables($apns_array, $body);
                    $res = $objNotification->sendIOSNotification();

                    $body['gcmText'] = $RateNotificationMessage;
                    $body['pushfrom'] = 'Cab_Rating';
                    $objNotification->setVariables($apns_array, $body);
                    $res = $objNotification->sendIOSNotification();
                }
                if ($resp=='success'){
                    claimFirstRideBonus($MemberNumber, $objNotification, '');
                }
            }
        }

        $sql12 = "UPDATE cabopen set ratenotificationsend = '1' where cabid = '$CabID'";
        $stmt12 = $con->prepare($sql12);
        $res12 = $stmt12->execute();
    }

    header('Content-Type: application/json');
    echo '{"msg": "Cab status updated, notification sent."}';
    exit;
} else {
    header('Content-Type: application/json');
    echo '{"msg": "Invalid Params."}';
    exit;
}