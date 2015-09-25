<?php
 
include ('connection.php');

$CabID = '';
if(isset($_REQUEST['CabID']))
{
	$CabID = $_REQUEST['CabID'];
	
	$FromLoc='';
	$ToLoc='';
	$FromCity= '';
	$ToCity= '';

	$stmtF = $con->query("SELECT * FROM `cabopen` WHERE `CabID` = '$CabID'");	
	$no_of_users = $con->query("SELECT FOUND_ROWS()")->fetchColumn();	
	if ($no_of_users > 0) 
	{
		while($row = $stmtF->fetch())
		{	
			$FromLoc = $row["FromLocation"];			
			$FromLoc = str_replace (" ", "+", $FromLoc);
			$fullurl = "http://maps.googleapis.com/maps/api/geocode/json?address=$FromLoc&sensor=true";
			$sData = file_get_contents($fullurl); // get json content
			$json_a = json_decode($sData, true); //json decode
			foreach($json_a['results'][0]['address_components'] as $item)
			{
				if($item["types"][0] == "locality")
				{
					$FromCity = $item["long_name"];
					break;
				}
			}
			
			$ToLoc = $row["ToLocation"];			
			$ToLoc = str_replace (" ", "+", $ToLoc);
			$fullurl = "http://maps.googleapis.com/maps/api/geocode/json?address=$ToLoc&sensor=true";
			$sData = file_get_contents($fullurl); // get json content
			$json_a = json_decode($sData, true); //json decode
			foreach($json_a['results'][0]['address_components'] as $item)
			{
				if($item["types"][0] == "locality")
				{
					$ToCity = $item["long_name"];
					break;
				}
			}
		}
	}
	
	if($ToCity == '')
	{
		$fromgroup = '';
		$fromstmt = $con->query("SELECT `CityGroup` FROM `groupcities` WHERE `City` = '$FromCity'");
		//$cityRows = $fromstmt->rowCount();
		$cityRows = $con->query("SELECT FOUND_ROWS()")->fetchColumn();
		if($cityRows > 0)
		{
			$fromgroup  = $fromstmt->fetchColumn(); 
		}	
		if($fromgroup != '')
		{
			$FromCity = $fromgroup;
		}

		$sql = "SELECT a.*,b.`CabName`, b.`CabMobileSite`,b.`CabMode`,b.`CabPackageName`, (SELECT `ModeName` FROM `cabmode` where `ModeID` = b.`CabMode`)  As ModeName, ((SELECT SUM(`Rating`) FROM `cabrating` where `CabDetailID` = a.`CabDetailID`)/(SELECT COUNT(*) FROM `cabrating` where `CabDetailID` = a.`CabDetailID`)) As Rating, '' as timeEstimate, '' as priceEstimate, '' as product_id FROM `cabdetails` a , `cabnames` b WHERE a.`City` = '$FromCity' AND a.`Active` = 1 and b.`CabNameID` = a.`CabNameID` and a.`OutStation` = 'N'";	
	}
	else
	{
		if($FromCity == $ToCity)
		{
			$fromgroup = '';
			$fromstmt = $con->query("SELECT `CityGroup` FROM `groupcities` WHERE `City` = '$FromCity'");
			//$cityRows = $fromstmt->rowCount();
			$cityRows = $con->query("SELECT FOUND_ROWS()")->fetchColumn();
			if($cityRows > 0)
			{
				$fromgroup  = $fromstmt->fetchColumn(); 
			}	
			if($fromgroup != '')
			{
				$FromCity = $fromgroup;
			}
			$sql = "SELECT a.*,b.`CabName`, b.`CabMobileSite`,b.`CabMode`,b.`CabPackageName`, (SELECT `ModeName` FROM `cabmode` where `ModeID` = b.`CabMode`)  As ModeName, ((SELECT SUM(`Rating`) FROM `cabrating` where `CabDetailID` = a.`CabDetailID`)/(SELECT COUNT(*) FROM `cabrating` where `CabDetailID` = a.`CabDetailID`)) As Rating, '' as timeEstimate, '' as priceEstimate, '' as product_id FROM `cabdetails` a , `cabnames` b WHERE a.`City` = '$FromCity' AND a.`Active` = 1 and b.`CabNameID` = a.`CabNameID` and a.`OutStation` = 'N'";		
		}
		else
		{
			$fromgroup = '';
			$fromstmt = $con->query("SELECT `CityGroup` FROM `groupcities` WHERE `City` = '$FromCity'");
			//$cityRows = $fromstmt->rowCount();
			$cityRows = $con->query("SELECT FOUND_ROWS()")->fetchColumn();
			if($cityRows > 0)
			{
				$fromgroup  = $fromstmt->fetchColumn(); 
			}			
			
			$togroup = '';
			$tostmt = $con->query("SELECT `CityGroup` FROM `groupcities` WHERE `City` = '$ToCity'");
			//$cityRows = $tostmt->rowCount();
			$cityRows = $con->query("SELECT FOUND_ROWS()")->fetchColumn();
			if($cityRows > 0)
			{
				$togroup  = $tostmt->fetchColumn(); 
			}			
			
			if($fromgroup == $togroup)
			{
				$sql = "SELECT a.*,b.`CabName`, b.`CabMobileSite`,b.`CabMode`,b.`CabPackageName`, (SELECT `ModeName` FROM `cabmode` where `ModeID` = b.`CabMode`)  As ModeName, ((SELECT SUM(`Rating`) FROM `cabrating` where `CabDetailID` = a.`CabDetailID`)/(SELECT COUNT(*) FROM `cabrating` where `CabDetailID` = a.`CabDetailID`)) As Rating, '' as timeEstimate, '' as priceEstimate, '' as product_id FROM `cabdetails` a , `cabnames` b WHERE a.`City` = '$fromgroup' AND a.`Active` = 1 and b.`CabNameID` = a.`CabNameID` and a.`OutStation` = 'N'";						
			}
			else
			{
				$sql = "SELECT a.*,b.`CabName`, b.`CabMobileSite`,b.`CabMode`,b.`CabPackageName`, (SELECT `ModeName` FROM `cabmode` where `ModeID` = b.`CabMode`)  As ModeName, ((SELECT SUM(`Rating`) FROM `cabrating` where `CabDetailID` = a.`CabDetailID`)/(SELECT COUNT(*) FROM `cabrating` where `CabDetailID` = a.`CabDetailID`)) As Rating, '' as timeEstimate, '' as priceEstimate, '' as product_id FROM `cabdetails` a , `cabnames` b WHERE a.`City` = '$FromCity' AND a.`Active` = 1 and b.`CabNameID` = a.`CabNameID` and a.`OutStation` = 'Y'";			
			}	
		}
	}

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
		echo "No Cabs found";
	}
}
else
{
	echo "No Cabs found";
}	

?>