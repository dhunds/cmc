<?php
include('connection.php');
include_once('classes/class.notification.php');
$objNotification = new Notification();

$CabId = $_POST['CabId'];
$MobileNumber = $_POST['MobileNumber'];
$OwnerName = $_POST['OwnerName'];
$FromLocation = $_POST['FromLocation'];
$ToLocation = $_POST['ToLocation'];
$TravelDate = $_POST['TravelDate'];
$TravelTime = $_POST['TravelTime'];
$Seats = $_POST['Seats'];
$RemainingSeats = $_POST['RemainingSeats'];
$Distance = $_POST['Distance'];
$MembersNumber = $_POST['MembersNumber'];
$MembersName = $_POST['MembersName'];
$Message = $_POST['Message'];
$ExpTripDuration = $_POST['ExpTripDuration'];
$FromShortName = $_POST['FromShortName'];
$ToShortName = $_POST['ToShortName'];

$rideType = '';
$perKmCharge = '';

if(isset($_POST['rideType']) && $_POST['rideType'] !='') {
    $rideType = $_POST['rideType'];
}

if(isset($_POST['perKmCharge']) && $_POST['perKmCharge'] !='') {
    $perKmCharge = $_POST['perKmCharge'];
}

$dateInput = explode('/', $TravelDate);
$cDate = $dateInput[1] . '/' . $dateInput[0] . '/' . $dateInput[2];

$expTrip = strtotime($cDate . ' ' . $TravelTime);
$newdate = $expTrip + $ExpTripDuration;
$ExpEndDateTime = date('Y-m-d H:i:s', $newdate);

$startDate = $expTrip;
$ExpStartDateTime = date('Y-m-d H:i:s', $startDate);

$sql2 = "INSERT INTO cabopen(CabId, MobileNumber, OwnerName, FromLocation, ToLocation, FromShortName, ToShortName, TravelDate, TravelTime, Seats, RemainingSeats, Distance, OpenTime, ExpTripDuration,ExpStartDateTime,ExpEndDateTime,rideType,perKmCharge) VALUES ('$CabId','$MobileNumber','$OwnerName','$FromLocation','$ToLocation','$FromShortName','$ToShortName','$TravelDate','$TravelTime','$Seats','$RemainingSeats','$Distance',now(),'$ExpTripDuration', '$ExpStartDateTime','$ExpEndDateTime',$rideType,$perKmCharge)";

$stmt2 = $con->prepare($sql2);
$res2 = $stmt2->execute();

if ($res2 === true) {
    $Membersnew = substr($MembersNumber, 1, -1);
    $MembersNamenew = substr($MembersName, 1, -1);

    $myArraynumber = explode(',', $Membersnew);
    $myArrayname = explode(',', $MembersNamenew);
    $strNo = '';
    $str = "INSERT INTO cabmembers(CabId, MemberName, MemberNumber) VALUES";
    for ($i = 0; $i < count($myArraynumber); $i++) {
        $str .= "('" . $CabId . "','" . $myArrayname[$i] . "','" . $myArraynumber[$i] . "'),";

        $stmt1 = $con->query("SELECT * FROM registeredusers WHERE MobileNumber = Trim('$myArraynumber[$i]')");

        $user_exists = $con->query("SELECT FOUND_ROWS()")->fetchColumn();
        if ($MobileNumber == Trim($myArraynumber[$i])) {
            $user_exists = 1;
        }
        if ($user_exists == 0) {
            if ($strNo == '') {
                $strNo = "[" . "" . $myArraynumber[$i];
            } else {
                $strNo .= ',' . $myArraynumber[$i];
            }
        }
    }

    if ($strNo != '') {
        $sql = "SELECT SmsMessage FROM smstemplates WHERE SmsshortCode = 'INVITE'";
        $stmt = $con->query($sql);
        $message = $stmt->fetchColumn();
        $message = str_replace("OXXXXX", $OwnerName, $message);
        $message = str_replace("FXXXXX", $FromShortName, $message);
        $message = str_replace("TXXXXX", $ToShortName, $message);
        $objNotification->sendSMS($strNo . "]", $message);
    }
    $str = trim($str, ",");

    $stmt2121 = $con->prepare($str);
    $res2222 = $stmt2121->execute();
    $NotificationType = "CabId_Invited";

    $sqlquery = "INSERT INTO notifications(NotificationType, SentMemberName, SentMemberNumber, ReceiveMemberName, ReceiveMemberNumber, Message, CabId, DateTime) VALUES ";
    for ($j = 0; $j < count($myArraynumber); $j++) {
        $sqlquery .= "('" . $NotificationType . "','" . $OwnerName . "','" . $MobileNumber . "','" . $myArrayname[$j] . "','" . $myArraynumber[$j] . "','" . $Message . "','" . $CabId . "', now()),";
    }
    $sqlquery = trim($sqlquery, ",");
    $manstmt = $con->prepare($sqlquery);
    $ressqlquery = $manstmt->execute();

    $stmt = $con->query("SELECT * FROM registeredusers WHERE MobileNumber IN ($Membersnew) and PushNotification != 'off'");

    $no_of_users = $con->query("SELECT FOUND_ROWS()")->fetchColumn();
    if ($no_of_users > 0) {
        $body = array('gcmText' => $Message, 'pushfrom' => 'CabId_', 'CabId' => $CabId);

        while ($row = $stmt->fetch()) {
            $rs = $con->query("SELECT NotificationId FROM notifications WHERE CabID = '".$CabId."' AND ReceiveMemberNumber='".$row['MobileNumber']."'");
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
    echo 'Error';
}

?>