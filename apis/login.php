<?php
include('connection.php');
include_once('classes/class.notification.php');
$objNotification = new Notification();

$MobileNumber = $_POST['MobileNumber'];

if ($MobileNumber == '00919810000000') {  // Dummy login for apple testing
    $stmt = $con->query("SELECT * FROM registeredusers WHERE Trim(MobileNumber) = Trim('$MobileNumber')");
    $rows = $con->query("SELECT FOUND_ROWS()")->fetchColumn();
    $usrRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($usrRows);
    exit;
} else {
    $stmt = $con->query("SELECT * FROM registeredusers WHERE Trim(MobileNumber) = Trim('$MobileNumber')");
    $rows = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

    if ($rows > 0) {
        $usrRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $singleusepassword = rand(100000, 999999);
        $timestamp = date('Y-m-d H:i:s', strtotime('+1 day'));

        $stmt = $con->query("SELECT SmsMessage FROM smstemplates WHERE SmsshortCode = 'OTP'");
        $message = $stmt->fetchColumn();
        $message = str_replace("XXXXXX", $singleusepassword, $message);
        $MobileNumber = '[' . $MobileNumber . ']';

        $objNotification->sendSMS($MobileNumber, $message);
        $MobileNumber = $_POST['MobileNumber'];

        $sql2 = "UPDATE registeredusers SET SingleUsePassword='$singleusepassword', SingleUseExpiry= '$timestamp' WHERE Trim(MobileNumber) = Trim('$MobileNumber')";
        $stmt2 = $con->prepare($sql2);
        $res2 = $stmt2->execute();
        echo json_encode($usrRows);
    } else {
        echo "login error";
    }
}
