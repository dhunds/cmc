<?php
include('connection.php');
include_once('classes/class.notification.php');
$objNotification = new Notification();

$CabId = $_POST['CabId'];
$MemberName = $_POST['MemberName'];
$MemberNumber = $_POST['MemberNumber'];
$ReferedUserName = $_POST['ReferedUserName'];
$ReferedUserNumber = $_POST['ReferedUserNumber'];

$Message = '';
$OwnerNumber = '';
$OwnerName = '';
$OwnerDeviceToken = '';
$Platform = '';
$RefId = 0;

$IsSuccess = false;
$ReferedUserNumberNew = substr($ReferedUserNumber, 1, -1);
$ReferedUserNameNew = substr($ReferedUserName, 1, -1);

$sqlI = "SELECT MobileNumber FROM cabopen WHERE CabId = '$CabId' and CabStatus = 'A'";
$stmtI = $con->query($sqlI);
$OwnerNumber = $stmtI->fetchColumn();

$sqlF = "SELECT fromshortname FROM cabopen WHERE CabId = '$CabId' and CabStatus = 'A'";
$stmtF = $con->query($sqlF);
$FromShortAddress = $stmtF->fetchColumn();

$sqlT = "SELECT toshortname FROM cabopen WHERE CabId = '$CabId' and CabStatus = 'A'";
$stmtT = $con->query($sqlT);
$ToShortAddress = $stmtT->fetchColumn();

$stmt = $con->query("SELECT * FROM registeredusers WHERE MobileNumber = '$OwnerNumber' and PushNotification != 'off'");
$no_of_users = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

if ($no_of_users > 0) {
    while ($row = $stmt->fetch()) {
        $OwnerDeviceToken = $row['DeviceToken'];
        $Platform = $row['Platform'];
        $OwnerName = $row['FullName'];
    }

    if ($OwnerDeviceToken != '') {
        $gcm_array = array();
        $apns_array = array();
        if ($Platform == "A") {
            $gcm_array[] = $OwnerDeviceToken;
        } else {
            $apns_array[] = $OwnerDeviceToken;
        }

        $refNumber = explode(",", $ReferedUserNumberNew);
        $refName = explode(",", $ReferedUserNameNew);
        $length = count($refNumber);

        for ($i = 0; $i < $length; $i++) {
            $Message = $MemberName . " has reffered " . $refName[$i] . " to join your ride from " . $FromShortAddress . " to " . $ToShortAddress;
            $NotificationType = "CabId_Refered";

            $sqlRef = "INSERT INTO referfriendtoride (CabId,MemberNumber,FriendNumber,FriendName,RefDateTime) VALUES ('$CabId','$MemberNumber','$refNumber[$i]','$refName[$i]',now())";
            $stmtRef = $con->prepare($sqlRef);
            $resRef = $stmtRef->execute();
            $RefId = $con->lastInsertId();
            $notificationId = 0;

            if ($resRef === true) {
                $params = array('NotificationType' => $NotificationType, 'SentMemberName' => $MemberName, 'SentMemberNumber' => $MemberNumber, 'ReceiveMemberName'=>$OwnerName, 'ReceiveMemberNumber'=>$OwnerNumber, 'Message'=>$Message, 'CabId'=>$CabId, 'DateTime'=>'now()', 'RefId'=>$RefId);
                $notificationId = $objNotification->logNotification($params);
            }
        }

        if ($notificationId) {
            $Message = $MemberName . " has referred friend(s) to join your ride from " . $FromShortAddress . " to " . $ToShortAddress;
            $body = array('gcmText' => $Message, 'pushfrom' => '', 'notificationId' => $notificationId);

            if (count($gcm_array) > 0) {
                $objNotification->setVariables($gcm_array, $body);
                $objNotification->sendGCMNotification();

            }
            if (count($apns_array) > 0) {
                $objNotification->setVariables($apns_array, $body);
                $objNotification->sendIOSNotification();
            }
            echo "SUCCESS";
        } else {
            echo "FAILURE";
        }
    } else {
        echo "FAILURE";
    }
} else {
    echo "FAILURE";
}