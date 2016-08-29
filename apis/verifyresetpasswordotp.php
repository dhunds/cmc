<?php
include ('connection.php');

$MobileNumber = $_REQUEST['MobileNumber'];
$resetpasswordotp = $_REQUEST['resetpasswordotp'];
$userpassword = $_REQUEST['userpassword'];

$stmt1 = $con->query("SELECT * FROM `registeredusers` WHERE `MobileNumber` = '$MobileNumber' and `ResetPasswordOTP` = '$resetpasswordotp'");
//$rows = $stmt1->fetchAll(PDO::FETCH_ASSOC);
//$user_exists = count($rows);
$user_exists = $con->query("SELECT FOUND_ROWS()")->fetchColumn();
if($user_exists == 0)
{
	echo "FAILURE";
}
else
{
	$sql2 = "UPDATE `registeredusers` SET `Password` = '$userpassword', `ResetPasswordOTP` = '' where `MobileNumber` = '$MobileNumber' and `ResetPasswordOTP` = '$resetpasswordotp'";
	$stmt2 = $con->prepare($sql2);
	$res2 = $stmt2->execute();
	echo "SUCCESS";

}


?>