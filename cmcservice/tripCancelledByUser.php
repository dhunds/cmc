<?php
include('connection.php');

if (isset($_POST['sendercell']) && $_POST['sendercell'] != '') {
    $sendercell = $_POST['sendercell'];
} else {
    $error = 1;
}

if (isset($_POST['receivercell']) && $_POST['receivercell'] != '') {
    $receivercell = $_POST['receivercell'];
} else {
    $error = 1;
}

if (isset($_POST['amount']) && $_POST['amount'] != '') {
    $amount = $_POST['amount'];
} else {
    $error = 1;
}

if (isset($_POST['fee']) && $_POST['fee'] != '') {
    $fee = $_POST['fee'];
} else {
    $error = 1;
}

if (isset($_POST['orderid']) && $_POST['orderid'] != '') {
    $orderid = $_POST['orderid'];
} else {
    $error = 1;
}

if (isset($_POST['token']) && $_POST['token'] != '') {
    $token = $_POST['token'];
} else {
    $error = 1;
}

if (isset($_POST['mid']) && $_POST['mid'] != '') {
    $mid = $_POST['mid'];
} else {
    $error = 1;
}

if (isset($_POST['merchantname']) && $_POST['merchantname'] != '') {
    $merchantname = $_POST['merchantname'];
} else {
    $error = 1;
}

if (!$error) {

	$string = "'".$amount ."''". $fee ."''". $merchantname ."''". $mid ."''". $orderid ."''". $receivercell ."''". $sendercell ."''". $token ."'";

    $checksum = hash_hmac('sha256', $string, API_SECRET);

	$result = mobikwikTransfers($amount, $fee, $merchantname, $mid, $orderid, $receivercell, $sendercell, $token, $checksum);
    
    if ($result === FALSE) {
        http_response_code(500);
	    header('Content-Type: application/json');
	    echo '{"status":"failed", "message":"An Error occured, Please try later."}';
	    exit;
    } else {
        $resp = simplexml_load_string($result);
        
        if ($resp->status =='SUCCESS'){
            $cell = '0091'.substr(trim($_POST['cell']), -10);
            $sql = "INSERT INTO driverPayments(mobileNumberFrom, mobileNumberTo, transactionId, amount, transactionDate, cabId, status) VALUES ('" . $sendercell . "', '" . $resp->receivercell . "', '" . $resp->refId . "', '" . $resp->amount . "', now(), '" . $_POST['cabId'] . "', '" . $resp->status . "')";
            $nStmt = $con->prepare($sql);
            $nStmt->execute();

            $jsonResp = array('status'=>(string)$resp->status, 'statuscode'=>(string)$resp->statuscode, 'statusdescription'=>(string)$resp->statusdescription, 'amount'=>(string)$resp->amount, 'orderid'=>(string)$resp->orderid, 'refId'=>(string)$resp->refId, 'checksum'=>(string)$resp->checksum);
        } else {
        	$jsonResp = array('status'=>(string)$resp->status, 'statuscode'=>(string)$resp->statuscode, 'statusdescription'=>(string)$resp->statusdescription);
        }

		http_response_code(200);
		header('Content-Type: application/json');
		echo json_encode($jsonResp);
		exit;
    }
} else {
    http_response_code(500);
    header('Content-Type: application/json');
    echo '{"status":"failed", "message":"Invalid Params."}';
    exit;
}
