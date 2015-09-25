<?php
include ('connection.php');

	$CabDetailID = '';
	if(isset($_REQUEST['CabDetailID']))
	{
		$CabDetailID = $_REQUEST['CabDetailID'];
	}
	$CabID = '';
	if(isset($_REQUEST['CabID']))
	{
		$CabID = $_REQUEST['CabID'];
	}
	$Rating = '';
	if(isset($_REQUEST['Rating']))
	{
		$Rating = $_REQUEST['Rating'];
	}
	$MobileNumber = '';
	if(isset($_REQUEST['MobileNumber']))
	{
		$MobileNumber = $_REQUEST['MobileNumber'];
	}
	
	$sql2 = "INSERT INTO `cabrating`(`CabDetailID`, `CabID`, `Rating`,`MobileNumber`) VALUES ('$CabDetailID', '$CabID', '$Rating','$MobileNumber')";
	$stmt2 = $con->prepare($sql2);
	$res2 = $stmt2->execute();
	if(res2 == true)
	{
		
		$sql123 = "UPDATE `cabopen` set `CabStatus` = 'C' where `CabID` = '$CabID'";
		$stmt123 = $con->prepare($sql123);
		$res123 = $stmt123->execute();
		echo "SUCCESS";
	}
	else
	{
		echo "FAILURE";
	}

?>