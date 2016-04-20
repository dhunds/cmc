<?php
include('connection.php');

if (isset($_POST['mobileNumber']) && $_POST['mobileNumber'] !='' && isset($_POST['vehicleId']) && $_POST['vehicleId'] !='' && isset($_POST['isCommercial']) && $_POST['isCommercial'] !='') {

    $mobileNumber = $_POST['mobileNumber'];
    $vehicleId = $_POST['vehicleId'];
    $isCommercial = $_POST['isCommercial'];

    $sql = "INSERT INTO userVehicleDetail(mobileNumber,vehicleId, isCommercial, created) VALUES ('$mobileNumber','$vehicleId','$isCommercial', now())";
    $nStmt = $con->prepare($sql);

    if ($nStmt->execute()){
        http_response_code(200);
        header('Content-Type: application/json');
        echo '{"status":"success", "message":"Vehicle added successfully."}';
        exit;
    } else {
        http_response_code(500);
        header('Content-Type: application/json');
        echo '{"status":"failed", "message":"An error occured, Please try again after sometime."}';
        exit;
    }
} else {
    http_response_code(500);
    header('Content-Type: application/json');
    echo '{"status":"failed", "message":"Invalid Params."}';
    exit;
}
