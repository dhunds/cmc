<?php
		
	$Mode = urldecode($_GET['Mode']);	
	
	if($Mode=='LLU')
	{
		$dir='http://122.160.103.25/php/ClubMyCab/ProfileImages/';

		echo "<h4 class='headingText'>Last Logged In Users</h4>";
		echo "<div class='articleBorder'>";
		echo "<div class='pure-g' style='font-size:13px;font-weight:bold;'>";
		echo "<div class='pure-u-4-24'><p class='tHeading'>Name</p></div>";
		echo "<div class='pure-u-4-24'><p class='tHeading'>Number</p></div>";
		echo "<div class='pure-u-4-24'><p class='tHeading'>Image</p></div>";
		echo "<div class='pure-u-4-24'><p class='tHeading'>CabId</p></div>";
		echo "<div class='pure-u-8-24'><p class='tHeading'>Last Login DateTime</p></div>";
		echo "</div>";
		$stmt=$con->prepare("select a.`FullName`,a.`MobileNumber`,a.`LastLoginDateTime`,(Select `imagename` From `userprofileimage` where `MobileNumber`=a.`MobileNumber` LIMIT 1)As imagename,(select `CabId` from `cabmembers` where `MobileNumber`=a.`MobileNumber` LIMIT 1)As CabId From `registeredusers` a WHERE DATE_FORMAT(`LastLoginDateTime`,'%d/%m/%Y')= DATE_FORMAT(NOW(),'%d/%m/%Y')");
				
		if ($stmt->execute())
		{
			$rowCount = (int) $stmt->rowCount();		
			$result = $stmt->fetchAll(PDO::FETCH_ASSOC);				
			if ($rowCount > 0)
			{
				foreach ($result as $row) 
				{																
					echo "<div class='pure-g pure-g1' style='font-size:13px;'>";
					echo "<div class='pure-u-4-24'><p>" . $row['FullName'] . " </p></div>";
					echo "<div class='pure-u-4-24'><p>" . $row['MobileNumber'] . " </p></div>";	
					echo "<div class='pure-u-4-24'><p><img src=" . $dir . $row['imagename']. " alt='' width='80' /></p></div>";
					echo "<div class='pure-u-4-24'><p>" . $row['CabId'] . " </p></div>";								
					echo "<div class='pure-u-8-24'><p>" . $row['LastLoginDateTime'] . " </p></div>";	
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
		echo "</div>";		
	}	
	else if($Mode =='UP')
	{			
		echo "<h4 class='headingText'>Users In Rides</h4>";
		echo "<div class='articleBorder'>";
		echo "<div class='pure-g' style='font-size:13px;font-weight:bold;'>";
		echo "<div class='pure-u-4-24'><p class='tHeading'>CabId</p></div>";
		echo "<div class='pure-u-4-24'><p class='tHeading'>Owner</p></div>";
		echo "<div class='pure-u-4-24'><p class='tHeading'>From</p></div>";
		echo "<div class='pure-u-4-24'><p class='tHeading'>To</p></div>";
		echo "<div class='pure-u-4-24'><p class='tHeading'>Cab Open Time</p></div>";
		echo "<div class='pure-u-4-24'><p class='tHeading'>Members</p></div>";
		echo "</div>";
			
		$stmt = $con->prepare("SELECT a.*,(SELECT  GROUP_CONCAT(`MemberName`) FROM `acceptedrequest` where `CabId` = a.`CabId` and `Status` != 'Dropped') As MembersName FROM `cabopen` a");
		if ($stmt->execute())
		{
			$rowCount = (int) $stmt->rowCount();		
			$result = $stmt->fetchAll(PDO::FETCH_ASSOC);				
			if ($rowCount > 0)
			{
				foreach ($result as $row) 
				{																
					echo "<div class='pure-g pure-g1' style='font-size:13px;'>";
					echo "<div class='pure-u-4-24'><p>" . $row['CabId'] . " </p></div>";
					echo "<div class='pure-u-4-24'><p>" . $row['OwnerName'] . " </p></div>";	
					echo "<div class='pure-u-4-24'><p>" . $row['FromLocation'] . "</a></p></div>";
					echo "<div class='pure-u-4-24'><p>" . $row['ToLocation'] . " </p></div>";								
					echo "<div class='pure-u-4-24'><p>" . $row['OpenTime'] . " </p></div>";	
					echo "<div class='pure-u-4-24'><p>" . $row['MembersName'] . " </p></div>";	
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
		echo "</div>";	
	}
	else	
	{	
		if($Mode=='S')
		{
			echo "<h4 class='headingText'>Created Rides</h4>";
		}
		if($Mode=='C')
		{
			echo "<h4 class='headingText'>Completed Rides</h4>";
		}
		if($Mode=='I')
		{
			echo "<h4 class='headingText'>Archived Rides</h4>";
		}		
		if($Mode=='A')
		{
			echo "<h4 class='headingText'>Active Rides</h4>";
		}
		echo "<div class='articleBorder'>";		
		echo "<div class='pure-g' style='font-size:13px;font-weight:bold;'>";
		echo "<div class='pure-u-4-24'><p class='tHeading'>CabId</p></div>";
		echo "<div class='pure-u-4-24'><p class='tHeading'>Owner</p></div>";
		echo "<div class='pure-u-5-24'><p class='tHeading'>From</p></div>";
		echo "<div class='pure-u-4-24'><p class='tHeading'>To</p></div>";
		echo "<div class='pure-u-3-24'><p class='tHeading'>Remaining Seats</p></div>";
		echo "<div class='pure-u-4-24'><p class='tHeading'>Cab Open Time</p></div>";	
		echo "</div>";
		if($Mode=='S')
		{
            $sql = "Select * From `cabopen` WHERE 1";
		}				
		else
		{
            $sql = "Select * From `cabopen` Where `CabStatus`='$Mode'";
		}
        $condition = '';
        if ($_GET['from'] != '' && $_GET['to'] != '') {
            $condition .= " AND TravelDate BETWEEN '" . $_GET['from'] . "' AND '" . $_GET['to'] . "'";
        } elseif ($_GET['from'] != '') {
            $condition .= " AND TravelDate >= '" . $_GET['from'] . "'";
        } elseif ($_GET['to'] != '') {
            $condition .= " AND TravelDate <= '" . $_GET['to'] . "'";
        }
        $sql .=$condition;

        $stmt = $con->prepare($sql);

		//Paging Start Here
		$stmt->execute();
		$rowCount = (int)$stmt->rowCount();
		$totalpages = ceil($rowCount / PAGESIZE);

		if (isset($_REQUEST['page']) && $_REQUEST['page'] != '') {
			$page = $_REQUEST['page'];
		} else {
			$page = 1;
		}

		$start = ($page - 1) * PAGESIZE;
		$sql .= " LIMIT $start , " . PAGESIZE;

		$stmt = $con->prepare($sql);
		//Paging End Here

		if ($stmt->execute())
		{
			$rowCount = (int) $stmt->rowCount();
			$result = $stmt->fetchAll(PDO::FETCH_ASSOC);				
			if ($rowCount > 0)
			{
				foreach ($result as $row) 
				{																
					echo "<div class='pure-g pure-g1' style='font-size:13px;'>";
					echo "<div class='pure-u-4-24'><p>" . $row['CabId'] . " </p></div>";
					echo "<div class='pure-u-4-24'><p>" . $row['OwnerName'] . " </p></div>";	
					echo "<div class='pure-u-5-24'><p>" . $row['FromLocation'] . "</a></p></div>";
					echo "<div class='pure-u-4-24'><p>" . $row['ToLocation'] . " </p></div>";							
					echo "<div class='pure-u-3-24' style='text-align:center;'><p>" . $row['RemainingSeats'] . " </p></div>";	
					echo "<div class='pure-u-4-24'><p>" . $row['OpenTime'] . " </p></div>";	
					
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
			echo "<span style='color:Red;font-size:13px; font-weight:bold;'>Error: " . var_dump($con->errorInfo()) . "</span>";
		}
		echo pagination_search($totalpages, $page);
		echo "</div>";
	}
?>	