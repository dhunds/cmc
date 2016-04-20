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

	$fields = array('amount' => $amount, 'fee' => $fee, 'merchantname' => $merchantname, 'mid' => $mid, 'orderid' => $orderid, 'receivercell' => $receivercell, 'sendercell' => $sendercell, 'token' => $token, 'checksum' => $checksum);

	//$headers = 'payloadtype=json';
	$strParams = http_build_query($fields);
    $url = PEER_TRANSFER_URL . '?' . $strParams;
		
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => $url,
        CURLOPT_HTTPHEADER => array('Content-Type: application/json')
    ));
    $result = curl_exec($curl);
    curl_close($curl);

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

            $jsonResp = array('status'=>$resp->status, 'statuscode'=>$resp->statuscode, 'statusdescription'=>$resp->statusdescription, 'amount'=>$resp->amount, 'orderid'=>$resp->orderid, 'refId'=>$resp->refId, 'checksum'=>$resp->checksum);
        } else {
        	$jsonResp = array('status'=>$resp->status, 'statuscode'=>$resp->statuscode, 'statusdescription'=>$resp->statusdescription);
        }
        //$jsonResp = [];
        //$jsonResp['status'] = $resp->status;
        //$jsonResp['statuscode'] = $resp->statuscode;
        
        //echo json_encode($jsonResp);
        //die;
        //echo $resp->status;die;
        //$response['status'] = 'success';
		//$response['data'] = $jsonResp;

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
