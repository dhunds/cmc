<?php

include ('connection.php');
	
$CabId = $_POST['CabId'];
$MemberNumber= $_POST['MemberNumber'];

	$stmt = $con->query("SELECT * FROM `acceptedrequest` WHERE (`CabId` = '$CabId' AND `Status` != 'Dropped' AND `MemberNumber` != '$MemberNumber')");
	//$no_of_rows = $stmt->rowCount();
	$no_of_rows = $con->query("SELECT FOUND_ROWS()")->fetchColumn();
 if ($no_of_rows > 0) 
{
 $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
	echo json_encode($rows);
}
else
{
echo "No Members joined yet";
}
 ?>