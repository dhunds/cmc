<link rel="stylesheet" type="text/css" media="all" href="Calendar/calendar-blue.css"
        title="win2k-cold-1">
<script type="text/javascript" src="Calendar/calendar.js"></script>
<script type="text/javascript" src="Calendar/lang/calendar-en.js"></script>
<script type="text/javascript" src="Calendar/calendar-setup.js"></script>
 
<h4 class='headingText'>Active/Cancel Pools</H4>
<div class="articleBorder">
<form action="mActive.php" method="POST">

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
			<td colspan="6" align="center">
				<input type="submit" class="cBtn" name="btnview" id="btnview" value="Search">
			</td>			
		</tr>
	</table>

<?php 
	$allData = '';
	
	if(isset($_POST['btnview']))
	{	
		$stmt1 = $con->prepare("select * from `cabopen` where `CabStatus` = 'A' AND TravelDate BETWEEN '" . $_POST['from'] . "' AND '" . $_POST['to'] . "'");
		$stmt1->execute();
		$no_of_Active = $stmt1->rowCount();
		//echo $no_of_likes;		
		$stmt2 = $con->prepare("select * from `cabopen` where `CabStatus` = 'I' AND TravelDate BETWEEN '" . $_POST['from'] . "' AND '" . $_POST['to'] . "'");
		$stmt2->execute();
		$no_of_InActive = $stmt2->rowCount();
		//echo $no_of_dislikes;
		$allData = "[['Active'," . $no_of_Active . "],['InActive'," . $no_of_InActive . "]]";				
		//echo $allData;
	}
	else
	{
		$stmt1 = $con->prepare("select * from `cabopen` where `CabStatus` = 'A' AND `TravelDate` = DATE_FORMAT(NOW(),'%e/%m/%Y')");
		$stmt1->execute();
		$no_of_Active = $stmt1->rowCount();
		//echo $no_of_likes;		
		$stmt2 = $con->prepare("select * from `cabopen` where `CabStatus` = 'I' AND `TravelDate` = DATE_FORMAT(NOW(),'%e/%m/%Y')");
		$stmt2->execute();
		$no_of_InActive = $stmt2->rowCount();
		//echo $no_of_dislikes;
		$allData = "[['Active'," . $no_of_Active . "],['InActive'," . $no_of_InActive . "]]";				
		//echo $allData;
	}
