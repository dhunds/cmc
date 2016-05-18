<?php
include ('connection.php');
include_once('includes/offers.php');

if (isset($_POST['mobileNumber']) && $_POST['mobileNumber'] !='') {
    $offer = checkOffers($_POST['mobileNumber']);

    if ($offer) {
        $resp = array('header' => 200, 'status' => 'success', 'data' => $offer);
        http_response_code(200);
        header('Content-Type: application/json');
        echo json_encode($resp);
        exit;
    } else {
        $resp = array('header' => 200, 'status' => 'success', 'message' => '', 'data' => $offer);
        http_response_code(200);
        header('Content-Type: application/json');
        echo json_encode($resp);
        exit;
    }

} else {
    http_response_code(200);
    header('Content-Type: application/json');
    echo '{status:"fail", message:"Invalid Params"}';
    exit;
}