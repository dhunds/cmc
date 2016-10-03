<?php
spl_autoload_register(function ($class_name) {
    include 'classes/class.'.strtolower($class_name) . '.php';
});
include('connection.php');
include('includes/offers.php');
include('includes/functions.php');

if (isset($_POST['act']) && $_POST['act'] !='' && isset($_POST['mobileNumber']) && $_POST['mobileNumber'] !='' && isset($_POST['paymentMethod']) && $_POST['paymentMethod'] !='') {
    $paymentMethod = $_POST['paymentMethod'];
    $mobileNumber = $_POST['mobileNumber'];

    if ($paymentMethod=='Cash' && $_POST['act'] == 'checkBalanceForRide'){
        
        $user = getUserByMobileNumber($_POST['mobileNumber']);
        $credit = $user ['totalCredits'];

        $discount = 0;
        $offerDetail = checkForOffers ($mobileNumber);

        if ($offerDetail) {
            $discount = $offerDetail['amount'];
        } else {
            $discount = 0;
        }

        $availableBalance = $credit + $discount;
        $requiredBalance = $_POST['amount'];

        // Amount payable by rider
        if (($discount + $credit) < 1) {
            $payableByRider = $requiredBalance;
        } else {
            if ($requiredBalance <= $discount || ($requiredBalance <= ($discount + $credit))) {
                $payableByRider = 0;
            } else {
                $payableByRider = $requiredBalance - ($discount + $credit);
            }
        }

        if ($requiredBalance > $availableBalance){

            $jsonResp = array("code"=>200, "status" => "success", "walletStatusCode"=>"2", "balance" => $availableBalance, "payableByRider"=>$payableByRider, "message" =>"Insufficient balance for ride");
        } else {

            

            $jsonResp = array("code"=>200, "status" => "success", "walletStatusCode"=>"3", "balance" => $availableBalance, "payableByRider"=>$payableByRider, "message" =>"Balance sufficient for ride");
        }

        setResponse($jsonResp);
    } else {

        $objWallet = new $paymentMethod();

        if ($_POST['act'] == 'saveToken') {

            $resp = $objWallet->saveToken($_POST['mobileNumber'], $_POST['token']);

            if ($resp) {
                setResponse(array("code"=>200, "status"=>"success", "message"=>"Token Saved"));

            } else {
                setResponse(array("code"=>200, "status"=>"fail", "message"=>"An error occured, please try again."));
            }

        } else if($_POST['act'] == 'checkBalance') {

            $resp = $objWallet->checkBalance($_POST['mobileNumber']);

            if ($resp && $resp->status =='SUCCESS') {
                setResponse(array("code"=>200, "status"=>"success", "balance"=>(string)$resp->balanceamount));
            } else {
                setResponse(array("code"=>200, "status"=>"fail", "statuscode"=>(string)$resp->statuscode, 'statusdescription'=>(string)$resp->statusdescription));
            }

        } else if($_POST['act'] == 'checkBalanceForRide') {

            $user = getUserByMobileNumber($_POST['mobileNumber']);
            $mobikwikBalance = 0;

            // Check If wallet is linked
            $resp = $objWallet->getWallet($_POST['mobileNumber']);

            if (empty($resp) || $resp['token'] =='') {
                setResponse(array("code"=>200, "status"=>"success", "walletStatusCode"=>"1", "message"=>"Wallet not linked"));
            }

            // Proceed to balance check if wallet linked
            $resp = $objWallet->checkBalance($_POST['mobileNumber']);

            if ($resp && $resp->status =='SUCCESS') {
                $mobikwikBalance = $resp->balanceamount;
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

            // Amount payable by rider
            if (($discount + $credit) < 1) {
                $payableByRider = $requiredBalance;
            } else {
                if ($requiredBalance <= $discount || ($requiredBalance <= ($discount + $credit))) {
                    $payableByRider = 0;
                } else {
                    $payableByRider = $requiredBalance - ($discount + $credit);
                }
            }

            if ($requiredBalance > $availableBalance){

                $jsonResp = array("code"=>200, "status" => "success", "walletStatusCode"=>"2", "balance" => ($resp->balanceamount + $credit + $discount), "payableByRider"=>$payableByRider, "message" =>"Insufficient balance for ride");
            } else {
                $jsonResp = array("code"=>200, "status" => "success", "walletStatusCode"=>"3", "balance" => ($resp->balanceamount + $credit + $discount), "payableByRider"=>$payableByRider, "message" =>"Balance sufficient for ride");
            }

            setResponse($jsonResp);

        } else if($_POST['act'] == 'getToken') {

            $resp = $objWallet->getWallet($_POST['mobileNumber']);

            if (!empty($resp)) {

                $mobikwikBalance = 0;

                if (isset($resp['token']) && $resp['token'] !=''){
                    $resp = $objWallet->checkBalance($_POST['mobileNumber']);

                    if ($resp && $resp->status =='SUCCESS') {
                        $mobikwikBalance = $resp->balanceamount;
                    }
                }
                setResponse(array("code"=>200, "status"=>"success", "token"=>$resp['token'], "balance"=>$mobikwikBalance));

            } else {
                setResponse(array("code"=>200, "status"=>"fail", "message"=>"No Data"));
            }
        }
    }
} else {
    setResponse(array("code"=>200, "status"=>"fail", "message"=>"Invalid Params"));
}