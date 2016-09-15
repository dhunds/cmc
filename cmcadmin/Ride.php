<?php
include ('connection.php');
include('functions.php');

?> 
<link rel="stylesheet" type="text/css" media="all" href="Calendar/calendar-blue.css"title="win2k-cold-1">
<script type="text/javascript" src="Calendar/calendar.js"></script>
<script type="text/javascript" src="Calendar/lang/calendar-en.js"></script>
<script type="text/javascript" src="Calendar/calendar-setup.js"></script>  
<h2 class="headingText">Rides</h2>
<div>
<form action="mRide.php" method="GET">

<table width="50%" cellpadding="2" cellspacing="2" style="margin-left: 5px;">
	<tr>
		<td width="10%">Name</td>
		<td width="20%" style="text-align: left"><input type="text" name="Name" id="Name" class="textfield" value="<?=$_REQUEST['Name']?>"></td>
		
		<td width="10%" style="text-align: right;">Date </td>
		<td width="20%">
			<input type="text" name="Date" id="Date" class="textfield" value="<?=$_REQUEST['Date']?>">
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
		<td>Number</td>
		<td><input type="text" name="Number" id="Number" class="textfield" value="<?=$_REQUEST['Number']?>"></td>
		
		<td style="text-align: right">City </td>
		<td><input type="text" name="City" id="City" class="textfield" value="<?=$_REQUEST['City']?>"></td>
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

	$sql = "SELECT * FROM cabopen WHERE 1";

	if (isset($_REQUEST['Name']) && $_REQUEST['Name'] !='') {
		$sql .= " AND OwnerName='".$_REQUEST['Name']."'";
	}

	if (isset($_REQUEST['Number']) && $_REQUEST['Number'] !='') {
		$sql .= " AND MobileNumber='".$_REQUEST['Number']."'";
	}

	if (isset($_REQUEST['City']) && $_REQUEST['City'] !='') {
		$sql .= " AND FromLocation='".$_REQUEST['City']."'";
	}

	if (isset($_REQUEST['Date']) && $_REQUEST['Date'] !='') {
		$sql .= " AND TravelDate='".$_REQUEST['Date']."'";
	}

	$stmt = $con->prepare($sql);
	$stmt->execute();
	$rowCount = (int) $stmt->rowCount();

	$totalpages   = ceil($rowCount/PAGESIZE);

	if(isset($_REQUEST['page']) && $_REQUEST['page']!=''){
		$page = $_REQUEST['page'];
	}else{
		$page = 1;
	}

	$start= ($page-1) * PAGESIZE;
	$sql .=" ORDER BY OpenTime DESC LIMIT $start , ".PAGESIZE;

	$stmt = $con->prepare($sql);
	$stmt->execute();

	echo "<div class='pure-g' style='font-size:13px; font-weight:bold; margin-top:10px;'>";
	echo "<div class='pure-u-3-24'><p class='tHeading'>Mobile Number</p></div>";
	echo "<div class='pure-u-2-24'><p class='tHeading'>Owner Name</p></div>";
	echo "<div class='pure-u-3-24'><p class='tHeading'>From ShortName</p></div>";
	echo "<div class='pure-u-3-24'><p class='tHeading'>To ShortName</p></div>";
	echo "<div class='pure-u-3-24'><p class='tHeading'>Travel Date</p></div>";
	echo "<div class='pure-u-2-24'><p class='tHeading'>Travel Time</p></div>";
	echo "<div class='pure-u-2-24'><p class='tHeading'>Seats</p></div>";
	echo "<div class='pure-u-2-24'><p class='tHeading'>Remain Seats</p></div>";
	echo "<div class='pure-u-2-24'><p class='tHeading'>Open Time</p></div>";
	echo "<div class='pure-u-2-24'><p class='tHeading'>Cab Status</p></div>";
	echo "</div>";

	if ($rowCount > 0)
	{
		$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

		foreach($data as $row)
		{
			echo "<div class='pure-g pure-g1' style='font-size:13px;'>";
			echo "<div class='pure-u-3-24'><p style='margin-left: 5px;'>" . $row['MobileNumber']. "</p></div>";
			echo "<div class='pure-u-2-24'><p>" . $row['OwnerName'] . " </p></div>";
			echo "<div class='pure-u-3-24'><p>" . $row['FromShortName'] . " </p></div>";
			echo "<div class='pure-u-3-24'><p>" . $row['ToShortName'] . " </p></div>";
			echo "<div class='pure-u-3-24'><p>" . $row['TravelDate']. "</p></div>";
			echo "<div class='pure-u-2-24'><p>" . $row['TravelTime'] . " </p></div>";
			echo "<div class='pure-u-2-24'><p>" . $row['Seats'] . " </p></div>";
			echo "<div class='pure-u-2-24'><p>" . $row['RemainingSeats'] . " </p></div>";
			echo "<div class='pure-u-2-24'><p>" . date("jS M Y g:i A", strtotime($row['OpenTime'])) . " </p></div>";
			echo "<div class='pure-u-2-24'><p style='text-align: center'>" . $row['CabStatus'] . " </p></div>";
			echo "</div>";
		}
	}
	else
	{
		echo "<div class='pure-g pure-g1' style='font-size:13px;'>";
		echo "<div class='pure-u-24-24'><p style='text-align:center;'>No Ride Found</p></div>";
		echo "</div>";
	}


	echo pagination_search($totalpages, $page);
	echo "</div>";
?>

</form>
</div>