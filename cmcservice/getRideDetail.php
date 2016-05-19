<?php
include('connection.php');

if (isset($_REQUEST['cabId']) && $_REQUEST['cabId'] !='') {
    $cabId = $_REQUEST['cabId'];

    $stmtF = $con->query("SELECT * FROM cabopen WHERE CabId = '$cabId'");
    $cab_exits = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

    if ($cab_exits) {
        $cabDetail = $stmtF->fetch(PDO::FETCH_ASSOC);
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
