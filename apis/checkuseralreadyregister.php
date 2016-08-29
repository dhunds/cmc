<?php

include ('connection.php');
	
$ProfileId = $_POST['ProfileId'];

	$stmt = $con->query("SELECT * FROM `registeredusers` WHERE `ProfileId` = '$ProfileId'");
	//$no_of_users = $stmt->rowCount();
	$no_of_users = $con->query("SELECT FOUND_ROWS()")->fetchColumn();
 if ($no_of_users > 0) 
{
 $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
	echo json_encode($rows);
}
else
{
echo "freshuser";
}
 ?>