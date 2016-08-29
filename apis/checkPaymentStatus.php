<?php
include('connection.php');

if (isset($_POST['mobileNumber']) && $_POST['mobileNumber'] !='') {
	$sql = "SELECT id FROM payments WHERE mobileNumber = '".$_POST['mobileNumber']."' AND comment='referral' AND status=0 AND sender !='system'";

	$stmt = $con->query($sql);
	$numRows = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

	if ($numRows > 0) {
		http_response_code(200);
		header('Content-Type: application/json');
		echo '{status:"fail", message:"Payment failed"}';
		exit;
	} else {
		http_response_code(200);
		header('Content-Type: application/json');
		echo '{status:"success", message:"Payment successful"}';
		exit;
	}
}