<?php
include('connection.php');
include_once('classes/class.notification.php');
$objNotification = new Notification();

$MembersNumber = $_POST['MembersNumber'];
$MembersName = $_POST['MembersName'];
$FullName = $_POST['FullName'];
$MobileNumber = $_POST['MobileNumber'];
$Message = $_POST['Message'];
$latlongstr = $_POST['latlongstr'];

$Membersnew = substr($MembersNumber, 1, -1);
$MembersNamenew = substr($MembersName, 1, -1);
$myArraynumber = explode(',', $Membersnew);
$myArrayname = explode(',', $MembersNamenew);

$NotificationType = "Share_LocationUpdate";
$sqlquery = "INSERT INTO notifications(NotificationType, SentMemberName, SentMemberNumber, ReceiveMemberName, ReceiveMemberNumber, Message, UserLatLong, DateTime) VALUES ";

for ($j = 0; $j < count($myArraynumber); $j++) {
    $sqlquery .= "('" . $NotificationType . "','" . $FullName . "','" . $MobileNumber . "','" . $myArrayname[$j] . "','" . trim((string)$myArraynumber[$j]) . "','" . $Message . "','" . $latlongstr . "', now()),";
}
$sqlquery = trim($sqlquery, ",");
$manstmt = $con->prepare($sqlquery);
$ressqlquery = $manstmt->execute();

$myArraynumber = explode(',', $Membersnew);
$strNo = '';
for ($i = 0; $i < count($myArraynumber); $i++) {
    $memNumber = trim((string)$myArraynumber[$j]);
    $stmt1 = $con->query("SELECT * FROM registeredusers WHERE MobileNumber = '$memNumber'");
    $user_exists = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

    if ($MobileNumber == $memNumber) {
        $user_exists = 1;
    }
    if ($user_exists == 0) {
        if ($strNo == '') {
            $strNo = "[" . "" . $memNumber;
        } else {
            $strNo .= ',' . $memNumber;
        }
    }
}

if ($strNo != '') {
    $sql = "SELECT SmsMessage FROM smstemplates WHERE SmsshortCode = 'SHARELOC'";
    $stmt = $con->query($sql);
    $message = $stmt->fetchColumn();
    $message = str_replace("UXXXXX", $FullName, $message);
    $objNotification->sendSMS($strNo . "]", $message);
}

$stmt = $con->query("SELECT * FROM registeredusers WHERE MobileNumber IN ($Membersnew) and PushNotification != 'off' AND DeviceToken !=''");
$no_of_users = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

if ($no_of_users > 0) {
    $body = array('gcmText' => $Message, 'pushfrom' => 'Share_LocationUpdate', 'latLong' => $latlongstr);

    while ($row = $stmt->fetch()) {
        $rs = $con->query("SELECT NotificationId FROM notifications WHERE UserLatLong = '".$latlongstr."' AND ReceiveMemberNumber='".$row['MobileNumber']."'");
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
?>