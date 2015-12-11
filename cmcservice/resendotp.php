<?php
include('connection.php');
include_once('classes/class.notification.php');
$objNotification = new Notification();

$singleusepassword = rand(100000, 999999);
$timestamp = date('Y-m-d H:i:s', strtotime('+1 day'));

$MobileNumber = $_POST['MobileNumber'];

$stmt = $con->query("SELECT FullName FROM tmp_register WHERE Trim(MobileNumber) = Trim('$MobileNumber')");
$found = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

if ($found) {
    $tableName = 'tmp_register';
} else {
    $tableName = 'registeredusers';
}

$sql2 = "UPDATE $tableName SET SingleUsePassword = '$singleusepassword',SingleUseExpiry = '$timestamp' where MobileNumber = '$MobileNumber'";
$stmt2 = $con->prepare($sql2);
$res2 = $stmt2->execute();

if ($res2 == true) {
    $stmt = $con->query("SELECT * FROM $tableName WHERE Trim(MobileNumber) = Trim('$MobileNumber')");
    $usrRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $sql = "SELECT SmsMessage FROM smstemplates WHERE SmsshortCode = 'OTP'";
    $stmt = $con->query($sql);
    $message = $stmt->fetchColumn();
    $message = str_replace("XXXXXX", $singleusepassword, $message);
    $MobileNumber = '[' . $MobileNumber . ']';

    $objNotification->sendSMS($MobileNumber, $message);

    /*if ($usrRows[0]['Email'] != '') {
        $emailBody = 'Hi ' . $usrRows[0]['FullName'] . ',<br/><br/>' . $message . '<br/><br/>Thanks,<br/>iShareRyde Team';
        $params = array('to' => $usrRows[0]['Email'], 'body' => $emailBody);
        $resp = $objNotification->sendEmailOTP($params);
    }*/
    echo "SUCCESS";
} else {
    echo "FAILURE";
}
