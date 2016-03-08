<?php
include ('connection.php');

?> 
<h2 class="headingText">Cab Bookings</h2>
<div>
<form action="mCabBookings.php" method="POST">
<table width="50%" style="margin-left: 5px;">
	<tr>
		<td width="50">Cab Type</td>
		<td><input type="text" name="CabType" id="CabType" class="textfield"></td>
		
		<td width="50" style="text-align: right">Name </td>
		<td><input type="text" name="CabName" id="CabName" class="textfield"></td>
	</tr>
	<tr>
		<td> &nbsp;</td>
	</tr>
	<tr>
		<td> <input type="submit" class="cBtn" name="nameview" id="nameview" value="View"></td>
	</tr>
</table>
 
<?php
	if(isset($_POST['nameview']))
	{	
		$tCab = $_POST['CabType'];	
		$nCab = $_POST['CabName'];	
		
		$tsql="call getcabBook('$tCab','$nCab', @totalRows)";		
		$result = $con->query($tsql)->fetchAll(PDO::FETCH_ASSOC);	
		$total_count = $con->query("select @totalRows;")->fetch(PDO::FETCH_ASSOC);

		if($total_count["@totalRows"] === NULL)
		{		
			$total_count = 0;		
		}					
		//echo "ME";
		if ($total_count > 0) 
		{		
			echo "<div class='pure-g' style='font-size:13px; font-weight:bold; margin-top:10px;'>";
			echo "<div class='pure-u-3-24'><p class='tHeading'>Cab Name</p></div>";
			echo "<div class='pure-u-3-24'><p class='tHeading'>Cab Type</p></div>";			
			echo "<div class='pure-u-3-24'><p class='tHeading'>Booking Time</p></div>";
			echo "<div class='pure-u-3-24'><p class='tHeading'>From Latitude</p></div>";
			echo "<div class='pure-u-3-24'><p class='tHeading'>To Latitude</p></div>";			
			echo "<div class='pure-u-3-24'><p class='tHeading'>Mobile Number</p></div>";			
			echo "<div class='pure-u-3-24'><p class='tHeading'>Cab UserName</p></div>";
			echo "<div class='pure-u-3-24'><p class='tHeading'>Booking RefNo</p></div>";			
			echo "</div>";
			foreach ($result as $row) 
			{				
				echo "<div class='pure-g pure-g1' style='font-size:13px;'>";
				echo "<div class='pure-u-3-24'><p style='margin-left: 5px;'>" . $row['CabName']. "</p></div>";
				echo "<div class='pure-u-3-24'><p style='margin-left: 10px;'> " . $row['CabType'] . " </p></div>";
				echo "<div class='pure-u-3-24'><p>" . $row['BookingTime']. "</p></div>";
				echo "<div class='pure-u-3-24'><p>" . $row['FromLat']. "</p></div>";
				echo "<div class='pure-u-3-24'><p>" . $row['ToLat'] . " </p></div>";	
				echo "<div class='pure-u-3-24'><p>" . $row['MobileNumber'] . " </p></div>";	
				echo "<div class='pure-u-3-24'><p>" . $row['CabUserName']. "</p></div>";
				echo "<div class='pure-u-3-24'><p style='text-align: center'>" . $row['BookingRefNo'] . " </p></div>";
				echo "</div>";
			}	
		
		}
		else
		{
			echo"No Cab Bookings Found !!";
		}
	}	
?>
</div>
</form>
</div>