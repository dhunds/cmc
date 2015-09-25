<?php

include ('connection.php');
	
$MobileNumber = $_POST['MobileNumber'];
$Password = $_POST['Password'];

	//$stmt = $con->query("SELECT * FROM `registeredusers` WHERE (`MobileNumber` = '$MobileNumber')");
	$stmt = $con->query("UPDATE `registeredusers` SET `Password`='$Password' Where `MobileNumber`='$MobileNumber'");
	//$no_of_users = $stmt->rowCount();
	$no_of_users = $con->query("SELECT FOUND_ROWS()")->fetchColumn();
 if ($no_of_users > 0)
{
 $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
	echo json_encode($rows);
}
else
{
echo "error";
}
 ?>