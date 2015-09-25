<h4 class="headingText">Pool Detail</h4>
<div class="articleBorder">
<?php
	$date = '';
	$CabStatus = '';
	$DropStatus = '';
	if(isset($_GET['Date']))
	{
		$date = $_GET['Date'];
	}
	if(isset($_GET['CabStatus']))
	{
		$CabStatus = $_GET['CabStatus'];
	}
	if(isset($_GET['DropStatus']))
	{
		$DropStatus = $_GET['DropStatus'];
	}
	
	echo "Date : ".$date;
	//echo $date;
	echo "<br/>";
	echo "<div class='pure-g' style='font-size:14px; font-weight:bold;'>";									
	echo "</div>";
	echo "<br/>";
	echo "<div class='pure-g' style='font-size:13px;font-weight:bold;'>";
	echo "<div class='pure-u-5-24'><p class='tHeading'>Cab From</p></div>";
	echo "<div class='pure-u-5-24'><p class='tHeading'>Cab To</p></div>";
	echo "<div class='pure-u-4-24'><p class='tHeading'>Owner</p></div>";
	echo "<div class='pure-u-3-24'><p class='tHeading' style='text-align:center;'>No.Of Seat</p></div>";
	echo "<div class='pure-u-3-24'><p class='tHeading' style='text-align:center;'>Time</p></div>";
	echo "<div class='pure-u-4-24'><p class='tHeading'>Members</p></div>";

	echo "</div>";
	if($DropStatus != '')
	{
		if($DropStatus != 'A')
		{
			$stmt = $con->prepare("SELECT *, (SELECT GROUP_CONCAT(`MemberName`)  from `cabmembers` where `CabId` = a.`CabId` and `DropStatus` = 'Yes') AS MemberName From `cabopen` a where a.`TravelDate` = '$date' and `CabId` in (SELECT `CabId`  from `cabmembers` where `CabId` = a.`CabId` and `DropStatus` = 'Yes')");														
		}
		else
		{
			$stmt = $con->prepare("SELECT *, (SELECT GROUP_CONCAT(`MemberName`)  from `cabmembers` where `CabId` = a.`CabId`) AS MemberName From `cabopen` a where a.`TravelDate` = '$date'");														
		}
	}
	else 
	{
		if($CabStatus != '')
		{
			$stmt = $con->prepare("SELECT *, (SELECT GROUP_CONCAT(`MemberName`)  from `cabmembers` where `CabId` = a.`CabId`) AS MemberName From `cabopen` a where a.`TravelDate` = '$date' and a.`CabStatus` = '$CabStatus'");														
		}
		else
		{
			$stmt = $con->prepare("SELECT *, (SELECT GROUP_CONCAT(`MemberName`)  from `cabmembers` where `CabId` = a.`CabId`) AS MemberName From `cabopen` a where a.`TravelDate` = '$date'");														
		}
	}
	if ($stmt->execute())
	{
		$rowCount = (int) $stmt->rowCount();
		$result = $stmt->fetchAll(PDO::FETCH_ASSOC);				
		if ($rowCount > 0)
		{
			foreach ($result as $row) 
			{																
				echo "<div class='pure-g pure-g1' style='font-size:13px;'>";
				echo "<div class='pure-u-5-24'><p>" . $row['FromLocation'] . "</a></p></div>";
				echo "<div class='pure-u-5-24'><p>" . $row['ToLocation'] . " </p></div>";									
				echo "<div class='pure-u-4-24'><p style='margin-left:10px;'>" . $row['OwnerName'] . " </p></div>";	
				echo "<div class='pure-u-3-24'><p style='text-align:center;'>" . $row['Seats'] . " </p></div>";	
				echo "<div class='pure-u-3-24'><p style='text-align:center;'>" . $row['TravelTime'] . " </p></div>";
				echo "<div class='pure-u-4-24'><p>" . $row['MemberName'] . " </p></div>";	
				echo "</div>";
			}											
		}				
		else
		{
			echo "<span style='color:Green;font-size:13px; font-weight:bold;'>No results to display!</span>";
		}
	}	
	else
	{							
		echo "<span style='color:Red;font-size:13px; font-weight:bold;'>Error: " . $con->error . "</span>";
	}	
?>	
	
</div>