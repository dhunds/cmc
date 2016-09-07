<?php
include('connection.php');
include_once('classes/class.notification.php');
include('../common.php');
$objNotification = new Notification();

list($sLat, $sLon) = explode(',', $_POST['sLatLon']);
list($eLat, $eLon) = explode(',', $_POST['eLatLon']);

$CabId = $_POST['CabId'];
$MobileNumber = $_POST['MobileNumber'];
$userId = $_POST['userId'];
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
$fromCity = $_POST['fromCity'];
$toCity = $_POST['toCity'];
$sLatLon = $_POST['sLatLon'];
$eLatLon = $_POST['eLatLon'];

$rideType = '';

$isIntercity =0;

if (isIntracityRide($fromCity, $toCity)){
    $isIntercity =0;
    $perKmCharge = perKMChargeIntracity();
} else {
    $isIntercity =1;
    $perKmCharge = perKMChargeIntercity();
}


if(isset($_POST['rideType']) && $_POST['rideType'] !='') {
    $rideType = $_POST['rideType'];
}

$dateInput = explode('/', $TravelDate);
$cDate = $dateInput[1] . '/' . $dateInput[0] . '/' . $dateInput[2];

$expTrip = strtotime($cDate . ' ' . $TravelTime);
$newdate = $expTrip + $ExpTripDuration;
$ExpEndDateTime = date('Y-m-d H:i:s', $newdate);

$startDate = $expTrip;
$ExpStartDateTime = date('Y-m-d H:i:s', $startDate);

$sql2 = "INSERT INTO cabopen(CabId, userId, MobileNumber, OwnerName, FromLocation, ToLocation, FromShortName, ToShortName, fromCity, toCity, sLatLon, eLatLon, sLat, sLon, eLat, eLon, TravelDate, TravelTime, Seats, RemainingSeats, Distance, OpenTime, ExpTripDuration,ExpStartDateTime,ExpEndDateTime,rideType,perKmCharge, isIntercity) VALUES ('$CabId', $userId, '$MobileNumber','$OwnerName','$FromLocation','$ToLocation','$FromShortName','$ToShortName', '$fromCity', '$toCity', '$sLatLon','$eLatLon', '$sLat', '$sLon', '$eLat', '$eLon','$TravelDate','$TravelTime','$Seats','$RemainingSeats','$Distance',now(),'$ExpTripDuration', '$ExpStartDateTime','$ExpEndDateTime','$rideType','$perKmCharge', $isIntercity)";

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
            $rs = $con->query("SELECT NotificationId FROM notifications WHERE CabID = '".$CabId."' AND TRIM(ReceiveMemberNumber)='".$row['MobileNumber']."'");
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