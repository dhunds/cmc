<?php
spl_autoload_register(function ($class_name) {
    include 'classes/class.'.strtolower($class_name) . '.php';
});
include('connection.php');
include('includes/offers.php');
include('includes/functions.php');

if (isset($_POST['act']) && $_POST['act'] !='' && isset($_POST['mobileNumber']) && $_POST['mobileNumber'] !='' && isset($_POST['paymentMethod']) && $_POST['paymentMethod'] !='') {
    $paymentMethod = $_POST['paymentMethod'];
    $objMobikwik = new $paymentMethod();
    
    $mobileNumber = $_POST['mobileNumber'];

    if ($_POST['act'] == 'saveToken') {

        $resp = $objMobikwik->saveToken($_POST['mobileNumber'], $_POST['token']);

        if ($resp) {
            setResponse(array("code"=>200, "status"=>"success", "message"=>"Token Saved"));

        } else {
            setResponse(array("code"=>200, "status"=>"fail", "message"=>"An error occured, please try again."));
        }

    } else if($_POST['act'] == 'checkBalance') {

        $resp = $objMobikwik->checkBalance($_POST['mobileNumber']);

        if ($resp && $resp->status =='SUCCESS') {
            setResponse(array("code"=>200, "status"=>"success", "balance"=>$resp->balanceamount));
        } else {
            setResponse(array("code"=>200, "status"=>"fail", "statuscode"=>(string)$resp->statuscode, 'statusdescription'=>(string)$resp->statusdescription));
        }

    } else if($_POST['act'] == 'checkBalanceForRide') {

        $user = getUserByMobileNumber($_POST['mobileNumber']);
        $resp = $objMobikwik->getWallet($_POST['mobileNumber']);
        $mobikwikBalance = 0;

        if (!empty($resp) && $resp['token'] !='') {
            $resp = $objMobikwik->checkBalance($_POST['mobileNumber']);

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

            $jsonResp = array("code"=>200, "status" => "success", "balance" => ($resp->balanceamount + $credit + $discount), "message" =>"Insufficient balance for ride");
        } else {

            $jsonResp = array("code"=>200, "status" => "success", "balance" => ($resp->balanceamount + $credit + $discount), "message" =>"Balance sufficient for ride");
        }

        setResponse($jsonResp);

    } else if($_POST['act'] == 'getToken') {

        $resp = $objMobikwik->getWallet($_POST['mobileNumber']);

        if (!empty($res)) {
            setResponse(array("code"=>200, "status"=>"success", "token"=>$res['token']));

        } else {
            setResponse(array("code"=>200, "status"=>"fail", "message"=>"No Data"));
        }
    }
} else {
    setResponse(array("code"=>200, "status"=>"fail", "message"=>"Invalid Params"));
}