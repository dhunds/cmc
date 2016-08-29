<?php
include ('connection.php');

$smsTemplate = "Your One-Time-Passsword (OTP) for iShareRyde is ";

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

$phone = $_POST['phone'];
$name = $_POST['name'];
$email = $_POST['email'];
$pwd = $_POST['pwd'];
$dToken = $_POST['dToken'];
$gender = $_POST['gender'];
$dob = $_POST['dob'];
$platform = $_POST['platform'];
$otp = $_POST['otp'];

//echo "".$phone." ".$name." ".$email." ".$pwd." ".$dToken." ".$dob." ".$gender." ".$platform." ".$otp;

$sql = "INSERT INTO `tblregister` ( `phone`, `name`, `email`, `pwd`, `dToken`, `gender`, `dob`, `platform`) VALUES ('$phone','$name','$email','$pwd','$dToken','$gender','$dob','$platform')";
$stmt = $con -> prepare($sql);
if ($stmt -> execute()) {
	echo "_JSUCCESS";
	$phone = '[91' . $phone . ']';
	$sms = $smsTemplate . $otp;
	sendSMS($phone, $sms);
} else {

	$sql = "SELECT * FROM `tblregister` WHERE `phone`='$phone' AND `pwd`='$pwd'";
	$result = $con -> query($sql);
	$no_of_users = $con->query("SELECT FOUND_ROWS()")->fetchColumn();				
	if ($no_of_users > 0) {
		foreach($result as $row)
		{
			if($row['isOtpVerified']==0)
			{
				$phone = '[91' . $phone . ']';
				$sms = $smsTemplate . $otp;
				sendSMS($phone, $sms);
				break;
			}
			else {
				echo "_JSUCCESS_EXIST";
				break;
			}
		}
		
	} else {
		echo "_JFAIL";
	}
}
?>