<?php 
	$setName= '';
	$setValue= '';
	$setDescription = '';
		
	$id = '';

	if (isset($_GET['id']))
	{
		if (is_numeric($_GET['id']) && $_GET['id'] > 0)
		{	
			$setID  = $_GET['id'];
			if (isset($_POST['submit']))
			{
			
				$setID = $_POST["setID"];
				$setName = $_POST["setName"];
				$setValue = $_POST["setValue"];
				$setDescription = $_POST["setDescription"];
				
				$sql = "UPDATE `settings` SET `setValue` = '$setValue',`setDescription` = '$setDescription' WHERE `setID` = '$setID'";
								
				if ($stmt = $con->prepare($sql))
				{
					$res = $stmt->execute();
					if ($res === true) 
					{
						header("Location: mSetting.php");
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
				$stmt = $con->prepare("select * from `settings` Where `setID`='$setID'");														
				if ($stmt->execute())
				{
					$rowCount = (int) $stmt->rowCount();
					$result = $stmt->fetchAll(PDO::FETCH_ASSOC);				
					if ($rowCount > 0)
					{
						foreach ($result as $row) 
						{	
							$setID = $row["setID"];
							$setName = $row["setName"];
							$setValue = $row["setValue"];
							$setDescription = $row["setDescription"];
						
						}
					}
				}
			}
		}		
	}
	
	
?>
<h2 class='headingText'>Settings</h2>
<div>
<form action="" method="post">
		<input type="hidden" name="id" value="<?php echo $id; ?>" />	
		<div class='pure-g' style='font-size:13px; font-weight:bold;margin-left: 5px;'>
			<div class='pure-u-6-24'><p class='tHeading'>Name</p></div>
			<div class='pure-u-6-24'><p class='tHeading'>Value</p></div>
			<div class='pure-u-12-24'><p class='tHeading'>Description</p></div>
			</div>
		<div class='pure-g' style='font-size:13px; margin-left: 10px;'>
			<div class='pure-u-6-24'><input type="hidden" name="setID" size="12px" value="<?php echo $setID; ?>" class />
			<?php echo $setName; ?></div>
			<div class='pure-u-6-24'><input type="text" name="setValue" size="12px" value="<?php echo $setValue; ?>" class="textfield" /></div>
			<div class='pure-u-12-24'><input type="text" name="setDescription" size="12px" value="<?php echo $setDescription; ?>" class="textfield" /></div>

		
			</div>			
		</div>
		<div style="margin-top:20px; margin-left: 10px;">
			<input type="submit" name="submit" value="Submit" class="cBtn" />	
		</div>
</form>
</div>
