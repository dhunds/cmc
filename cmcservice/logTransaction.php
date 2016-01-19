<?php
include('connection.php');

if (isset($_POST['cabId']) && $_POST['cabId'] != '') {
    $orderId = uniqid();
    $sql = "INSERT INTO transactionLogs SET
          merchantId='" . $_POST['mid'] . "',
          merchantname='" . $_POST['merchantname'] . "',
          orderId='" . $orderId . "',
          token='" . $_POST['token'] . "',
          senderNumber='0091" . $_POST['sendercell'] . "',
          receiverNumber='0091" . $_POST['receivercell'] . "',
          amount='" . $_POST['amount'] . "',
          fee='" . $_POST['fee'] . "',
          cabId='" . $_POST['cabId'] . "',
          transactionOn=now()";
    $stmt = $con->prepare($sql);
    $res = $stmt->execute();

    if ($res == true) {
        http_response_code(200);
        header('Content-Type: application/json');
        echo '{"status":"success", "message":"Data Saved", "orderId":"'.$orderId.'"}';
        exit;
    } else {
        http_response_code(500);
        header('Content-Type: application/json');
        echo '{"status":"fail", "message":"Something went wrong. Please try again later."}';
        exit;
    }
} else {
    http_response_code(500);
    header('Content-Type: application/json');
    echo '{"status":"fail", "message":"Invalid Params"}';
    exit;
}