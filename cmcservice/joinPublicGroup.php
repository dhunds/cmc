<?php
include ('connection.php');

if (isset($_POST['poolId']) && isset($_POST['mobileNumber']) && $_POST['poolId'] !='' && $_POST['mobileNumber'] !='') {
    $poolId = $_POST['poolId'];
    $mobileNumber = trim($_POST['mobileNumber']);
    $memberName = $_POST['memberName'];

    list($sLat, $sLon) = explode(',', $_POST['sLatLon']);
    list($eLat, $eLon) = explode(',', $_POST['eLatLon']);

    $sql = "INSERT INTO userpoolsslave(PoolId,MemberName,MemberNumber, IsActive) VALUES  ('$poolId','$memberName','$mobileNumber', '1')";
    $stmt = $con->prepare($sql);
    $res = $stmt->execute();

    if ($res) {
        http_response_code(200);
        header('Content-Type: application/json');
        echo '{status:"success", message:"User added to group"}';
        exit;
    } else {
        http_response_code(200);
        header('Content-Type: application/json');
        echo '{status:"fail", message:"An error occured, Please try later. "}';
        exit;
    }
} else {
    http_response_code(500);
    header('Content-Type: application/json');
    echo '{"status":"failed", "message":"Invalid Params."}';
}
