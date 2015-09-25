<?php
include('connection.php');
include_once('classes/class.notification.php');
include_once('includes/functions.php');

$objNotification = new Notification();

if (isset($_POST['mobileNumber']) && $_POST['mobileNumber'] != '') {

	$sql = "SELECT amount, maxUseLimit, isActive FROM referral";
	$stmt1 = $con->query($sql);
	$referral = $stmt1->fetch(PDO::FETCH_ASSOC);

	$sql = "SELECT COUNT(id) as totalReferrals FROM payments WHERE sender='" . $_POST['mobileNumber'] . "' AND comment='referral'";
	$stmt = $con->query($sql);
	$refered = $stmt->fetch(PDO::FETCH_ASSOC);

	$sql = "SELECT referralCode FROM registeredusers WHERE MobileNumber='" . $_POST['mobileNumber'] . "'";
	$stmt = $con->query($sql);
	$user = $stmt->fetch(PDO::FETCH_ASSOC);

	if ($referral['isActive'] == 1 && $user['referralCode'] != '') {
		$finalArray['status'] = 'success';
		$finalArray['data'] = array(
			'referralCode' => $user['referralCode'],
			'amount' => $referral['amount'],
			'maxUseLimit' => $referral['maxUseLimit'],
			'totalReferrals' => $refered['totalReferrals']
		);
		$finalArray = json_encode($finalArray);

		http_response_code(200);
		header('Content-Type: application/json');
		echo $finalArray;
		exit;
	}
	else {
		http_response_code(500);
		header('Content-Type: application/json');
		echo '{status:"fail", message:"Referral Code Not Available"}';
		exit;
	}
}