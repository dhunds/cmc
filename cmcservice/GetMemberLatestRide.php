<?php

include ('connection.php');
	
	
	$MobileNumber = $_POST['MobileNumber'];
	$sql="call fetchmypools('$MobileNumber', @totalRows)";	
	$data = $con->query($sql)->fetchAll(PDO::FETCH_ASSOC);	
	$total_count = $con->query("select @totalRows;")->fetch(PDO::FETCH_ASSOC);
	
	if($total_count["@totalRows"] === NULL)
	{		
		$total_count = 0;		
	}					
	
	if ($total_count > 0) 
	{		
		echo json_encode($data);
	}
	else
	{
		echo "No Pool";
	}
	
	
	/*
//$MobileNumber = $_POST['MobileNumber'];
$MobileNumber = '9810347316';
$sql ="select *,'Y' As IsOwner from `cabopen` where Trim(`mobilenumber`) = '$MobileNumber' union select *,'N'  As IsOwner from `cabopen` where `Cabid` in (SELECT b.`CabId` FROM `cabmembers` b where Trim(`MemberNumber`) = '$MobileNumber'  and `DropStatus` = 'No' AND `CabId` IN(SELECT `CabId` FROM `acceptedrequest` where `CabId` = b.`CabId` and Trim(`MemberNumber`) = b.`MemberNumber`))  ORDER BY `OpenTime` DESC";
//echo $sql;
	$stmt = $con->query($sql);
	$no_of_rows = $stmt->rowCount();

 if ($no_of_rows > 0) 
{
 $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
	echo json_encode($rows);
}
else
{
echo "No Pool Created Yet!!";
}*/
 ?>