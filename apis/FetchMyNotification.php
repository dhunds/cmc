<?php

include ('connection.php');
	
 $MobileNumber = $_POST['MobileNumber'];
 //$MobileNumber='9958211837';
	//$stmt = $con->query("SELECT * FROM `cabopen` WHERE `CabId` IN (SELECT b.`CabId` FROM `cabmembers` b where TRIM(`MemberNumber`) = '$MobileNumber'  and `DropStatus` = 'No' AND `CabId` NOT IN(SELECT `CabId` FROM `acceptedrequest` where `CabId` = b.`CabId` and `MemberNumber` = b.`MemberNumber`)) ORDER BY `OpenTime` DESC");
	
	$sql = "SELECT *,CONCAT((`Seats`-`RemainingSeats`) ,'/', `Seats`) AS Seat_Status FROM `cabopen` WHERE `CabStatus` = 'A' and `CabId` IN (SELECT b.`CabId` FROM `cabmembers` b where TRIM(`MemberNumber`) = '$MobileNumber'  and `DropStatus` = 'No') AND `CabId` NOT IN(SELECT `CabId` FROM `acceptedrequest` where TRIM(`MemberNumber`) = '$MobileNumber') ORDER BY `OpenTime` DESC";
	$stmt = $con->query($sql);
	//$no_of_rows = $stmt->rowCount();
	$no_of_rows = $con->query("SELECT FOUND_ROWS()")->fetchColumn();
 if ($no_of_rows > 0)
{
 $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
	echo json_encode($rows);
}
else
{
echo "No Notification !!";
}
 ?>