<?php
include('connection.php');
include_once('classes/class.notification.php');
$objNotification = new Notification();

$MobileNumber = $_POST['MobileNumber'];

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

    /*if ($usrRows[0]['Email'] != '') {
        $emailBody = 'Hi ' . $usrRows[0]['FullName'] . ',<br/><br/>' . $message . '<br/><br/>Thanks,<br/>ClubMyCab Team';
        $params = array('to' => $usrRows[0]['Email'], 'body' => $emailBody);
        $resp = $objNotification->sendEmailOTP($params);
    }*/

    $MobileNumber = $_POST['MobileNumber'];

    $sql2 = "UPDATE registeredusers SET SingleUsePassword='$singleusepassword', SingleUseExpiry= '$timestamp' WHERE Trim(MobileNumber) = Trim('$MobileNumber')";
    $stmt2 = $con->prepare($sql2);
    $res2 = $stmt2->execute();
    echo json_encode($usrRows);
} else {
    echo "login error";
}

function sendSMS($nos, $message)
{
    $ch1 = curl_init();
    $fields_string = '';
    $fieldsNew = array(
        'Message' => $message,
        'Numbers' => $nos
    );
    foreach ($fieldsNew as $key => $value) {
        $fields_string .= $key . '=' . urlencode($value) . '&';
    }
    rtrim($fields_string, '&');
    curl_setopt($ch1, CURLOPT_URL, "http://127.0.0.1/cmc/cmcservice/sendsms.php");
    curl_setopt($ch1, CURLOPT_POST, true);
    curl_setopt($ch1, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch1, CURLOPT_POSTFIELDS, $fields_string);
    $resultNew = curl_exec($ch1);
}
