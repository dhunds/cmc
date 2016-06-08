<?php
include('connection.php');

$resp = array('header' => 500, 'status' => 'fail', 'message' => '', 'data' => array());

if (isset($_POST['mobileNumber']) && $_POST['mobileNumber'] != '') {

    $sql = "SELECT FullName, Email, referralCode, totalCredits, usedReferralCode as signedUpUsingCode, DeviceToken, status, mobikwikToken  FROM registeredusers WHERE MobileNumber='".$_POST['mobileNumber']."'";
    $stmt = $con->query($sql);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    $sql = "SELECT id FROM offers WHERE status=1 AND validThru > now() AND type='referral'";
    $stmt = $con->query($sql);
    $found = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

    if (!$found) {
        $user['referralCode'] ="";
    }

    $sql = "SELECT id FROM cabOwners WHERE mobileNumber='".$_POST['mobileNumber']."'";
    $stmt = $con->query($sql);
    $found = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

    if ($found) {
        $user['type'] =2;
    } else {
        $user['type'] =1;
    }

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