<?php

include_once('config.php');
include_once('../cmcservice/classes/class.notification.php');
$objNotification = new Notification();

if (isset($_POST['cabId']) && $_POST['cabId'] != '') {

    $CabId = $_POST['cabId'];

    $stmt = $con->query("SELECT c.CabId, c.OwnerName, c.MobileNumber FROM cabopen c JOIN cabOwners co ON c.MobileNumber=co.mobileNumber WHERE co.cleintId=" . $client_id . " AND c.CabStatus='A' AND c.CabId='$CabId'");
    $found = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

    if ($found > 0) {
        $cabDetail = $stmt->fetch();
        $OwnerName = $cabDetail['OwnerName'];
        $OwnerNumber = $cabDetail['MobileNumber'];
        $Message = $_POST['Message'];

        $sql = "UPDATE cabopen set CabStatus='I' where (CabId = '$CabId' AND MobileNumber = '$OwnerNumber')";
        $stmt = $con->prepare($sql);
        $res = $stmt->execute();

        if ($res === true) {
            $stmt = $con->query("SELECT MemberName,MemberNumber FROM acceptedrequest WHERE (CabId = '$CabId' AND Status != 'Dropped' AND MemberNumber != '$OwnerNumber')");

            while ($row = $stmt->fetch()) {
                $MemberName_array[] = $row['MemberName'];
                $MemberNumber_array[] = $row['MemberNumber'];
            }

            $NotificationType = "CabId_CancelRide";
            $sqlquery = "INSERT INTO notifications(NotificationType, SentMemberName, SentMemberNumber, ReceiveMemberName, ReceiveMemberNumber, Message, CabId, DateTime) VALUES ";
            for ($j = 0; $j < count($MemberNumber_array); $j++) {

                $sqlquery .= "('" . $NotificationType . "','" . $OwnerName . "','" . $OwnerNumber . "','" . $MemberName_array[$j] . "','" . $MemberNumber_array[$j] . "','" . $Message . "','" . $CabId . "', now()),";

            }
            $sqlquery = trim($sqlquery, ",");
            $manstmt = $con->prepare($sqlquery);
            $ressqlquery = $manstmt->execute();

            $stmt = $con->query("SELECT * FROM registeredusers WHERE PushNotification != 'off' and MobileNumber IN (SELECT MemberNumber FROM acceptedrequest WHERE (CabId = '$CabId' AND Status != 'Dropped' AND MemberNumber != '$OwnerNumber'))");
            $no_of_users = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

            if ($no_of_users > 0) {
                $body = array('gcmText' => $Message, 'pushfrom' => '', 'CabId' => $CabId);
                while ($row = $stmt->fetch()) {
                    $rs = $con->query("SELECT NotificationId FROM notifications WHERE CabID = '" . $CabId . "' AND ReceiveMemberNumber='" . $row['MobileNumber'] . "'");
                    $notification = $rs->fetch(PDO::FETCH_ASSOC);
                    $notificationId = $notification['NotificationId'];

                    $body['notificationId'] = $notificationId;

                    if ($row['Platform'] == "A") {
                        $gcm_array = array();
                        $gcm_array[] = $row['DeviceToken'];
                        $objNotification->setVariables($gcm_array, $body);
                        $res = $objNotification->sendGCMNotification();
                    } else {
                        $apns_array = array();
                        $apns_array[] = $row['DeviceToken'];
                        $objNotification->setVariables($apns_array, $body);
                        $objNotification->sendIOSNotification();
                    }

                }
            }
        }

        setResponse(array("code" => 200, "status" => "Success", "message" => "Ride Cancelled"));

    } else {
        setResponse(array("code" => 200, "status" => "Error", "message" => "Invalid Ride"));
    }

} else {
    setResponse(array("code" => 200, "status" => "Error", "message" => "Bad Request"));
}
