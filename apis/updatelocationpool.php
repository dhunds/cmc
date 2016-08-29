<?php
include('connection.php');
include_once('classes/class.notification.php');
$objNotification = new Notification();

$CabId = $_POST['CabId'];
$MemberName = $_POST['MemberName'];
$MemberNumber = $_POST['MemberNumber'];
$OwnerName = $_POST['OwnerName'];
$OwnerNumber = $_POST['OwnerNumber'];
$MemberLocationAddress = $_POST['MemberLocationAddress'];
$MemberLocationlatlong = $_POST['MemberLocationlatlong'];
$MemberEndLocationAddress = $_POST['MemberEndLocationAddress'];
$MemberEndLocationlatlong = $_POST['MemberEndLocationlatlong'];
$Message = $_POST['Message'];

$sql2 = "UPDATE acceptedrequest SET MemberLocationAddress='$MemberLocationAddress',
MemberLocationlatlong='$MemberLocationlatlong', MemberEndLocationAddress='$MemberEndLocationAddress',
MemberEndLocationlatlong='$MemberEndLocationlatlong' WHERE (CabId = '$CabId' AND MemberNumber = '$MemberNumber')";
$stmt2 = $con->prepare($sql2);
$res2 = $stmt2->execute();

if ($res2 === true) {
    $NotificationType = "CabId_UpdateLocation";
    $params = array('NotificationType' => $NotificationType, 'SentMemberName' => $MemberName, 'SentMemberNumber' => $MemberNumber, 'ReceiveMemberName' => $OwnerName, 'ReceiveMemberNumber' => $OwnerNumber, 'Message' => $Message, 'CabId' => $CabId, 'DateTime' => 'now()');
    $notificationId = $objNotification->logNotification($params);

    $stmt = $con->query("SELECT * FROM registeredusers WHERE PushNotification != 'off' and MobileNumber = '$OwnerNumber'");

    $no_of_users = $con->query("SELECT FOUND_ROWS()")->fetchColumn();
    if ($no_of_users > 0) {
        while ($row = $stmt->fetch()) {
            $gcm_array[] = $row['DeviceToken'];
        }

        $body = array('gcmText' => $Message, 'pushfrom' => 'CabId_', 'CabId' => $CabId, 'notificationId' => $notificationId);
        $objNotification->setVariables($gcm_array, $body);
        $objNotification->sendGCMNotification();
    } else {
        echo "no one in database";
    }
    http_response_code(200);
    header('Content-Type: application/json');
    echo '{status:"success", message:"Location Updated"}';
    exit;
} else {
    echo 'Error';
}
