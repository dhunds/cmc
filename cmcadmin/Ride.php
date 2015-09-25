<?php
include ('connection.php');

?> 
<link rel="stylesheet" type="text/css" media="all" href="Calendar/calendar-blue.css"title="win2k-cold-1">
<script type="text/javascript" src="Calendar/calendar.js"></script>
<script type="text/javascript" src="Calendar/lang/calendar-en.js"></script>
<script type="text/javascript" src="Calendar/calendar-setup.js"></script>  
<h3 class="headingText">Rides</h3>
<div class="articleBorder">
<form action="mRide.php" method="POST">

<table width="50%" cellpadding="2" cellspacing="2">
	<tr>
		<td width="20%" class="bluetext">Name</td>
		<td width="20%"><input type="text" name="Name" id="Name" class="textfield"></td>
		
		<td width="20%" class="bluetext">Date</td>
		<td width="20%">
			<input type="text" name="Date" id="Date" class="textfield">
		</td>
		<td width="35">	
			<img src="images/calendar.png" id="dtFrom">&nbsp;
			<script type="text/javascript">
				Calendar.setup({
					inputField: 'Date',
					ifFormat: "%d/%m/%Y",
					button: "dtFrom",
					//align          :    "Tl",
					singleClick: true
				});
			</script>
		</td>
	</tr>
	<tr>
	<td colspan="5" height="5"></td>
	</tr>
	<tr>
		<td class="bluetext">Number</td>
		<td><input type="text" name="Number" id="Number" class="textfield"></td>
		
		<td class="bluetext">City</td>
		<td><input type="text" name="City" id="City" class="textfield"></td>
	</tr>
	<tr>
	<td colspan="5" height="10"></td>
	</tr>
	<tr>
		<td> <input type="submit" class="cBtn" name="nameview" id="nameview" value="View"></td>
	</tr>
	<tr>
	<td colspan="5" height="10"></td>
	</tr>
</table>
 
<?php

	if(isset($_POST['nameview']))
	{
	
		$Name = $_POST['Name'];	
		$Date = $_POST['Date'];	
		$Number = $_POST['Number'];	
		$City = $_POST['City'];	

		
		$sql="call getCabDetails('$Name','$Date','$Number','$City', @totalRows)";	
		$data = $con->query($sql)->fetchAll(PDO::FETCH_ASSOC);	
		$total_count = $con->query("select @totalRows;")->fetch(PDO::FETCH_ASSOC);

		if($total_count["@totalRows"] === NULL)
		{		
			$total_count = 0;		
		}					
		//echo "ME";
		if ($total_count > 0) 
		{			
			echo "<div class='pure-g' style='font-size:13px; font-weight:bold; margin-top:10px;'>";
			echo "<div class='pure-u-2-24'><p class='tHeading'>Mobile Number</p></div>";
			echo "<div class='pure-u-2-24'><p class='tHeading'>Owner Name</p></div>";			
			echo "<div class='pure-u-2-24'><p class='tHeading'>From Location</p></div>";
			echo "<div class='pure-u-2-24'><p class='tHeading'>To Location</p></div>";
			echo "<div class='pure-u-2-24'><p class='tHeading'>From ShortName</p></div>";			
			echo "<div class='pure-u-2-24'><p class='tHeading'>To ShortName</p></div>";			
			echo "<div class='pure-u-2-24'><p class='tHeading'>Travel Date</p></div>";
			echo "<div class='pure-u-2-24'><p class='tHeading'>Travel Time</p></div>";	
			echo "<div class='pure-u-2-24'><p class='tHeading'>Seats</p></div>";			
			echo "<div class='pure-u-2-24'><p class='tHeading'>Remaining Seats</p></div>";			
			echo "<div class='pure-u-2-24'><p class='tHeading'>Open Time</p></div>";
			echo "<div class='pure-u-2-24'><p class='tHeading'>Cab Status</p></div>";
			echo "</div>";
			
			foreach($data as $row)
			{
				echo "<div class='pure-g pure-g1' style='font-size:13px;'>";
				echo "<div class='pure-u-2-24'><p>" . $row['MobileNumber']. "</p></div>";	
				echo "<div class='pure-u-2-24'><p>" . $row['OwnerName'] . " </p></div>";
				echo "<div class='pure-u-2-24'><p>" . $row['FromLocation']. "</p></div>";
				echo "<div class='pure-u-2-24'><p>" . $row['ToLocation']. "</p></div>";
				echo "<div class='pure-u-2-24'><p>" . $row['FromShortName'] . " </p></div>";	
				echo "<div class='pure-u-2-24'><p>" . $row['ToShortName'] . " </p></div>";	
				echo "<div class='pure-u-2-24'><p>" . $row['TravelDate']. "</p></div>";
				echo "<div class='pure-u-2-24'><p>" . $row['TravelTime'] . " </p></div>";	
				echo "<div class='pure-u-2-24'><p>" . $row['Seats'] . " </p></div>";
				echo "<div class='pure-u-2-24'><p>" . $row['RemainingSeats'] . " </p></div>";	
				echo "<div class='pure-u-2-24'><p>" . $row['OpenTime'] . " </p></div>";			
				echo "<div class='pure-u-2-24'><p>" . $row['CabStatus'] . " </p></div>";
				echo "</div>";
			}	
		}
		else
		{
			echo "No Ride Found";
		}
	}     
?>

</form>
</div>