<?php
include('connection.php');
include_once('classes/class.notification.php');
$objNotification = new Notification();

$CabId = $_POST['CabId'];
$MembersNumber = $_POST['MembersNumber'];
$MembersName = $_POST['MembersName'];
$OwnerName = $_POST['OwnerName'];
$OwnerNumber = $_POST['OwnerNumber'];

$sqlF = "SELECT fromshortname FROM cabopen WHERE CabId = '$CabId'";
$stmtF = $con->query($sqlF);
$FromShortAddress = $stmtF->fetchColumn();

$sqlT = "SELECT toshortname FROM cabopen WHERE CabId = '$CabId'";
$stmtT = $con->query($sqlT);
$ToShortAddress = $stmtT->fetchColumn();

$Message = $OwnerName . " invited you to share a cab from " . $FromShortAddress . " to " . $ToShortAddress;

$MembersNumbernew = substr($MembersNumber, 1, -1);
$MembersNamenew = substr($MembersName, 1, -1);

$myArraynumber = explode(',', $MembersNumbernew);
$myArrayname = explode(',', $MembersNamenew);
$strNo = '';
$strNoforAlreadyExistUser = '';
$str = "INSERT INTO cabmembers(CabId, MemberName, MemberNumber) VALUES";
for ($i = 0; $i < count($myArraynumber); $i++) {
    $stmt10 = $con->query("SELECT * FROM cabmembers WHERE MemberNumber = Trim('$myArraynumber[$i]') and CabId= '$CabId'");
    $user_already_exists = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

    if ($user_already_exists > 0) {
        if ($strNoforAlreadyExistUser == '') {
            $strNoforAlreadyExistUser = $myArraynumber[$i];
        } else {
            $strNoforAlreadyExistUser .= ',' . $myArraynumber[$i];
        }
        $sql12 = "UPDATE cabmembers set DropStatus = 'No' where MemberNumber = Trim('$myArraynumber[$i]') and CabId= '$CabId'";
        $stmt12 = $con->prepare($sql12);
        $res12 = $stmt12->execute();
    } else {
        $str .= "('" . $CabId . "','" . $myArrayname[$i] . "','" . $myArraynumber[$i] . "'),";

        $NotificationType = "CabId_Invited";

        $params = array('NotificationType' => $NotificationType, 'SentMemberName' => $OwnerName, 'SentMemberNumber' => $OwnerNumber, 'ReceiveMemberName'=>Trim($myArrayname[$i]), 'ReceiveMemberNumber'=>Trim($myArraynumber[$i]), 'Message'=>$Message, 'CabId'=>$CabId, 'DateTime'=>'now()');
        $notificationId = $objNotification->logNotification($params);
    }

    $stmt1 = $con->query("SELECT * FROM registeredusers WHERE MobileNumber = Trim('$myArraynumber[$i]')");

    $user_exists = $con->query("SELECT FOUND_ROWS()")->fetchColumn();
    if ($OwnerNumber == Trim($myArraynumber[$i])) {
        $user_exists = 1;
    }
    if ($user_exists == 0) {
        if ($strNo == '') {
            $strNo = "[" . $myArraynumber[$i];
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
    $message = str_replace("FXXXXX", $FromShortAddress, $message);
    $message = str_replace("TXXXXX", $ToShortAddress, $message);
    $objNotification->sendSMS($strNo . "]", $message);
}
$str = trim($str, ",");

$stmt2121 = $con->prepare($str);
$res2222 = $stmt2121->execute();

$stmt = $con->query("SELECT * FROM registeredusers WHERE MobileNumber IN ($MembersNumbernew) and PushNotification != 'off'");
$no_of_users = $con->query("SELECT FOUND_ROWS()")->fetchColumn();
if ($no_of_users > 0) {
    while ($row = $stmt->fetch()) {
        $gcm_array[] = $row['DeviceToken'];
    }
    $body = array('gcmText' => $Message, 'pushfrom' => 'CabId_', 'notificationId' => $notificationId);
    $objNotification->setVariables($gcm_array, $body);
    $objNotification->sendGCMNotification();
} else {
    echo "no one in database";
}

?>