?>
<input type='hidden' id='hdnData' value="<?php echo $allData; ?>"/>
<div id="chartContainer" style="height: 300px; width: 30%; display:none;margin-top:20px;"></div>  
<br/>		
<?php
	if(isset($_POST['btnview']))
	{
		echo "<br/><br/>";
		echo "<div class='pure-g' style='font-size:14px; font-weight:bold;'>";									
		echo "<div class='pure-u-1'><h4 class='headingText'>Number of Active/Cancel Pools</h4></div>";
		echo "</div>";
		echo "<div class='articleBorder'>";
		echo "<div class='pure-g' style='font-size:13px;font-weight:bold;'>";
		echo "<div class='pure-u-2-5'><p class='tHeading'>Date</p></div>";
		echo "<div class='pure-u-2-5'><p class='tHeading'>Active</p></div>";
		echo "<div class='pure-u-1-5'><p class='tHeading'>InActive</p></div>";
		echo "</div>";
		$stmt = $con->prepare("select distinct a.`TravelDate`, (SELECT COUNT(*) FROM `cabopen` where `CabStatus` = 'A' and `TravelDate` = a.`TravelDate`) as Active, (SELECT COUNT(*) FROM `cabopen` where `CabStatus` = 'I' and `TravelDate` = a.`TravelDate`) as InActive from `cabopen` a where a.TravelDate BETWEEN '" . $_POST['from'] . "' AND '" . $_POST['to'] . "'");														
		if ($stmt->execute())
		{
			$rowCount = (int) $stmt->rowCount();
			$result = $stmt->fetchAll(PDO::FETCH_ASSOC);				
			if ($rowCount > 0)
			{
				foreach ($result as $row) 
				{																
					echo "<div class='pure-g pure-g1' style='font-size:13px;'>";
					echo "<div class='pure-u-2-5'><p><a href='mAllpool.php?Date=" . $row['TravelDate'] . "'>" . $row['TravelDate'] . "</a></p></div>";
					echo "<div class='pure-u-2-5'><p><a href='mAllpool.php?Date=" . $row['TravelDate'] . "&Active=1'>" . $row['Active'] . " </p></div>";									
					echo "<div class='pure-u-1-5'><p><a href='mAllpool.php?Date=" . $row['TravelDate'] . "&Active=0'>" . $row['InActive'] . " </p></div>";		
					echo "</div>";
				}											
			}				
			else
			{
				echo "<span style='color:Green;font-size:13px; font-weight:bold;'>No results to display!</span>";
			}
		}	
		else
		{							
			echo "<span style='color:Red;font-size:13px; font-weight:bold;'>Error: " . $con->error . "</span>";
		}	
		echo "</div>";
	}
	else
	{	
		echo "<br/><br/>";
		echo "<div class='pure-g' style='font-size:14px; font-weight:bold;'>";									
		echo "<div class='pure-u-1'><h4 class='headingText'>Number of Active/Cancel Pools</h4></div>";
		echo "</div>";
		echo "<div class='articleBorder'>";
		echo "<div class='pure-g' style='font-size:13px;font-weight:bold;'>";
		echo "<div class='pure-u-2-5'><p class='tHeading'>Date</p></div>";
		echo "<div class='pure-u-2-5'><p class='tHeading'>Active</p></div>";
		echo "<div class='pure-u-1-5'><p class='tHeading'>InActive</p></div>";
		echo "</div>";
		$stmt = $con->prepare("select distinct a.`TravelDate`, (SELECT COUNT(*) FROM `cabopen` where `CabStatus` = 'A' and `TravelDate` = a.`TravelDate`) as Active, (SELECT COUNT(*) FROM `cabopen` where `CabStatus` = 'I' and `TravelDate` = a.`TravelDate`) as InActive from `cabopen` a where a.`TravelDate` = DATE_FORMAT(NOW(),'%e/%m/%Y')");														
		if ($stmt->execute())
		{
			$rowCount = (int) $stmt->rowCount();
			$result = $stmt->fetchAll(PDO::FETCH_ASSOC);				
			if ($rowCount > 0)
			{
				foreach ($result as $row) 
				{																
					echo "<div class='pure-g pure-g1' style='font-size:13px;'>";
					echo "<div class='pure-u-2-5'><p><a href='mAllpool.php?Date=" . $row['TravelDate'] . "'>" . $row['TravelDate'] . "</a></p></div>";
					echo "<div class='pure-u-2-5'><p><a href='mAllpool.php?CabStatus=A&Date=" . $row['TravelDate'] . "'>" . $row['Active'] . "</a></p></div>";
					echo "<div class='pure-u-1-5'><p><a href='mAllpool.php?CabStatus=I&Date=" . $row['TravelDate'] . "'>" . $row['InActive'] . "</a></p></div>";
					echo "</div>";
				}											
			}				
			else
			{
				echo "<span style='color:Green;font-size:13px; font-weight:bold;'>No results to display!</span>";
			}
		}	
		else
		{							
			echo "<span style='color:Red;font-size:13px; font-weight:bold;'>Error: " . $con->error . "</span>";
		}	
		echo "</div>";
	}
?>	

