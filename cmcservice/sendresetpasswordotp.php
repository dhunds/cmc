<?php
include ('connection.php');

function sendSMS($nos,$message)
{
$ch1 = curl_init();
$fields_string = '';
$fieldsNew = array(
 'Message' => $message,
 'Numbers' => $nos
 );
foreach($fieldsNew as $key=>$value) { $fields_string .= $key.'='. urlencode($value) .'&'; }
rtrim($fields_string, '&');	
curl_setopt($ch1, CURLOPT_URL, "http://127.0.0.1/cmc/cmcservice/sendsms.php");
curl_setopt($ch1, CURLOPT_POST, true);
curl_setopt($ch1, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch1, CURLOPT_POSTFIELDS, $fields_string);
$resultNew = curl_exec($ch1);
}

$singleusepassword = rand(100000,999999);
$MobileNumber = $_REQUEST['MobileNumber'];

$stmt = $con->query("Select * From `registeredusers` Where `MobileNumber`='$MobileNumber'");
//$no_of_users = $stmt->rowCount();
$no_of_users = $con->query("SELECT FOUND_ROWS()")->fetchColumn();	
if ($no_of_users > 0)
{
	$sql2 = "UPDATE `registeredusers` SET `ResetPasswordOTP` = '$singleusepassword' where `MobileNumber` = '$MobileNumber'";
	$stmt2 = $con->prepare($sql2);
	$res2 = $stmt2->execute();
	if(res2 == true)
	{
		$sql = "SELECT `SmsMessage` FROM `smstemplates` WHERE `SmsshortCode` = 'OTP'";
		$stmt = $con->query($sql);
		$message = $stmt->fetchColumn();
		$message = str_replace("XXXXXX", $singleusepassword ,$message);
		$MobileNumber = '[' . $MobileNumber . ']';
		sendSMS($MobileNumber, $message);	
		echo "SUCCESS";
	}
	else
	{
		echo "FAILURE";
	}
}
else
{
	echo 'FAILURE';
}





?>