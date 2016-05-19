<?php
include('connection.php');

if (isset($_REQUEST['cabId']) && $_REQUEST['cabId'] !='') {
    $cabId = $_REQUEST['cabId'];

    $stmt = $con->query("SELECT co.*, ui.imagename FROM cabopen co JOIN userprofileimage ui ON ui.MobileNumber=co.MobileNumber WHERE co.CabId = '$cabId'");
    $cab_exits = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

    if ($cab_exits) {
        $cabDetail = $stmt->fetch(PDO::FETCH_ASSOC);
        http_response_code(200);
        $resp = array("status"=>"success", "data"=>$cabDetail, "data"=>$cabDetail);
        header('Content-Type: application/json');
        echo json_encode($resp);
        exit;
    } else {
        http_response_code(200);
        header('Content-Type: application/json');
        echo '{"status":"fail", "message":"Invalid cab Id"}';
        exit;
    }
} else {
    http_response_code(200);
    header('Content-Type: application/json');
    echo '{"status":"fail", "message":"Invalid Params"}';
    exit;
}