</br></br>
<?php

	if(isset($_POST['btnview']))
	{		
		echo "<br/><br/>";
		echo "<div class='pure-g' style='font-size:14px; font-weight:bold;'>";									
		echo "<div class='pure-u-1'><h4 class='headingText'>Drop Pool %</h4></div>";
		echo "</div>";
		echo "<div class='articleBorder'>";
		echo "<div class='pure-g' style='font-size:13px;font-weight:bold;'>";
		echo "<div class='pure-u-2-5'><p class='tHeading'>Date</p></div>";
		echo "<div class='pure-u-1-5'><p class='tHeading'>Open Pools</p></div>";
		echo "<div class='pure-u-1-5'><p class='tHeading'>Pool Cancel(%)</p></div>";
		echo "<div class='pure-u-1-5'><p class='tHeading'>No. Of Person Drop</p></div>";
		echo "</div>";
		$stmt = $con->prepare("select distinct a.`TravelDate`, ((SELECT COUNT(*) FROM `cabopen` where `CabStatus` = 'A' and `TravelDate` = a.`TravelDate`)/(SELECT COUNT(*) FROM `cabopen` where `TravelDate` = a.`TravelDate`)) * 100 as Active, ((SELECT COUNT(*) FROM `cabopen` where `CabStatus` = 'I' and `TravelDate` = a.`TravelDate`)/(SELECT COUNT(*) FROM `cabopen` where `TravelDate` = a.`TravelDate`)) * 100 as InActive, (SELECT COUNT(*) FROM `cabmembers` where `CabId` IN(SELECT `CabId` FROM `cabopen` where TravelDate BETWEEN '" . $_POST['from'] . "' AND '" . $_POST['to'] . "') and `DropStatus` = 'Yes') As NoofPersons from `cabopen` a where a.TravelDate BETWEEN '" . $_POST['from'] . "' AND '" . $_POST['to'] . "'");														
		if ($stmt->execute())
		{
			$rowCount = (int) $stmt->rowCount();
			$result = $stmt->fetchAll(PDO::FETCH_ASSOC);				
			if ($rowCount > 0)
			{
				foreach ($result as $row) 
				{																
					echo "<div class='pure-g pure-g1' style='font-size:13px;'>";
					echo "<div class='pure-u-2-5'><p>" . $row['TravelDate'] . "</p></div>";
					echo "<div class='pure-u-1-5'><p>" . round($row['Active']) . " </p></div>";									
					echo "<div class='pure-u-1-5'><p>" . round($row['InActive']) . " </p></div>";		
					echo "<div class='pure-u-1-5'><p>" . $row['NoofPersons'] . " </p></div>";		
					echo "</div>";
				}											
			}				
			else
			{
				echo "<span style='color:Green;font-size:13px; font-weight:bold;'>No results to display!</span>";
			}
		}	
		else
		{							
			echo "<span style='color:Red;font-size:13px; font-weight:bold;'>Error: " . $con->error . "</span>";
		}
		echo "</div>";		
	}
	else
	{		
		echo "<div class='pure-g' style='font-size:14px; font-weight:bold;'>";									
		echo "<div class='pure-u-1'><h4 class='headingText'>Drop Pool %</h4></div>";
		echo "</div>";
		echo "<div class='articleBorder'>";
		echo "<div class='pure-g' style='font-size:13px;font-weight:bold;'>";
		echo "<div class='pure-u-2-5'><p class='tHeading'>Date</p></div>";
		echo "<div class='pure-u-1-5'><p class='tHeading'>Open Pools</p></div>";
		echo "<div class='pure-u-1-5'><p class='tHeading'>Pool Cancel(%)</p></div>";
		echo "<div class='pure-u-1-5'><p class='tHeading'>No. Of Person Drop</p></div>";
		echo "</div>";
		$stmt = $con->prepare("select distinct a.`TravelDate`, ((SELECT COUNT(*) FROM `cabopen` where `CabStatus` = 'A' and `TravelDate` = a.`TravelDate`)/(SELECT COUNT(*) FROM `cabopen` where `TravelDate` = a.`TravelDate`)) * 100 as Active, ((SELECT COUNT(*) FROM `cabopen` where `CabStatus` = 'I' and `TravelDate` = a.`TravelDate`)/(SELECT COUNT(*) FROM `cabopen` where `TravelDate` = a.`TravelDate`)) * 100 as InActive, (SELECT COUNT(*) FROM `cabmembers` where `CabId` IN(SELECT `CabId` FROM `cabopen` where `TravelDate` = DATE_FORMAT(NOW(),'%e/%m/%Y')) and `DropStatus` = 'Yes') As NoofPersons from `cabopen` a where a.`TravelDate` = DATE_FORMAT(NOW(),'%e/%m/%Y')");														
		if ($stmt->execute())
		{
			$rowCount = (int) $stmt->rowCount();
			$result = $stmt->fetchAll(PDO::FETCH_ASSOC);				
			if ($rowCount > 0)
			{
				foreach ($result as $row) 
				{																
					echo "<div class='pure-g pure-g1' style='font-size:13px;'>";
					echo "<div class='pure-u-2-5'><p><a href='mAllpool.php?DropStatus=A&Date=" . $row['TravelDate'] . "'>" . $row['TravelDate'] . "</a></p></div>";
					echo "<div class='pure-u-1-5'><p>" . round($row['Active']) . "</p></div>";									
					echo "<div class='pure-u-1-5'><p>" . round($row['InActive']) . "</p></div>";		
					echo "<div class='pure-u-1-5'><p><a href='mAllpool.php?DropStatus=Y&Date=" . $row['TravelDate'] . "'>" . $row['NoofPersons'] . "</a></p></div>";		
					echo "</div>";
				}											
			}				
			else
			{
				echo "<span style='color:Green;font-size:13px; font-weight:bold;'>No results to display!</span>";
			}
		}	
		else
		{							
			echo "<span style='color:Red;font-size:13px; font-weight:bold;'>Error: " . $con->error . "</span>";
		}
		echo "</div>";
	}
?>	
<script type="text/javascript">
	$(document).ready(function(){
		var hdnValues = document.getElementById('hdnData');	
		if(hdnValues != undefined)
		{
			var data = hdnValues.value;	
			$('#chartContainer').show();
			var plot1 = $.jqplot ('chartContainer', [eval(data)],
			{
			  gridPadding: {top:0, bottom:38, left:0, right:0},
			  seriesDefaults: {		
				shadow: false, 
				renderer: $.jqplot.PieRenderer,
				trendline:{ show:false }, 
				rendererOptions: {				 
				  showDataLabels: true,
				  sliceMargin: 4, 
				  lineWidth: 5,
				  padding: 10,
				  dataLabels: 'value'
				}
			  },
			  legend:{
					show: true, 					
					rendererOptions: {
						numberRows: 5
					}, 					
					marginTop: '15px',
					location: 's',
					placement: 'outside'
				}    
			}
		  );
		}
	});	
  </script>	
</form>
</div>