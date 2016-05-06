<?php

include('connection.php');
include('includes/functions.php');

if (isset($_POST['act']) && $_POST['act'] !='' && isset($_POST['mobileNumber']) && $_POST['mobileNumber'] !='') {

    if ($_POST['act'] == 'saveToken') {

        $resp = saveMobikwikToken($_POST['mobileNumber'], $_POST['token']);

        if ($resp) {
            http_response_code(200);
            header('Content-Type: application/json');
            echo '{"status":"success", "message":"Token Saved"}';
            exit;
        } else {
            http_response_code(200);
            header('Content-Type: application/json');
            echo '{"status":"fail", "message":"An error occured, please try again."}';
            exit;
        }

    } else if($_POST['act'] == 'checkBalance') {
        $resp = checkMobikwikWalletBalance($_POST['mobileNumber']);

        if ($resp && $resp->status =='SUCCESS') {
            http_response_code(200);
            header('Content-Type: application/json');
            echo '{"status":"success", "balance":"'.$resp->balanceamount.'"}';
            exit;
        } else {
            $jsonResp = array('status'=>'fail', 'statuscode'=>(string)$resp->statuscode, 'statusdescription'=>(string)$resp->statusdescription);
            http_response_code(200);
            header('Content-Type: application/json');
            echo json_encode($jsonResp);
            exit;
        }
    } else if($_POST['act'] == 'getToken') {

        $res = getMobikwikToken($_POST['mobileNumber']);

        if ($res) {
            $user = $stmt->fetch();
            echo '{"status":"success", "token":"'.$res.'"}';
            exit;
        } else {
            http_response_code(200);
            header('Content-Type: application/json');
            echo '{"status":"fail", "message":"No Data"}';
            exit;
        }
    }
} else {
    http_response_code(200);
    header('Content-Type: application/json');
    echo '{"status":"fail", "message":"Invalid Params"}';
    exit;
}

