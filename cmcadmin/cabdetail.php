<?php 
	$CabDetailID='';
	$CabNameID='';
	$CabContactNo='';
	$Outstation ='';		
	$CarType='';
	$BaseFare='';
	$BaseFareKM ='';
	$RatePerKMAfterBaseFare='';	
	$NightTimeStartHours='';	
	$NightTimeEndHours='';
	$NightTimeRateMultiplier='';
	$City='';
	$Active='';	
	$fortykmorfourhours='';
	$eightykmoreighthours='';
	$OvernightCharges='';
	$id ='';
if (isset($_GET['id']))
	{
		if (is_numeric($_GET['id']) && $_GET['id'] > 0)
		{	
			$CabDetailID= $_GET['id'];
			if (isset($_POST['submit']))
			{
				$CabDetailID = $_POST["CabDetailID"];
				$CabNameID = $_POST["CabNameID"];
				$CabContactNo = $_POST["CabContactNo"];
				$Outstation = $_POST["Outstation"];				
				$CarType = $_POST["CarType"];
				$BaseFare = $_POST["BaseFare"];
				$BaseFareKM = $_POST["BaseFareKM"];
				$RatePerKMAfterBaseFare = $_POST["RatePerKMAfterBaseFare"];
				$NightTimeStartHours = $_POST["NightTimeStartHours"];				
				$NightTimeEndHours = $_POST["NightTimeEndHours"];
				$NightTimeRateMultiplier = $_POST["NightTimeRateMultiplier"];
				$fortykmorfourhours = $_POST["fortykmorfourhours"];
				$eightykmoreighthours = $_POST["eightykmoreighthours"];
				$OvernightCharges = $_POST["OvernightCharges"];
				$City = $_POST["City"];
				if ($_POST['Active'] == '1')
				{
					$Active = 1;
				}
				else
				{
					$Active = 0;
				}
				
							

				$sql = "UPDATE `cabdetails` SET `CabNameID`='$CabNameID',`CabContactNo`='$CabContactNo',`Outstation`='$Outstation',`CarType`='$CarType',`BaseFare`='$BaseFare',`BaseFareKM`='$BaseFareKM',`RatePerKMAfterBaseFare`='$RatePerKMAfterBaseFare',`NightTimeStartHours`='$NightTimeStartHours',`NightTimeEndHours`='$NightTimeEndHours',`NightTimeRateMultiplier`='$NightTimeRateMultiplier',`40kmor4hours` = '$fortykmorfourhours',`80kmor8hours` = '$eightykmoreighthours',`OvernightCharges` = '$OvernightCharges',`City`='$City',`Active`='$Active' WHERE `CabDetailID` = '$CabDetailID'";
				if ($stmt = $con->prepare($sql))
				{
					$res = $stmt->execute();
					if ($res === true) 
					{
						header("Location: mOtherCab.php");
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
				$stmt = $con->prepare("select * from `cabdetails` WHERE `CabDetailID`='$CabDetailID'");														
				if ($stmt->execute())
				{
					$rowCount = (int) $stmt->rowCount();
					$result = $stmt->fetchAll(PDO::FETCH_ASSOC);				
					if ($rowCount > 0)
					{
						foreach ($result as $row) 
						{	
							$CabDetailID = $row["CabDetailID"];
							$CabNameID = $row["CabNameID"];
							$CabContactNo = $row["CabContactNo"];
							$Outstation = $row["Outstation"];							
							$CarType = $row["CarType"];
							$BaseFare = $row["BaseFare"];
							$BaseFareKM = $row["BaseFareKM"];
							$RatePerKMAfterBaseFare = $row["RatePerKMAfterBaseFare"];
							$NightTimeStartHours = $row["NightTimeStartHours"];				
							$NightTimeEndHours = $row["NightTimeEndHours"];
							$NightTimeRateMultiplier = $row["NightTimeRateMultiplier"];
							$fortykmorfourhours = $row["40kmor4hours"];
							$eightykmoreighthours = $row["80kmor8hours"];
							$OvernightCharges = $row["OvernightCharges"];
							$City = $row["City"];
							$Active = $row["Active"];							
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
			$CabDetailID = $_POST["CabDetailID"];
			$CabNameID = $_POST["CabNameID"];
			$CabContactNo = $_POST["CabContactNo"];
			$Outstation = $_POST["Outstation"];
			
		//	$FareType = $_POST["FareType"];
			$CarType = $_POST["CarType"];
			$BaseFare = $_POST["BaseFare"];
			$BaseFareKM = $_POST["BaseFareKM"];
			$RatePerKMAfterBaseFare = $_POST["RatePerKMAfterBaseFare"];
			$NightTimeStartHours = $_POST["NightTimeStartHours"];				
			$NightTimeEndHours = $_POST["NightTimeEndHours"];
			$NightTimeRateMultiplier = $_POST["NightTimeRateMultiplier"];
			$fortykmorfourhours = $_POST["fortykmorfourhours"];
			$eightykmoreighthours = $_POST["eightykmoreighthours"];
			$OvernightCharges = $_POST["OvernightCharges"];
			$City = $_POST["City"];
			if ($_POST['Active'] == '1')
			{
				$Active = 1;
			}
			else
			{
				$Active = 0;
			}
							
			$sql = "INSERT INTO `cabdetails` (`CabNameID`, `CabContactNo`, `Outstation`, `CarType`, `BaseFare`, `BaseFareKM`, `RatePerKMAfterBaseFare`, `NightTimeStartHours`, `NightTimeEndHours`, `NightTimeRateMultiplier`,`40kmor4hours`,`80kmor8hours`,`OvernightCharges`, `City`, `Active`)
			                           values('$CabNameID','$CabContactNo','$Outstation','$CarType','$BaseFare','$BaseFareKM','$RatePerKMAfterBaseFare', '$NightTimeStartHours', '$NightTimeEndHours', '$NightTimeRateMultiplier','$fortykmorfourhours','$eightykmoreighthours','$OvernightCharges', '$City', '$Active')";
			if ($stmt = $con->prepare($sql))
			{
				$res = $stmt->execute();
				if ($res === true) 
				{
					echo "<span style='color:Red;font-size:13px; font-weight:bold;'>submited... </span>";
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
<h4 class='headingText'>Cab Detail</h4>
<div class="articleBorder">
	<form action="" method="post">
		<input type="hidden" name="id" value="<?php echo $id; ?>" />
		<input type="hidden" name="CabDetailID" size="8px" value="<?php echo $CabDetailID; ?>" class='textfield' />			
		<table width="30%">
			<tr>
				<td>Cab Name</td>
				<td>
				<?php
					echo "<select name='CabNameID' class='dropfield'>";
					$stmtL = $con->prepare("select * from `cabnames`");														
					if ($stmtL->execute())
					{
						$rowCountL = (int) $stmtL->rowCount();
						$resultL = $stmtL->fetchAll(PDO::FETCH_ASSOC);				
						if ($rowCountL > 0)
						{					
							foreach ($resultL as $row) 
							{
								if($CabNameID != '')
								{
									if($CabNameID ==  $row['CabNameID'])
									{
										echo "<option value='" . $row['CabNameID'] . "' selected>" . $row['CabName'] . "</option>";
									}
									else
									{
										echo "<option value='" . $row['CabNameID'] . "'>" . $row['CabName'] . "</option>";
									}	
								}
								else
								{
									echo "<option value='" . $row['CabNameID'] . "'>" . $row['CabName'] . "</option>";
								}
							}
						}
					}
					echo "</select>";
				?>			
				</td>
			</tr>
			<tr>
				<td colspan="2" height="10"></td>
			</tr>
			<tr>
				<td>Contact No</td>
				<td><input type="text" name="CabContactNo" size="8px" value="<?php echo $CabContactNo; ?>" class='textfield' /></td>
			</tr>
			<tr>
				<td colspan="2" height="10"></td>
			</tr>
			<tr>
				<td>OutStation</td>
				<td>
					<?php
					echo "<select name='Outstation' id='Outstation' class='dropfield'>";
					if($Outstation == 'Y')
					{
						echo "<option value='Y' selected>Yes</option>";
						echo "<option value='N'>No</option>";
					}
					else
					{
						echo "<option value='Y'>Yes</option>";
						echo "<option value='N' selected>No</option>";
					}	
					echo "</select>";
				?>		</td>
			</tr>						
			<tr>
				<td colspan="2" height="10"></td>
			</tr>
			<tr>
				<td>Car Type</td>
				<td><input type="text" name="CarType" size="8px" value="<?php echo $CarType; ?>" class='textfield' /></td>
			</tr>
			<tr>
				<td colspan="2" height="10"></td>
			</tr>
			<tr>
				<td>Base Fare</td>
				<td><input type="text" name="BaseFare" size="8px" value="<?php echo $BaseFare; ?>" class='textfield' /></td>
			</tr>
			<tr>
				<td colspan="2" height="10"></td>
			</tr>
			<tr>
				<td>Base Fare(KM)</td>
				<td><input type="text" name="BaseFareKM" size="8px" value="<?php echo $BaseFareKM; ?>" class='textfield' /></td>
			</tr>
			<tr>
				<td colspan="2" height="10"></td>
			</tr>
			<tr>
				<td>Rate/KM</td>
				<td><input type="text" name="RatePerKMAfterBaseFare" size="8px" value="<?php echo $RatePerKMAfterBaseFare; ?>" class='textfield' /></td>
			</tr>
			<tr>
				<td colspan="2" height="10"></td>
			</tr>
			<tr>
				<td>Night Time Start Hours</td>
				<td><input type="text" name="NightTimeStartHours" size="8px" value="<?php echo $NightTimeStartHours; ?>" class='textfield' /></td>
			</tr>
			<tr>
				<td colspan="2" height="10"></td>
			</tr>
			<tr>
				<td>Night Time End Hours</td>
				<td><input type="text" name="NightTimeEndHours" size="8px" value="<?php echo $NightTimeEndHours; ?>" class='textfield' /></td>
			</tr>
			<tr>
				<td colspan="2" height="10"></td>
			</tr>
			<tr>
				<td>Night Time Rate Multiplier</td>
				<td><input type="text" name="NightTimeRateMultiplier" size="8px" value="<?php echo $NightTimeRateMultiplier; ?>" class='textfield' /></td>
			</tr>
			<tr>
				<td colspan="2" height="10"></td>
			</tr>
			<tr>
				<td>40 km or 4 hours</td>
				<td><input type="text" name="fortykmorfourhours" size="8px" value="<?php echo $fortykmorfourhours; ?>" class='textfield' /></td>
			</tr>
			<tr>
				<td colspan="2" height="10"></td>
			</tr>
			<tr>
				<td>80 km or 8 hours</td>
				<td><input type="text" name="eightykmoreighthours" size="8px" value="<?php echo $eightykmoreighthours; ?>" class='textfield' /></td>
			</tr>
			<tr>
				<td colspan="2" height="10"></td>
			</tr>
			<tr>
				<td>Overnight Charges</td>
				<td><input type="text" name="OvernightCharges" size="8px" value="<?php echo $OvernightCharges; ?>" class='textfield' /></td>
			</tr>
			<tr>
				<td colspan="2" height="10"></td>
			</tr>
			<tr>
				<td>City</td>
				<td><input type="text" name="City" size="8px" value="<?php echo $City; ?>" class='textfield' /></td>
			</tr>
			<tr>
				<td colspan="2" height="10"></td>
			</tr>
			<tr>
				<td>Active</td>
				<td>
				<?php
					if($Active != '')
					{
						if($Active == '1')
						{
							echo "<input type='checkbox' name='Active' id='Active' value='1' checked />";
						}
						else
						{
							echo "<input type='checkbox' name='Active' id='Active' value='1' />";
						}
					}
					else
					{
						echo "<input type='checkbox' name='Active' id='Active' value='1' />";
					}
				?>
				</td>
			</tr>
			<tr>
				<td colspan="2" height="10"></td>
			</tr>
			<tr>
				<td colspan="2" align="center">
					<input type="submit" name="submit" value="Submit" class="cBtn" />	
				</td>
			</tr>
		</table>			
	</form>
</div>

