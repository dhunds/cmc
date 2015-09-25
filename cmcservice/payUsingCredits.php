<?php
include('connection.php');
include('includes/functions.php');

$resp = array('header' => 500, 'status' => 'fail', 'message' => '');

if (isset($_POST['mobileNumber']) && $_POST['mobileNumber'] != '' && isset($_POST['amount']) && $_POST['amount'] != '') {

    $cell = substr(trim($_POST['mobileNumber']), -10);
    $sender = substr(trim($_POST['sender']), -10);
    $amount = $_POST['amount'];
    $comment = 'creditTransfer';
    $orderid = uniqid();
    $typeofmoney = 0;
    $creditmethod = 'cashback';

    /**** Transfer to Referrer ***/

    $string = "'" . $amount . "''" . $cell . "''" . $comment . "''" . $creditmethod . "''" . MERCHANT_NAME . "''" . MID . "''" . $orderid . "''" . $typeofmoney . "''" . WALLET_ID . "'";

    $checksum = hash_hmac('sha256', $string, API_SECRET);

    $fields = array(
        'amount' => $amount,
        'typeofmoney' => $typeofmoney,
        'cell' => $cell,
        'orderid' => $orderid,
        'creditmethod' => $creditmethod,
        'comment' => $comment,
        'walletid' => WALLET_ID,
        'mid' => MID,
        'merchantname' => MERCHANT_NAME,
        'checksum' => $checksum
    );
    $result = transferMoney($fields, LOAD_MONEY_URL);
    $resp = simplexml_load_string($result);

    if ($resp->status == 'SUCCESS') {
        $cell = '0091' . substr(trim($_POST['mobileNumber']), -10);
        $sender = '0091' . substr(trim($_POST['sender']), -10);
        $sql = "INSERT INTO payments(mobileNumber, amount, comment, sender, DateTime) VALUES ('" . $cell . "', '" . $amount . "', '" . $comment . "', '$sender', now())";
        $stmt = $con->prepare($sql);
        $stmt->execute();

        $sql = "UPDATE registeredusers SET totalCredits = (totalCredits - $amount) WHERE MobileNumber='" . $sender . "'";
        $stmt = $con->prepare($sql);
        $stmt->execute();

        if ($sender==$_POST['owner']){
            $sql = "UPDATE cabopen SET settled=1 WHERE CabId = '".$_POST['cabId']."' AND trim(MobileNumber) = '".$sender."'";
        }else{
            $sql = "UPDATE cabmembers SET settled=1 WHERE CabId = '".$_POST['cabId']."' AND trim(MemberNumber) = '".$sender."'";
        }

        $stmt = $con->prepare($sql);
        $res = $stmt->execute();

        $resp = array('header' => 200, 'status' => 'success', 'message' => 'Money Transferred');
    } else {
        $resp = array('header' => 200, 'status' => 'fail', 'message' => 'An error occured!');
    }

} else {
    $resp = array('header' => 200, 'status' => 'fail', 'message' => 'Invalid Params');
}

$header = $resp['header'];
unset($resp['header']);

http_response_code($header);
header('Content-Type: application/json');
echo json_encode($resp);
exit;