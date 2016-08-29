<?php

include ('connection.php');

$UserNumber = $_POST['MobileNumber'];

	$stmt = $con->query("SELECT `imagename` FROM `userprofileimage` WHERE `MobileNumber` = '$UserNumber'");
	//$no_of_users = $stmt->rowCount();
	$no_of_users = $con->query("SELECT FOUND_ROWS()")->fetchColumn();
 if ($no_of_users > 0) 
{
	$result = $stmt->fetchColumn();
	echo $result;
}
else
{
echo "No Data";
}
 ?>