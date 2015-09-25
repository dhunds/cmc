<?php

    $dsn = 'mysql:host=localhost;dbname=cmcdev';
    $us = 'root';
    $pa = 'Vbh4{tc+Nb';

    try {
        $con = new PDO($dsn, $us, $pa);
    } catch (PDOException $e) {
        $error_message = $e->getMessage();
        //include('../errors/err.php');
        echo $error_message;
        exit();
    }
define("MID", "MBK9002");
define("MERCHANT_NAME", "MyMerchantName");
define("API_SECRET", "ju6tygh7u7tdg554k098ujd5468o");
define("LOAD_MONEY_URL", "https://test.mobikwik.com/mobikwik/loadmoney");
define("WALLET_ID", "testapisupport@gmail.com");
define("CMC_KEY", "NrD4LMv5xGAXdYvxvzzcxASUgWQkKcZx");

$arrAPI = array(    
    'openacab.php',
    'fetchimagename.php',
    'Store_Club.php',
    'referFriendRideStepOne.php',
    'ownerinvitefriends.php',
    'Fetch_Club.php',
    'getCoupons.php',
    'checkPaymentStatus.php',
    'processPendingTransactions.php',
    'UpdateNotificationStatusToRead.php',
    'FetchUnreadNotificationCount.php',
    'imageupload.php',
    'fetchmyprofile.php',
    'rideInvitations.php',
    'login.php',
    'removeuserfromclub.php',
    'addmoreuserstoclub.php',
    'removeclub.php',
    'referralCode.php',
    'Leave_Club.php',
    'updatemyprofile.php',
    'FetchMyPools.php',
    'fetchmypoolshistory.php',
    'customerQuery.php',
    'changenotificationstatus.php',
    'changenotificationstatusasread.php',
    'FetchMyAllNotification.php',
    'swipetodeletenotification.php',
    'GoToPool.php',
    'referFriendRideStepTwo.php',
    'referFriendStepTwo.php',
    'getCabs.php',
    'verifyotp.php',
    'verifyloginotp.php',
    'resendotp.php',
    'cmcCabRating.php',
    'userregister.php',
    'updatepushnotificationstatus.php',
    'fetchCabDetailsNew.php',
    'cabbookrequest.php',    
    'MegaApi.php',
    'logTransaction.php',
    'getMyFare.php',
    'updateCabStatus.php',
    'startTripNotification.php',
    'ShowMemberOnMap.php',
    'cancelpoolbyowner.php',
    'dropuserfrompopup.php',
    'sendcustommessage.php',
    'sendchatnotification.php',
    'tripcompletedmembers.php',
    'saveCalculatedFare.php',
    'tripCompleted.php',
    'checkpoolalreadyjoined.php',
    'joinpool.php',
    'dropapool.php',
    'updateOwnerLocation.php',
    'updatelocationpool.php',
    'sharelocationtomembers.php'
);

$uri = $_SERVER['PHP_SELF'];
$link_array = explode('/',$uri);
$page = end($link_array);

if (in_array($page, $arrAPI)) {

    if (count($_REQUEST) > 0) {
        $arrParams = $_REQUEST;
        $arrParams = [];

        foreach ($_REQUEST as $key => $value) {
            if ($key != 'auth' && $value != '') {
                $key = strtolower($key);
                $arrParams[$key] = $value;
            }
        }

        ksort($arrParams);
        $string = '';

        foreach ($arrParams as $value) {
            $string .= $value;
        }

        $auth = hash_hmac('sha256', $string, CMC_KEY);

        if ($auth != $_POST['auth']) {
            http_response_code(200);
            header('Content-Type: application/json');
            echo '{"status":"fail", "message":"Unauthorized Access"}';
            exit;
        }
	unset($arrParams, $string, $value, $auth);
    }
}

