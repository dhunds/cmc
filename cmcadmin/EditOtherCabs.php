<?php 
	$CabNameID= '';
	$CabName= '';
	$CabFullName = '';
	$Address = '';	
	$MobileSite= '';
	$CabPackageName ='';
	$Mode='';	
	$id = '';	
	if (isset($_GET['id']))
	{
		if (is_numeric($_GET['id']) && $_GET['id'] > 0)
		{	
			$CabNameID  = $_GET['id'];
			if (isset($_POST['submit']))
			{
			
				$CabNameID = $_POST["CabNameID"];
				$CabName = $_POST["CabName"];
				$CabFullName = $_POST["CabFullName"];
				$Address = $_POST["Address"];							
				$MobileSite = $_POST["MobileSite"];
				$CabPackageName = $_POST["CabPackageName"];
				$Mode = $_POST["Mode"];																										
				$sql = "UPDATE `cabnames` SET `CabName` = '$CabName',`CabFullName` = '$CabFullName' ,`CabAddress` = '$Address', `CabMobileSite` = '$MobileSite', `CabMode` = '$Mode', `CabPackageName` = '$CabPackageName' WHERE `CabNameID` = '$CabNameID'";
								
				if ($stmt = $con->prepare($sql))
				{
					$res = $stmt->execute();
					if ($res === true) 
					{
						header("Location: mOtherCabD.php");
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
				$stmt = $con->prepare("select * from `cabnames` Where `CabNameID`='$CabNameID'");														
				if ($stmt->execute())
				{
					$rowCount = (int) $stmt->rowCount();
					$result = $stmt->fetchAll(PDO::FETCH_ASSOC);				
					if ($rowCount > 0)
					{
						foreach ($result as $row) 
						{	
							$CabNameID = $row["CabNameID"];
							$CabName = $row["CabName"];
							$CabFullName = $row["CabFullName"];
							$Address = $row["CabAddress"];							
							$MobileSite = $row["CabMobileSite"];
							$CabPackageName = $row["CabPackageName"];
							$Mode = $row["CabMode"];							
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
			$CabName = $_POST["CabName"];
			$CabFullName = $_POST["CabFullName"];
			$Address = $_POST["Address"];			
			$MobileSite = $_POST["MobileSite"];
			$CabPackageName = $_POST["CabPackageName"];
			$Mode = $_POST["Mode"];			
			$sql = "INSERT INTO `cabnames` (`CabName`,`CabFullName`,`CabAddress`,`CabMobileSite`,`CabMode`,`CabPackageName`) values('$CabName','$CabFullName','$Address','$MobileSite','$Mode','$CabPackageName')";
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
<h2 class='headingText'>Cab Master</h2>
<div>
<form action="" method="post">
		<input type="hidden" name="id" value="<?php echo $id; ?>" />	
		<div class='pure-g' style='font-size:13px; font-weight:bold;'>
			<div class='pure-u-4-24'><p class='tHeading'>Cab Name</p></div>
			<div class='pure-u-4-24'><p class='tHeading'>Cab Full Name</p></div>
			<div class='pure-u-4-24'><p class='tHeading'>Address</p></div>			
			<div class='pure-u-4-24'><p class='tHeading'>Mobile Site</p></div>
			<div class='pure-u-4-24'><p class='tHeading'>Package Name</p></div>
			<div class='pure-u-4-24'><p class='tHeading'>Mode</p></div>		
		</div>
		<div class='pure-g' style='font-size:13px; font-weight:bold;'>		
			<div class='pure-u-4-24'><input type="hidden" name="CabNameID" size="12px" value="<?php echo $CabNameID; ?>" class />
			<input type="text" name="CabName" size="12px" value="<?php echo $CabName; ?>" class="textfield" /></div>
			<div class='pure-u-4-24'><input type="text" name="CabFullName" size="12px" value="<?php echo $CabFullName; ?>" class="textfield" /></div>
			<div class='pure-u-4-24'><input type="text" name="Address" size="12px" value="<?php echo $Address; ?>" class="textfield" /></div>
			<div class='pure-u-4-24'><input type="text" name="MobileSite" size="12px" value="<?php echo $MobileSite; ?>" class="textfield" /></div>
			<div class='pure-u-4-24'><input type="text" name="CabPackageName" size="12px" value="<?php echo $CabPackageName; ?>" class="textfield" /></div>			
			<div class='pure-u-4-24'>
			<?php
				echo "<select name='Mode' class='dropfield'>";
				$stmtL = $con->prepare("select * from `cabmode`");														
				if ($stmtL->execute())
				{
					$rowCountL = (int) $stmtL->rowCount();
					$resultL = $stmtL->fetchAll(PDO::FETCH_ASSOC);				
					if ($rowCountL > 0)
					{					
						foreach ($resultL as $row) 
						{
							if($Mode != '')
							{
								if($Mode ==  $row['ModeID'])
								{
									echo "<option value='" . $row['ModeID'] . "' selected>" . $row['ModeName'] . "</option>";
								}
								else
								{
									echo "<option value='" . $row['ModeID'] . "'>" . $row['ModeName'] . "</option>";
								}	
							}
							else
							{
								echo "<option value='" . $row['ModeID'] . "'>" . $row['ModeName'] . "</option>";
							}
						}
					}
				}
				echo "</select>";
			?>			
			
			</div>			
		</div>
		<div style="margin-top:20px;">
			<input type="submit" name="submit" value="Submit" class="cBtn" />	
		</div>
</form>
</div>
