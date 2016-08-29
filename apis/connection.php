<?php
$dsn = 'mysql:host=localhost;dbname=cmcdev';
$us = 'root';
$pa = 'root';

try {
    $con = new PDO($dsn, $us, $pa);
} catch (PDOException $e) {
    $error_message = $e->getMessage();
    //include('../errors/err.php');
    echo $error_message;
    exit();
}
define("BASEURL", "http://localhost/cmc/cmcservice");
define("BASE_URL", "http://localhost/cmc");
define("ENV", "dev");

/*** Mobikwik Development Credentials*/

define("MID", "MBK9005");
define("MID_LOADMONEY", "MBK9002");
define("MERCHANT_NAME", "MyMerchantName");
define("MERCHANT_NUMBER", "9013287269");
define("API_SECRET", "ju6tygh7u7tdg554k098ujd5468o");
define("LOAD_MONEY_URL", "https://test.mobikwik.com/mobikwik/loadmoney");
define("MOBIKWIK_TOKEN_REGENERATE_KEY", "lu6tygh7u7tdg554k098ujd5468o");
define("TOKEN_REGENERATE_URL", "https://test.mobikwik.com/mobikwik/tokenregenerate");
define("MOBIKWIK_BALANCE_CHECK_URL", "https://test.mobikwik.com/mobikwik/userbalance");
define("PEER_TRANSFER_URL", "https://test.mobikwik.com/mobikwik/initiatePeerTransfer");
define("DEBIT_API", "https://test.mobikwik.com/debitwallet");
define("WALLET_ID", "testapisupport@gmail.com");

define("CMC_KEY", "NrD4LMv5xGAXdYvxvzzcxASUgWQkKcZx");
define("OLA_APP_TOKEN", "7e22de1177fb4ac4b173a8653c72e1f3");
define("SEARCH_RIDE_PROXIMITY", 5);

// Uber Dev Credentials

/*define("CLIENT_ID", "b_jm7Egafm62U_Xtfg131aVHXy2Di-aL");
define("SERVER_TOKEN", "fKQmJ56Nlp8oIzYuFCgCCvVkYDjKJvwo0iwYEW6l");
define("CLIENT_SECRET", "u2datGAvQRbgNxj7kNzOXxQhDw8K0vIa98LMpmoO");
define("UBER_BASE_URL", "https://sandbox-api.uber.com/v1/");
define("AUTHORIZATION_ENDPOINT", "https://login.uber.com/oauth/authorize");
define("TOKEN_ENDPOINT", "https://login.uber.com/oauth/token");*/

// Uber Live Credentials
define("CLIENT_ID", "-Q9kL1722l5XeCTUK5vS8YHiY0mJUijo");
define("SERVER_TOKEN", "l4534hMQeJg5JREbbhRfOHJuM9-UbwFKPfrYpwAi");
define("CLIENT_SECRET", "02UIbwOsXX1FXGPdOyU_95jjuW60YBKJAJd4seBX");
define("UBER_BASE_URL", "https://api.uber.com/v1/");
define("AUTHORIZATION_ENDPOINT", "https://login.uber.com/oauth/authorize");
define("TOKEN_ENDPOINT", "https://login.uber.com/oauth/token");

$arrAPI = array(
    'openacab.php',
    'fetchimagename.php',
    //'Store_Club.php',
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
    //'FetchMyPools.php',
    //'fetchmypoolshistory.php',
    //'customerQuery.php',
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
    //'fetchCabDetailsNew.php',
    'cabbookrequest.php',
    'MegaApi.php',
    'logTransaction.php',
    'getMyFare.php',
    //'updateCabStatus.php',
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
    'sharelocationtomembers.php',
    //'mobikwikPayments.php'

);

$uri = $_SERVER['PHP_SELF'];
$link_array = explode('/',$uri);
$page = end($link_array);

if (in_array($page, $arrAPI)) {
    $string = '';
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

        foreach ($arrParams as $value) {
            $string .= $value;
        }
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

