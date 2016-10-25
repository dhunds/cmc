<?php
include('connection.php');
include('functions.php');

if (isset($_POST['mobileNumber']) && $_POST['mobileNumber'] != '') {

    $messageString = 'Testing Message';

    $auth = hash_hmac('sha256', $messageString, CMC_KEY);

    $messageKey = substr($auth, 0, 10);
    $messageString = $messageString.PHP_EOL.$messageKey;

    if (sendSMS($_POST['mobileNumber'], $messageString)){
        $resp = array('code' => 200, 'status' => 'success', 'message' => 'Message sent.');
    } else {
        $resp = array('code' => 200, 'status' => 'fail', 'message' => 'An error occured.');
    }

} else {
    $resp = array('code' => 200, 'status' => 'fail', 'message' => 'Invalid Params');
}

setResponse($resp);