<link rel="stylesheet" type="text/css" media="all" href="Calendar/calendar-blue.css"
        title="win2k-cold-1">
<script type="text/javascript" src="Calendar/calendar.js"></script>
<script type="text/javascript" src="Calendar/lang/calendar-en.js"></script>
<script type="text/javascript" src="Calendar/calendar-setup.js"></script>  
<h4 class="headingText">Top Origins/Top Destinations</h4>
<div class="articleBorder">
<form action="mTopOrigins.php" method="POST">
	<table width="550" cellspacing="2" cellpadding="2">
		<tr>
			<td>Date From</td>
			<td>
				<input type="text" name="from" id="from" class="textfield">
			</td>
			<td width="35">	
				<img src="images/calendar.png" id="dtFrom">&nbsp;
				<script type="text/javascript">
					Calendar.setup({
						inputField: 'from',
						ifFormat: "%d/%m/%Y",
						button: "dtFrom",
						//align          :    "Tl",
						singleClick: true
					});
				</script>
			</td>
			<td>Date To</td>
			<td>
				<input type="text" name="to" id="to" class="textfield">
			</td>
			<td width="35">	
				<img src="images/calendar.png" id="dtto">&nbsp;
				<script type="text/javascript">
					Calendar.setup({
						inputField: 'to',
						ifFormat: "%d/%m/%Y",
						button: "dtto",
						//align          :    "Tl",
						singleClick: true
					});
				</script>
			</td>
		</tr>
		<tr>
			<td colspan="6" height="10"></td>
		</tr>
		<tr>
			<td colspan="3" align="right">
				<input type="submit" class="cBtn" name="btnTopOrigin" value="TopOrigin">
			</td>
			<td colspan="3">
				&nbsp;<input type="submit" class="cBtn" name="btnTopDestination" value="TopDestination">
			</td>
		</tr>
	</table>
 
<?php
include ('connection.php');

if(isset($_POST['btnTopOrigin']))
{
	$name = $_POST['from'];
	try 
	{
		$sql = "SELECT DISTINCT (a.FromLocation) as FromLocation,(SELECT COUNT(*) FROM cabopen where FromLocation = a.FromLocation and  TravelDate BETWEEN '" . $_POST['from'] . "' AND '" . $_POST['to'] . "' ) AS NoOfCabs, TravelDate FROM cabopen a Where TravelDate BETWEEN '" . $_POST['from'] . "' AND '" . $_POST['to'] . "'";
		$stmt = $con->prepare($sql); 
		$stmt->execute();
	 
		$result = $stmt->fetchAll(PDO::FETCH_ASSOC);	
		echo "<div class='pure-g' style='font-size:13px; font-weight:bold; margin-top:20px;'>";
		echo "<div class='pure-u-2-5'><p class='tHeading'>Address</p></div>";
		echo "<div class='pure-u-2-5'><p class='tHeading'>No. of Cabs</p></div>";			
		echo "<div class='pure-u-1-5'><p class='tHeading'>Action</p></div>";			
		echo "</div>";
		foreach ($result as $row) 
		{
			$FromLoc = $row['FromLocation'];
			$FromData = explode(',', $FromLoc);
			$l1 = count($FromData);
					
			echo "<div class='pure-g pure-g1' style='font-size:13px;'>";
			echo "<div class='pure-u-2-5'><p>" . $FromData[$l1-2] . "</p></div>";
			echo "<div class='pure-u-2-5'><p>" . $row['NoOfCabs'] . "</p></div>";									
			echo "<div class='pure-u-1-5'><p><a href='mTopOriginView.php?loc=" . urlencode($FromData[$l1-2]) . "&Date=" . $row['TravelDate'] . "'>View</a></p></div>";					
			echo "</div>";  
		}
	}
	catch(PDOException $e) 
	{
		echo "Error: " . $e->getMessage();
	}
	$conn = null;
	echo "</table>";
}

if(isset($_POST['btnTopDestination']))
{
	$name = $_POST['from'];
	try 
	{
		$sql = "SELECT DISTINCT (a.ToLocation) as ToLocation,(SELECT COUNT(*) FROM cabopen where ToLocation = a.ToLocation and  TravelDate BETWEEN '" . $_POST['from'] . "' AND '" . $_POST['to'] . "' ) AS NoOfCabs FROM cabopen a Where TravelDate BETWEEN '" . $_POST['from'] . "' AND '" . $_POST['to'] . "'";
		$stmt = $con->prepare($sql); 
		$stmt->execute();
		 
		$result = $stmt->fetchAll(PDO::FETCH_ASSOC);	
		echo "<div class='pure-g' style='font-size:13px; font-weight:bold;'>";
		echo "<div class='pure-u-2-5'><p class='bluetext'>Address</p></div>";
		echo "<div class='pure-u-2-5'><p class='bluetext'>No. of Cabs</p></div>";			
		echo "<div class='pure-u-1-5'><p class='bluetext'>Action</p></div>";			
		echo "</div>";
		foreach ($result as $row) 
		{
			$ToLoc = $row['ToLocation'];
			$ToData = explode(',', $ToLoc);
			$l1 = count($ToData);
			
			echo "<div class='pure-g pure-g1' style='font-size:13px;'>";
			echo "<div class='pure-u-2-5'><p>" . $ToData[$l1-2] . "</p></div>";
			echo "<div class='pure-u-2-5'><p>" . $row['NoOfCabs'] . "</p></div>";									
			echo "<div class='pure-u-1-5'><p><a href='mTopDestinationView.php?local=" . urlencode($ToData[$l1-2]) . "'>View</a></p></div>";					
			echo "</div>";
		}
	}

	catch(PDOException $e) {
		echo "Error: " . $e->getMessage();
	}
	$conn = null;
	echo "</table>";
}
?>

</form>
</div>