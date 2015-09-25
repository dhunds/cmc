<html>
 <head>
<style>
h4 {
   text-decoration: underline;
  color: Blue;
}
</style>

   </head>


		<?php
		
	$Mode = urldecode($_GET['Mode']);
	//$CabStatus = $_GET['CabStatus'];
	//$DropStatus = $_GET['DropStatus'];
	
	 if($Mode=='LLU')
			{
										
			echo "<br/>";
			echo "<div class='pure-g' style='font-size:14px; font-weight:bold;'>";									
			echo "</div>";
			echo "<br/>";
			echo "<div class='pure-g' style='font-size:13px;font-weight:bold;'>";
			echo "<div class='pure-u-4-24'>CabId</div>";
			echo "<div class='pure-u-4-24'>Owner</div>";
			echo "<div class='pure-u-4-24'>From</div>";
			echo "<div class='pure-u-4-24'>To</div>";
			echo "<div class='pure-u-4-24'>OpenTime</div>";
			echo "<div class='pure-u-4-24'>Members</div>";
			echo "</div>";
					
				$stmt = $con->prepare("SELECT a.*, GROUP_CONCAT(b.`MemberName`)As MembersName FROM `cabopen` a, `cabmembers` b WHERE a.`CabId` = b.`CabId`");
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

			}
	else if($Mode =='UP')
			{
						
					
			echo "<br/>";
			echo "<div class='pure-g' style='font-size:14px; font-weight:bold;'>";									
			echo "</div>";
			echo "<br/>";
			echo "<div class='pure-g' style='font-size:13px;font-weight:bold;'>";
			echo "<div class='pure-u-4-24'>CabId</div>";
			echo "<div class='pure-u-4-24'>Owner</div>";
			echo "<div class='pure-u-4-24'>From</div>";
			echo "<div class='pure-u-4-24'>To</div>";
			echo "<div class='pure-u-4-24'>OpenTime</div>";
			echo "<div class='pure-u-4-24'>Members</div>";
			echo "</div>";
					
				$stmt = $con->prepare("SELECT a.*, GROUP_CONCAT(b.`MemberName`)As MembersName FROM `cabopen` a, `cabmembers` b WHERE a.`CabId` = b.`CabId`");
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
	
			}
	else
		
	{	
			echo "<br/>";
			echo "<div class='pure-g' style='font-size:14px; font-weight:bold;'>";									
			echo "</div>";
			echo "<br/>";
			echo "<div class='pure-g' style='font-size:13px;font-weight:bold;'>";
			echo "<div class='pure-u-4-24'>CabId</div>";
			echo "<div class='pure-u-4-24'>Owner</div>";
			echo "<div class='pure-u-4-24'>From</div>";
			echo "<div class='pure-u-4-24'>To</div>";
			echo "<div class='pure-u-4-24'>Remaining Seats</div>";
			echo "<div class='pure-u-4-24'>OpenTime</div>";
		
				$stmt = $con->prepare("Select * From `Cabopen` Where `CabStatus`='$Mode'");
			
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
					echo "<div class='pure-u-4-24'><p>" . $row['RemainingSeats'] . " </p></div>";	
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
			echo "<span style='color:Red;font-size:13px; font-weight:bold;'>Error: " . $con->error . "</span>";
		}	
	}
?>	

<html>