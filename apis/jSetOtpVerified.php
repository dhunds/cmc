<?php
include ('connection.php');
$phone = $_GET['phone'];
$sql = "UPDATE `tblregister` SET `isOtpVerified`=1 WHERE `phone`='$phone'";
$stmt = $con -> prepare($sql);
if ($stmt -> execute()) {
	echo "_JSUCCESS";
} else {
	echo "_JFAIL";
}
?>