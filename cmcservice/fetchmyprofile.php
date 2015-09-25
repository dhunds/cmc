<?php

include ('connection.php');

$UserNumber = $_POST['UserNumber'];

	$stmt = $con->query("SELECT * FROM `registeredusers` WHERE `MobileNumber` = '$UserNumber'");
	//$no_of_users = $stmt->rowCount();
	$no_of_users = $con->query("SELECT FOUND_ROWS()")->fetchColumn();
 if ($no_of_users > 0) 
{
 $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
	echo json_encode($rows);
}
else
{
echo "No Data";
}
 ?>