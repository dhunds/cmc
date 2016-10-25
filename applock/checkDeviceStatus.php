<?php
include('connection.php');
include('functions.php');

$errors = checkPostForBlank (array('mobileNumber','imei', 'timestamp', 'hash' ));

if (!$errors) {

    $mobileNumber = trim($_POST['mobileNumber']);
    $imei = trim($_POST['imei']);
    $timestamp = trim($_POST['timestamp']);
    $hash = trim($_POST['hash']);

    $messageString = $imei.$timestamp;
    $auth = hash_hmac('sha256', $messageString, CMC_KEY);
    $messageKey = substr($auth, 0, 10);

    if ($messageKey == $imei) {

        $sql = "SELECT *  FROM deviceDetails WHERE IMEI1='".$imei."' OR IMEI2='".$imei."'";
        $stmt = $con->query($sql);
        $found = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

        if ($found) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            $messageBody = 'STATUS:'.$user['status'].PHP_EOL.'TIMESTAMP:'.$timestamp.PHP_EOL.'NEXTCHECK:'.strtotime($user['nextCheckDateTime']).PHP_EOL.'HASH:'.$messageKey;

            if (sendSMS($mobileNumber, $messageBody)){
                $resp = array('code' => 200, 'status' => 'success', 'message' => 'Message sent.');
            } else {
                $resp = array('code' => 200, 'status' => 'fail', 'message' => 'An error occured.');
            }
        } else{
            $resp = array('code' => 200, 'status' => 'fail', 'message' => 'Device not found.');
        }


    } else {
        $resp = array('code' => 200, 'status' => 'fail', 'message' => 'Invalid Hash');
    }

} else {
    $resp = array('code' => 200, 'status' => 'fail', 'message' => 'Invalid Params');
}

setResponse($resp);