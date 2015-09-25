<?php
include('connection.php');

if (isset($_POST['type']) && $_POST['type'] != '' && isset($_POST['provider']) && $_POST['provider'] != '' && isset($_POST['mobileNumber']) && $_POST['mobileNumber'] != '') {

    $sql = "SELECT id, couponName, message FROM coupons c WHERE type='" . $_POST['type'] . "' AND provider='" . $_POST['provider'] . "' AND (NOT EXISTS(SELECT 1 FROM usercoupons uc WHERE uc.couponId = c.id)) LIMIT 1";

    $stmt = $con->query($sql);
    $found = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

    if ($found > 0) {
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        $finalArray['status'] = 'success';
        $finalArray['data'] = $data;
        $finalArray = json_encode($finalArray);
        $couponId = $data['id'];

        $sql = "INSERT INTO usercoupons(MobileNumber,couponId, DateTime) VALUES ('" . $_POST['mobileNumber'] . "', $couponId, now())";
        $nStmt = $con->prepare($sql);
        $nStmt->execute();

        http_response_code(200);
        header('Content-Type: application/json');
        echo $finalArray;
        exit;
    } else {
        http_response_code(500);
        header('Content-Type: application/json');
        echo '{status:"fail", message:"No Coupons available"}';
        exit;
    }
} else {
    http_response_code(500);
    header('Content-Type: application/json');
    echo '{status:"fail", message:"Invalid Params"}';
    exit;
}

