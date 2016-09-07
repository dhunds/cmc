<?php
include('connection.php');
include('includes/functions.php');
include_once('classes/class.notification.php');
$objNotification = new Notification();

$singleusepassword = rand(100000, 999999);
$timestamp = date('Y-m-d H:i:s', strtotime('+1 day'));

$MobileNumber = $_POST['MobileNumber'];

$socialId = $_POST['socialId'];

if ($socialId !='') {
    //Check Referral Code
    if (isset($_POST['referralCode']) && $_POST['referralCode'] != '') {
        $referralCode = $_POST['referralCode'];

        if (isReferralCodeUnique($_POST['referralCode'])) {
            http_response_code(200);
            header('Content-Type: application/json');
            echo '{"status":"fail", "message":"Invalid Referral Code"}';
            exit;
        }

        $sql = "SELECT id FROM offers WHERE status=1 AND validThru > now() AND type='referral'";
        $stmt = $con->query($sql);
        $found = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

        if (!$found) {
            http_response_code(200);
            header('Content-Type: application/json');
            echo '{"status":"success", "message":"Referral programme is closed now"}';
            exit;
        }
    }
    // End Referral Code

    $sql = "SELECT FullName FROM tmp_register WHERE socialId = Trim('$socialId')";

} else{
    $sql = "SELECT FullName FROM tmp_register WHERE Trim(MobileNumber) = Trim('$MobileNumber')";
}

$stmt = $con->query($sql);
$found = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

if ($found) {
    $tableName = 'tmp_register';
} else {
    $tableName = 'registeredusers';
}

if ($socialId !='') {
    if ($referralCode) {
        $sql2 = "UPDATE $tableName SET usedReferralCode = '$referralCode', SingleUsePassword = '$singleusepassword', SingleUseExpiry = '$timestamp', MobileNumber = '$MobileNumber' where socialId = '$socialId'";

    } else {
        $sql2 = "UPDATE $tableName SET SingleUsePassword = '$singleusepassword', SingleUseExpiry = '$timestamp', MobileNumber = '$MobileNumber' where socialId = '$socialId'";
    }
} else {
    $sql2 = "UPDATE $tableName SET SingleUsePassword = '$singleusepassword',SingleUseExpiry = '$timestamp' where MobileNumber = '$MobileNumber'";
}

$stmt2 = $con->prepare($sql2);
$res2 = $stmt2->execute();

if ($res2 == true) {
    $stmt = $con->query("SELECT * FROM $tableName WHERE Trim(MobileNumber) = Trim('$MobileNumber')");
    $usrRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $sql = "SELECT SmsMessage FROM smstemplates WHERE SmsshortCode = 'OTP'";
    $stmt = $con->query($sql);
    $message = $stmt->fetchColumn();
    $message = str_replace("XXXXXX", $singleusepassword, $message);
    $MobileNumber = '[' . substr(trim($_POST['MobileNumber']), -10) . ']';

    $objNotification->sendSMS($MobileNumber, $message);

    http_response_code(200);
    header('Content-Type: application/json');
    echo '{"status":"success", "message":"OTP sent."}';
    exit;
} else {
    http_response_code(200);
    header('Content-Type: application/json');
    echo '{"status":"fail", "message":"An error occured, please try again later."}';
    exit;
}