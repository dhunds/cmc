<?php
include('connection.php');
include_once('classes/class.notification.php');
$objNotification = new Notification();

$CabId = $_POST['CabId'];
$OwnerName = $_POST['OwnerName'];
$OwnerNumber = $_POST['OwnerNumber'];
$Message = $_POST['Message'];

$Status = "U";

$sql2 = "UPDATE cabopen set CabStatus='I' where (CabId = '$CabId' AND MobileNumber = '$OwnerNumber')";
$stmt2 = $con->prepare($sql2);
$res2 = $stmt2->execute();

if ($res2 === true) {
    $stmt999 = $con->query("SELECT MemberName,MemberNumber FROM acceptedrequest WHERE (CabId = '$CabId' AND Status != 'Dropped' AND MemberNumber != '$OwnerNumber')");

    while ($row999 = $stmt999->fetch()) {
        $MemberName_array[] = $row999['MemberName'];
        $MemberNumber_array[] = $row999['MemberNumber'];
    }

    $NotificationType = "CabId_CancelRide";
    $sqlquery = "INSERT INTO notifications(NotificationType, SentMemberName, SentMemberNumber, ReceiveMemberName, ReceiveMemberNumber, Message, CabId, DateTime) VALUES ";
    for ($j = 0; $j < count($MemberNumber_array); $j++) {
        $sqlquery .= "('" . $OwnerName . "','" . $OwnerName . "','" . $OwnerNumber . "','" . $MemberName_array[$j] . "','" . $MemberNumber_array[$j] . "','" . $Message . "','" . $CabId . "', now()),";
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
    } else {
        echo "no one in database";
    }
} else {
    echo "Error";
}