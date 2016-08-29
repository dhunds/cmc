<?php

include ('connection.php');
	
$Code = $_POST['Code'];

	$stmt = $con->query("SELECT * FROM `country` WHERE `countryCode` = 'IN'");
	//$no_of_rows = $stmt->rowCount();
	$no_of_rows = $con->query("SELECT FOUND_ROWS()")->fetchColumn();
 if ($no_of_rows > 0) 
{
 $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
	echo json_encode($rows);
}
else
{
echo "No !!";
}
 ?>