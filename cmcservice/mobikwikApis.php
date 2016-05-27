<?php

include('connection.php');
include('includes/offers.php');
include('includes/functions.php');

if (isset($_POST['act']) && $_POST['act'] !='' && isset($_POST['mobileNumber']) && $_POST['mobileNumber'] !='') {
    $mobileNumber = $_POST['mobileNumber'];

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
    } else if($_POST['act'] == 'checkBalanceForRide') {

        $user = getUserByMobileNumber($_POST['mobileNumber']);
        $mobikwikBalance = 0;

        if ($user['mobikwikToken'] !='') {
            $resp = checkMobikwikWalletBalance($_POST['mobileNumber']);
            if ($resp && $resp->status =='SUCCESS') {
                $mobikwikBalance = $resp->balanceamount;
            }
        }
        $discount = 0;
        $credit = $user ['totalCredits'];

        $offerDetail = checkForOffers ($mobileNumber);

        if ($offerDetail) {
            $discount = $offerDetail['amount'];
        } else {
            $discount = 0;
        }

        // Check User Balance (including discounts and credits) for the ride
        $availableBalance = $credit + $discount + $mobikwikBalance;
        $requiredBalance = $_POST['amount'];

        if ($requiredBalance > $availableBalance){
            $jsonResp = array("status" => "success", "balance" => ($resp->balanceamount + $credit + $discount), "message" =>"Insufficient balance for ride");
        } else {
            $jsonResp = array("status" => "success", "balance" => ($resp->balanceamount + $credit + $discount), "message" =>"Balance sufficient for ride");
        }

        http_response_code(200);
        header('Content-Type: application/json');
        echo json_encode($jsonResp);
        exit;
    } else if($_POST['act'] == 'getToken') {

        $res = getMobikwikToken($_POST['mobileNumber']);

        if ($res) {
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

