<?php
include('connection.php');
include_once('classes/class.notification.php');

define("MID", "MBK9002");
define("MERCHANT_NAME", "MyMerchantName");
define("API_SECRET", "ju6tygh7u7tdg554k098ujd5468o");
define("LOAD_MONEY_URL", "https://test.mobikwik.com/mobikwik/loadmoney");
define("WALLET_ID", "testapisupport@gmail.com");

$objNotification = new Notification();

if (isset($_POST['mobileNumber']) && $_POST['mobileNumber'] != '') {
	$sql = "SELECT referralCode FROM registeredusers WHERE MobileNumber='" . $_POST['mobileNumber'] . "'";
	$stmt = $con->query($sql);
	$user = $stmt->fetch(PDO::FETCH_ASSOC);

	$sql = "SELECT amount, isActive FROM referral";
	$stmt1 = $con->query($sql);
	$referral = $stmt1->fetch(PDO::FETCH_ASSOC);

	if ($referral['isActive'] == 1 && $user['referralCode'] != '') {
		$finalArray['status'] = 'success';
		$finalArray['data'] = array('referralCode' => $user['referralCode'], 'amount' => $referral['amount']);
		$finalArray = json_encode($finalArray);

		http_response_code(200);
		header('Content-Type: application/json');
		echo $finalArray;
		exit;
	} else {
		http_response_code(500);
		header('Content-Type: application/json');
		echo '{status:"fail", message:"Referral Code Not Available"}';
		exit;
	}
}
/**** Top Up Account ********/

if (isset($_POST['act']) && $_POST['act'] = 'topup' && isset($_POST['senderNumber']) && $_POST['senderNumber'] != '' && isset($_POST['referralCode']) && $_POST['referralCode'] != '') {

	$orderid = uniqid();
	$typeofmoney = 0;
	$creditmethod = 'cashback';
	$error = 0;

	$sql = "SELECT MobileNumber, DeviceToken, FullName FROM registeredusers WHERE referralCode='" . $_POST['referralCode'] . "'";
	$stmt = $con->query($sql);
	$user = $stmt->fetch(PDO::FETCH_ASSOC);
	$cell = substr(trim($user['MobileNumber']), -10);

	$sql = "SELECT amount FROM referral";
	$stmt = $con->query($sql);
	$referral = $stmt->fetch(PDO::FETCH_ASSOC);
	$amount = $referral['amount'];

	$sql = "SELECT MobileNumber, FullName, DeviceToken FROM registeredusers WHERE MobileNumber='" . $_POST['senderNumber'] . "'";
	$stmt = $con->query($sql);
	$senderData = $stmt->fetch(PDO::FETCH_ASSOC);


	if (isset($_POST['senderNumber']) && $_POST['senderNumber'] != '') {
		$sender = $_POST['senderNumber'];
	} else {
		$sender = '';
	}

	$comment = 'referral';

	if (!$error) {
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
			$Message = 'You got Rs.' . $amount . '! ' . $senderData['FullName'] . ' joined iShareRyde with your referral code. Amount added to your Mobikwik wallet';
			$log = logTransaction($cell, $amount, $comment, $sender, $Message, $user['DeviceToken'], $objNotification, "Referral_Bonus", $senderData['FullName'], $user['FullName'], $user['MobileNumber']);
		}

		/*** Transfer to User ***/
		$orderid = uniqid();
		$cell = substr(trim($sender), -10);

		$string = "'" . $amount . "''" . $cell . "''" . $comment . "''" . $creditmethod . "''" . MERCHANT_NAME . "''" . MID . "''" . $orderid . "''" . $typeofmoney . "''" . WALLET_ID . "'";
		$checksum = hash_hmac('sha256', $string, API_SECRET);

		$fields['orderid'] = $orderid;
		$fields['cell'] = $cell;
		$fields['checksum'] = $checksum;

		$result1 = transferMoney($fields, LOAD_MONEY_URL);
		$resp1 = simplexml_load_string($result1);

		if ($resp1->status == 'SUCCESS') {
			$Message = 'You got Rs.' . $amount . ' for joining iShareRyde using a referral code. Amount added to your Mobikwik wallet';
			$log = logTransaction($cell, $amount, $comment, $user['MobileNumber'], $Message, $senderData['DeviceToken'], $objNotification, "Referral_Bonus", $user['FullName'], $senderData['FullName'],
				$senderData['MobileNumber']);
		}
		//echo '<pre>';
		//print_r($resp);
		//print_r($resp);die;
		if ($resp->status == 'SUCCESS' && $resp1->status == 'SUCCESS') {
			http_response_code(200);
			header('Content-Type: application/json');
			echo '{status:"success", message:"Referral bonus transferred to beneficiaries wallet"}';
			exit;
		} elseif ($resp->status == 'SUCCESS' || $resp1->status == 'SUCCESS') {

			if ($resp->status != 'SUCCESS') {
				$msg = 'User wallet transfer Failed';
			}
			else {
				$msg = 'Referrer wallet transfer Failed';
			}
			http_response_code(200);
			header('Content-Type: application/json');
			echo '{status:"success", message:"' . $msg . '"}';
			exit;
		}
		else {
			http_response_code(500);
			header('Content-Type: application/json');
			echo '{status:"fail", message:"' . $resp->statusdescription . '"}';
			exit;
		}
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

function logTransaction($cell, $amount, $comment, $sender, $Message, $token, $objNotification, $NotificationType,
	$senderName, $MemberName, $MemberNumber){
	global $con;
	$sql = "INSERT INTO payments(mobileNumber, amount, comment, sender, DateTime) VALUES ('" . $cell . "', '" . $amount . "', '" . $comment . "', '$sender', now())";
	$stmt = $con->prepare($sql);
	$stmt->execute();

	$body = array('gcmText' => $Message, 'pushfrom' => 'genericnotification');
	$gcm_array[] = $token;
	$objNotification->setVariables($gcm_array, $body);
	$objNotification->sendGCMNotification();


	$sql = "INSERT INTO notifications(NotificationType, SentMemberName, SentMemberNumber, ReceiveMemberName, ReceiveMemberNumber, Message, DateTime) VALUES ('$NotificationType','$senderName','$sender','$MemberName','$MemberNumber','$Message',now())";
	$stmt = $con->prepare($sql);
	$resp = $stmt->execute();
	return $resp;
}




