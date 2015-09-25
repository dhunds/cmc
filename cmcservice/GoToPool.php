<?php

include ('connection.php');
	
$CabId = $_POST['CabId'];

	$stmt = $con->query("SELECT a.*,CONCAT((a.`Seats`-a.`RemainingSeats`) ,'/', `Seats`) AS Seat_Status, (SELECT `imagename` from `userprofileimage` where `MobileNumber` = a.`MobileNumber`) as `imagename`,(SELECT `BookingRefNo` from `cmccabrecords` where `cabid` = a.`cabid` order by `ReqId` desc LIMIT 1) AS `BookingRefNo`,(SELECT cabnames.`CabName` from `cmccabrecords`, cabnames where `cmccabrecords`.CabNameID = cabnames.CabNameID AND cmccabrecords.`cabid` = a.`cabid` order by `ReqId` desc LIMIT 1) AS `CabName`,
(SELECT `DriverName` from `cmccabrecords` where `cabid` = a.`cabid` order by `ReqId` desc LIMIT 1) AS `DriverName`,(SELECT `DriverNumber` from `cmccabrecords` where `cabid` = a.`cabid` order by `ReqId` desc LIMIT 1) AS `DriverNumber`,(SELECT `CarNumber` from `cmccabrecords` where `cabid` = a.`cabid` order by `ReqId` desc LIMIT 1) AS `CarNumber`,(SELECT `CarType` from `cmccabrecords` where `cabid` = a.`cabid` order by `ReqId` desc LIMIT 1) AS `CarType` FROM `cabopen` a WHERE a.`CabId` = '$CabId' AND a.`CabStatus` = 'A'");
	//$no_of_rows = $stmt->rowCount();
	$no_of_rows = $con->query("SELECT FOUND_ROWS()")->fetchColumn();
 if ($no_of_rows > 0)
{
 $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
	echo json_encode($rows);
}
else
{
echo "This Ride no longer exist";
}
 ?>