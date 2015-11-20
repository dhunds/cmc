<?php
include('connection.php');
include('includes/functions.php');
include_once('classes/class.notification.php');
$objNotification = new Notification();

$FullName = $_POST['FullName'];
$Password = $_POST['Password'];
$MobileNumber = $_POST['MobileNumber'];
$DeviceToken = $_POST['DeviceToken'];
$Email = $_POST['Email'];
$Gender = $_POST['Gender'];
$DOB = $_POST['DOB'];
$Platform = $_POST['Platform'];
$referralCode = '';

$singleusepassword = rand(100000, 999999);
$timestamp = date('Y-m-d H:i:s', strtotime('+1 day'));

if ($FullName != '' && $MobileNumber != '') {
    if (isset($_POST['referralCode']) && $_POST['referralCode'] != '') {
        $referralCode = $_POST['referralCode'];

        if (isReferralCodeUnique($_POST['referralCode'])) {
            http_response_code(500);
            header('Content-Type: application/json');
            echo '{"status":"fail", "message":"Invalid Referral Code"}';
            exit;
        }

        $sql = "SELECT id FROM offers WHERE status=1 AND validThru > now() AND type='referral'";
        $stmt = $con->query($sql);
        $found = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

        if (!$found) {
            http_response_code(500);
            header('Content-Type: application/json');
            echo '{"status":"success", "message":"Referral programme is closed now"}';
            exit;
        }
    }

    $code = '';
    do {
        $code = randomString();
    } while (!isReferralCodeUnique($code));

    $sql = "SELECT FullName FROM registeredusers WHERE MobileNumber='" . $MobileNumber . "'";
    $con->query($sql);
    $found = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

    if ($found > 0) {
        http_response_code(500);
        header('Content-Type: application/json');
        echo '{"status":"fail", "message":"Mobile number already exists. Please try to login or register with a different mobile number"}';
        exit;
    }

    $stmt = $con->prepare("DELETE FROM  tmp_register WHERE MobileNumber='" . $MobileNumber . "'");
    $stmt->execute();

    $sql = "INSERT INTO tmp_register(FullName, Password, MobileNumber, DeviceToken, Email, Gender, DOB, Platform, SingleUsePassword,SingleUseExpiry,CreatedOn, referralCode, usedReferralCode) VALUES ('$FullName','$Password', '$MobileNumber','$DeviceToken','$Email','$Gender', '$DOB','$Platform','$singleusepassword', '$timestamp',now(),'$code', '$referralCode')";
    $stmt = $con->prepare($sql);
    $res = $stmt->execute();

    if ($res == true) {
        /** Send SMS **/
        $sql = "SELECT SmsMessage FROM smstemplates WHERE SmsshortCode = 'OTP'";
        $stmt = $con->query($sql);
        $message = $stmt->fetchColumn();
        $message = str_replace("XXXXXX", $singleusepassword, $message);
        $MobileNumber = '[' . $MobileNumber . ']';
        $objNotification->sendSMS($MobileNumber, $message);

        /*if ($Email != '') {
            $emailBody = 'Hi ' . $FullName . ',<br/><br/>' . $message . '<br/><br/>Thanks,<br/>ClubMyCab Team';
            $params = array('to' => $Email, 'body' => $emailBody);
            $resp = $objNotification->sendEmailOTP($params);
        }*/

        http_response_code(200);
        header('Content-Type: application/json');
        echo '{"status":"success", "message":"success"}';
        exit;
    } else {
        http_response_code(500);
        header('Content-Type: application/json');
        echo '{"status":"fail", "message":"Mobile number already exists. Please try to login or register with a different mobile number"}';
        exit;
    }
} else {
    http_response_code(500);
    header('Content-Type: application/json');
    echo '{"status":"fail", "message":"Invalid Params"}';
    exit;
}


