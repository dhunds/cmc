<?php 
	$City= '';
	$CityGroup= '';
	$CityID = '';
		
	$id = '';

	if (isset($_GET['id']))
	{
		if (is_numeric($_GET['id']) && $_GET['id'] > 0)
		{	
			$CityID  = $_GET['id'];
			if (isset($_POST['submit']))
			{
			
				$CityID = $_POST["CityID"];
				$City = $_POST["City"];
				$CityGroup = $_POST["CityGroup"];
				
				$sql = "UPDATE `groupcities` SET `City` = '$City',`CityGroup` = '$CityGroup' WHERE `CityID` = '$CityID'";
								
				if ($stmt = $con->prepare($sql))
				{
					$res = $stmt->execute();
					if ($res === true) 
					{
						header("Location: mGroupCities.php");
					}
					else 
					{
						echo "<span style='color:Red;font-size:13px; font-weight:bold;'>Error: </span>";
					}
				}
				else
				{					
					echo "<span style='color:Red;font-size:13px; font-weight:bold;'>Error:  could not prepare SQL statement.</span>";
				}
			}
			else
			{
				$stmt = $con->prepare("select * from `groupcities` Where `CityID`='$CityID'");														
				if ($stmt->execute())
				{
					$rowCount = (int) $stmt->rowCount();
					$result = $stmt->fetchAll(PDO::FETCH_ASSOC);				
					if ($rowCount > 0)
					{
						foreach ($result as $row) 
						{	
							$CityID = $row["CityID"];
							$City = $row["City"];
							$CityGroup = $row["CityGroup"];
						
						}
					}
				}
			}
		}		
	}
	else	
	{
		if (isset($_POST['submit']))
		{			
			$City = $_POST["City"];
			$CityGroup = $_POST["CityGroup"];
			
			$sql = "INSERT INTO `groupcities` (`City`,`CityGroup`) values('$City','$CityGroup')";
			if ($stmt = $con->prepare($sql))
			{
				$res = $stmt->execute();
				if ($res === true) 
				{
					echo "<span style='color:Red;font-size:13px; font-weight:bold;'>added... </span>";
				}
				else 
				{
					echo "<span style='color:Red;font-size:13px; font-weight:bold;'>Error: </span>";
				}
			}
		
			else
			{					
				echo "<span style='color:Red;font-size:13px; font-weight:bold;'>Error:  could not prepare SQL statement.</span>";
			}	
	
	
		}
	}
?>
<h2 class='headingText'>Group Cities</h2>
<div>
<form action="" method="post">
		<input type="hidden" name="id" value="<?php echo $id; ?>" />	
		<div class='pure-g' style='font-size:13px; font-weight:bold;'>
			<div class='pure-u-6-24'><p class='tHeading'>City</p></div>
			<div class='pure-u-6-24'><p class='tHeading'>Super City</p></div>
			</div>
		<div class='pure-g' style='font-size:13px; font-weight:bold;'>		
			<div class='pure-u-6-24'><input type="hidden" name="CityID" size="12px" value="<?php echo $CityID; ?>" class />
			<input type="text" name="City" size="12px" value="<?php echo $City; ?>" class="textfield" /></div>
			<div class='pure-u-6-24'><input type="text" name="CityGroup" size="12px" value="<?php echo $CityGroup; ?>" class="textfield" /></div>

		
			</div>			
		</div>
		<div style="margin-top:20px;">
			<input type="submit" name="submit" value="Submit" class="cBtn" />	
		</div>
</form>
</div>
