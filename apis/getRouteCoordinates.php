<?php
include('connection.php');

/**
 * Api Endpoint - getRouteCoordinates.php
 * Short Description  -  Send route coordinates to plot route
 */

if (isset($_POST['routeId']) && $_POST['routeId'] !='') {
    $stmt = $con->query("SELECT coordinates, atTime, routeId, updatedOn FROM routelogs WHERE routeId='".$_POST['routeId']."'");
    $numRows = $con->query("SELECT FOUND_ROWS()")->fetchColumn();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($numRows < 1){
        http_response_code(200);
        header('Content-Type: application/json');
        echo '{status:"fail", message:"Invalid Route"}';
        exit;
    } else  {
        $resp['status']='success';
        $resp['data']=$row;

        http_response_code(200);
        header('Content-Type: application/json');
        echo json_encode($resp);
        exit;
    }
} else {
    http_response_code(500);
    header('Content-Type: application/json');
    echo '{"status":"failed", "message":"Invalid Params."}';
}
