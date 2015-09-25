<?php
include ('connection.php');
$dir='http://122.160.103.25/php/ClubMyCab/ProfileImages/';
?> 
<?php 
	$FullName= '';
	$MobileNumber= '';
	$Email = '';
	$Gender = '';
	$DOB= '';
	$PushNotification='';
	
	$id = '';	

	if (isset($_GET['id']))
	{
		if (is_numeric($_GET['id']) && $_GET['id'] > 0)
		{	
			$MobileNumber  = $_GET['id'];
			if (isset($_POST['submit']))
			{
					
							$FullName = $_POST["FullName"];
							$MobileNumber = $_POST["MobileNumber"];
							$Email = $_POST["Email"];
							$Gender = $_POST["Gender"];
							$DOB = $_POST["DOB"];
							$PushNotification = $_POST["PushNotification"];
						
						
							
							
				$sql = "UPDATE `registeredusers` SET `Email` = '$Email',`Gender` = '$Gender' ,`DOB` = '$DOB' WHERE `MobileNumber` = '$MobileNumber'";
				
				
				if ($stmt = $con->prepare($sql))
				{
					$res = $stmt->execute();
					if ($res === true) 
					{
						header("Location: mProfile.php");
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
			
//$stmt = $con->prepare("SELECT a.`FullName`,a.`MobileNumber`, a.`LastLoginDateTime`,(SELECT `imagename` FROM userprofileimage where `MobileNumber` = a.`MobileNumber`) As imagename, (SELECT COUNT(*) FROM `cabopen` where `MobileNumber` = a.`MobileNumber`) As NoofPoolsCreated, (SELECT COUNT(*) FROM `acceptedrequest` where `MemberNumber` =  a.`MobileNumber`) As NoofPoolsTaken FROM `registeredusers` a where a.`MobileNumber` = '$MobileNumber'");													
			$stmt= $con->prepare("SELECT * From `registeredusers` where `MobileNumber` = '$MobileNumber'");

				if ($stmt->execute())
				{
					$rowCount = (int) $stmt->rowCount();
					$result = $stmt->fetchAll(PDO::FETCH_ASSOC);				
					if ($rowCount > 0)
					{
						foreach ($result as $row) 
						{	
							//echo $row['FullName'];
							$FullName = $row['FullName'];
							$MobileNumber = $row['MobileNumber'];
							$Email = $row['Email'];
							$Gender = $row['Gender'];
							$DOB = $row['DOB'];
							$PushNotification = $row['PushNotification'];
									
						}
					}
				}
			}
		}		
	}

?>
<h4 class='headingText'>Members Profile</h4>
<div class="articleBorder">
<form action="" method="post">
		<input type="hidden" name="id" value="<?php echo $id; ?>" />	
					<table width="30%">
			
			<tr>
				<td>Name</td>
				<td><input type="text" name="FullName" size="8px" value="<?php echo $FullName; ?>" class='textfield' /></td>
			</tr>
			<tr>
				<td colspan="2" height="10"></td>
			</tr>
			<tr>
				<td>Number</td>
				<td><input type="text" name="MobileNumber" size="8px" value="<?php echo $MobileNumber; ?>" class='textfield' /></td>
			</tr>
			<tr>
				<td colspan="2" height="10"></td>
			</tr>
			<tr>
				<td>Email</td>
				<td>
					<input type="text" name="Email" size="8px" value="<?php echo $Email; ?>" class='textfield' />
				</td>
			</tr>
			<tr>
				<td colspan="2" height="10"></td>
			</tr>
			<tr>
				<td>Gender</td>
				<td><input type="text" name="Gender" size="8px" value="<?php echo $Gender; ?>" class='textfield' /></td>
			</tr>
			<tr>
				<td colspan="2" height="10"></td>
			</tr>
			<tr>
				<td>DOB</td>
				<td><input type="text" name="DOB" size="8px" value="<?php echo $DOB; ?>" class='textfield' /></td>
			</tr>
			<tr>
				<td colspan="2" height="10"></td>
			</tr>
			<tr>
				<td>PushNotification</td>
				<td><input type="text" name="PushNotification" size="8px" value="<?php echo $PushNotification; ?>" class='textfield' /></td>
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
	