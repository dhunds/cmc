<?php
include_once('connection.php');

$resp = array('header' => 500, 'status' => 'fail', 'message' => '');

if (isset($_POST['mobileNumber']) && $_POST['mobileNumber'] != '' && isset($_POST['id']) && $_POST['id'] != '') {

    $sql = "SELECT c.credits as credits FROM credits c JOIN offers o ON c.offerId=o.id WHERE o.status=1 AND o.validThru > NOW()  AND o.id=".$_POST['id']." AND c.mobileNumber='".$_POST['mobileNumber']."'";

    $stmt = $con->query($sql);
    $found = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

    if ($found > 0) {
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        $totalCredits = $data['credits'];

        if ($totalCredits > 0) {
            // Update user credits
            $sql = "UPDATE registeredusers SET totalCredits = totalCredits + $totalCredits WHERE MobileNumber='".$_POST['mobileNumber']."'";
            $stmt = $con->prepare($sql);
            $stmt->execute();

            // Initialize unclaimed credits
            $sql = "UPDATE credits SET credits=0, updated=now() WHERE offerId=".$_POST['id']." AND mobileNumber='".$_POST['mobileNumber']."'";
            $stmt = $con->prepare($sql);
            $stmt->execute();

            $resp = array('header' => 200, 'status' => 'success', 'message' => 'You have earned ' . $totalCredits . ' credits.');
        } else {
            $resp = array('header' => 200, 'status' => 'fail', 'message' => 'You have no credits to claim');
        }

    } else {
        $resp = array('header' => 200, 'status' => 'fail', 'message' => 'This offer has expired');
    }
} else {
    $resp = array('header' => 200, 'status' => 'fail', 'message' => 'Invalid Params');
}

$header = $resp['header'];
unset($resp['header']);

http_response_code($header);
header('Content-Type: application/json');
echo json_encode($resp);
exit;