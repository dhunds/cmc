<?php
include('connection.php');
include_once('classes/class.notification.php');

$objNotification = new Notification();

if (isset($_POST['mobileNumber']) && $_POST['mobileNumber'] != '') {
	$orderid = uniqid();
	$typeofmoney = 0;
	$creditmethod = 'cashback';

	$sql = "SELECT * FROM payments WHERE status=0 AND comment='referral' AND mobileNumber='".$_POST['mobileNumber']."' AND sender
!='system'";
	$stmt = $con->query($sql);
	$numRows = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

	if ($numRows > 0) {
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		$cell = substr(trim($row['mobileNumber']), -10);

		$string = "'" . $row['amount'] . "''" . $cell . "''" . $row['comment'] . "''" . $creditmethod . "''" . MERCHANT_NAME . "''" . MID . "''" . $orderid . "''" . $typeofmoney . "''" . WALLET_ID . "'";

		$checksum = hash_hmac('sha256', $string, API_SECRET);

		$fields = array('amount' => $row['amount'], 'typeofmoney' => $typeofmoney, 'cell' => $cell, 'orderid' => $orderid,
			'creditmethod' => $creditmethod, 'comment' => $row['comment'], 'walletid' => WALLET_ID, 'mid' => MID,'merchantname' => MERCHANT_NAME, 'checksum' => $checksum);

		$result = transferMoney($fields, LOAD_MONEY_URL);
		$resp = simplexml_load_string($result);

		$Message = 'You got Rs.' . $row['amount'] . ' for joining ClubMyCab using a referral code. Amount added to your Mobikwik wallet';

		logTransaction($row, $objNotification, $resp, $Message);

		http_response_code(200);
		header('Content-Type: application/json');
		echo '{status:"success", message:"User wallet credited with referral bonus."}';
		exit;
	}
} else {
	http_response_code(500);
	header('Content-Type: application/json');
	echo '{status:"fail", message:"Invalid Params"}';
	exit;
}

function transferMoney($fields, $merchantURL){
	$strParams = http_build_query($fields);
	$url = $merchantURL . '?' . $strParams;

	$curl = curl_init();
	curl_setopt_array($curl, array(
		CURLOPT_RETURNTRANSFER => 1,
		CURLOPT_URL => $url,
		CURLOPT_HTTPHEADER => array('Content-Type: application/json')
	));
	$result = curl_exec($curl);
	curl_close($curl);
	return $result;
}

function logTransaction($fields, $objNotification, $resp, $Message){
	global $con;

	$sql = '';
	if ($resp->status == 'SUCCESS') {
		$sql = "UPDATE payments SET status=1 WHERE id=".$fields['id'];
	} elseif ($resp->status == 'FAILURE' && $resp->statuscode=='120') {
		$sql = "UPDATE payments SET attempts=attempts+1 WHERE id=".$fields['id'];
	}

	if ($sql !='') {
		$stmt = $con->prepare($sql);
		$stmt->execute();
	}

	$sql = "SELECT DeviceToken, FullName FROM registeredusers WHERE referralCode='" . $fields['mobileNumber'] . "'";
	$stmt = $con->query($sql);
	$user = $stmt->fetch(PDO::FETCH_ASSOC);

	$sql = "SELECT FullName, DeviceToken FROM registeredusers WHERE MobileNumber='" . $fields['sender'] . "'";
	$stmt = $con->query($sql);
	$senderData = $stmt->fetch(PDO::FETCH_ASSOC);

	if ($resp->status == 'SUCCESS') {
		$body = array('gcmText' => $Message, 'pushfrom' => 'genericnotification');
		$gcm_array[] = $user['DeviceToken'];
		$objNotification->setVariables($gcm_array, $body);
		$objNotification->sendGCMNotification();

		$sql = "INSERT INTO notifications(NotificationType, SentMemberName, SentMemberNumber, ReceiveMemberName, ReceiveMemberNumber,Message, DateTime) VALUES ('Referral_Bonus','".$senderData['FullName']."','".$fields['sender']."','".$user['FullName']."','".$fields['mobileNumber']."','$Message',now())";
		$stmt = $con->prepare($sql);
		$resp = $stmt->execute();
		return $resp;
	} else {
		return true;
	}
}