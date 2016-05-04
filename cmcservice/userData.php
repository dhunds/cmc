<?php
include('connection.php');

$resp = array('header' => 500, 'status' => 'fail', 'message' => '', 'data' => array());

if (isset($_POST['mobileNumber']) && $_POST['mobileNumber'] != '') {

    $sql = "SELECT FullName, Email, referralCode, totalCredits, usedReferralCode as signedUpUsingCode, DeviceToken, status  FROM registeredusers WHERE MobileNumber='".$_POST['mobileNumber']."'";
    $stmt = $con->query($sql);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    $resp = array('header' => 200, 'status' => 'success', 'message' => '', 'data' => $user);

} else {
    $resp = array('header' => 200, 'status' => 'fail', 'message' => 'Invalid Params', 'data' => array());
}

$header = $resp['header'];
unset($resp['header']);

http_response_code($header);
header('Content-Type: application/json');
echo json_encode($resp);
exit;