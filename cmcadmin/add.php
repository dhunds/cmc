<?php
include ('connection.php');
?>
<h4 class="headingText">Add Configuration Setting</H4>
<div class="articleBorder">
	<form action="#" method="post">
		<table width="300">
			<tr>
				<td class="bluetext">Country</td>
				<td>
					<select name="drpCountry" id="drpCountry" class="dropfield">
						<option value="">--Select One--</option>
						<option value="IN">IN</option>
						<option value="CA">CA</option>
						<option value="AS"> AS</option>
						<option value="AI">AI</option>
						<option value="BB">BB</option>			   
					</select>
				</td>
			</tr>
			<tr>
				<td colspan="2" height="10"></td>
			</tr>
			<tr>
				<td class="bluetext">Item</td>
				<td>
					<select name="drpItem" id="drpItem" class="dropfield">
						<option value="">--Select One--</option>
						<option value="DOB">DOB</option>
						<option value="GENDER">Gender</option>
						<option value="EMAIL">Email</option>
					</select>
				</td>
			</tr>
			<tr>
				<td colspan="2" height="10"></td>
			</tr>
			<tr>
				<td class="bluetext">Visibility</td>
				<td>
					<select name="drpVisibility" id="drpVisibility" class="dropfield">
						<option value="">--Select One--</option>
						<option value="YES">Yes</option>
						<option value="NO">No</option>				   
					</select>
				</td>
			</tr>
			<tr>
				<td colspan="2" height="10"></td>
			</tr>
			<tr>
				<td colspan="2" align="center">
					  <input class="cBtn" type="submit" name="submit" value="SUBMIT" />
				</td>
			</tr>
		</table>

<?php
if(isset($_POST['submit']))
{
	$selected_country = $_POST['drpCountry'];  
	$selected_item = $_POST['drpItem'];
	$selected_vis = $_POST['drpVisibility'];

	$stmt = $con->query("SELECT * FROM `country` WHERE `countryCode` = '$selected_country ' AND `fieldName` = '$selected_item'");
	$no_of_rows = $stmt->rowCount();

	if ($no_of_rows > 0) 
	{

		$sql = "UPDATE `country` SET `visibilityOption`= '$selected_vis' Where `countryCode` = '$selected_country' AND `fieldName` = '$selected_item'";
		$stmt = $con->prepare($sql);
		$stmt->execute();
		if ($stmt == true) 
		{
			echo 'Record Updated Succesfully';
		}
		else 
		{
			echo 'Error';
		}
	}
	else
	{
		$sql2 = "INSERT INTO `country`(`countryCode`, `fieldName`, `visibilityOption`) VALUES ('$selected_country','$selected_item' ,'$selected_vis')";
		$stmt2 = $con->prepare($sql2);
		$stmt2 = $stmt2->execute();
		
		if ($stmt2 == true) 
		{
			echo 'Records  Submitted Succesfully';
		}
		else 
		{
			echo 'Error';
		}
	}
}
?>        
	</form>
</div>