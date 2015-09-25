 <html>
   <head>
<style>
h4 {
   text-decoration: underline;
  color: Blue;
}
</style>
</head>
<link rel="stylesheet" type="text/css" media="all" href="Calendar/calendar-blue.css"
        title="win2k-cold-1">
    <script type="text/javascript" src="Calendar/calendar.js"></script>
    <script type="text/javascript" src="Calendar/lang/calendar-en.js"></script>
    <script type="text/javascript" src="Calendar/calendar-setup.js"></script>
   </head>
     <div align="center">
 <div align="center">
<h4>Active/Cancel Pools</H4>
<form action="mActive.php" method="POST">

 Date From: <input type="text" name="from" id="from">
 <img src="images/calendar.png" id="dtFrom">
<script type="text/javascript">
                                Calendar.setup({
                                    inputField: 'from',
                                    ifFormat: "%d/%m/%Y",
                                    button: "dtFrom",
                                    //align          :    "Tl",
                                    singleClick: true
                                });
                            </script>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;

 Date To: <input type="text" name="to" id="to">
 <img src="images/calendar.png" id="dtto">
 <script type="text/javascript">
                                Calendar.setup({
                                    inputField: 'to',
                                    ifFormat: "%d/%m/%Y",
                                    button: "dtto",
                                    //align          :    "Tl",
                                    singleClick: true
                                });
                            </script>
 &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<input type="submit" style="background: #0B610B;color: #fff"  name="btnview" value="Search">  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;

			</br></br>
			
			
<?php 
	$allData = '';
	
	if(($_POST['btnview']))
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
<div id="chartContainer" style="height: 300px; width: 30%; display:none;"></div>  
<br/>		
<?php
	if(($_POST['btnview']))
	{
			echo "<br/><br/>";
			echo "<div class='pure-g' style='font-size:14px; font-weight:bold;'>";									
			echo "<div class='pure-u-1'><h4>Number of Active/Cancel Pools</h4></div>";
			echo "</div>";
			echo "<br/>";
			echo "<div class='pure-g' style='font-size:13px;font-weight:bold;'>";
			echo "<div class='pure-u-2-5'>Date</div>";
			echo "<div class='pure-u-2-5'>Active</div>";
			echo "<div class='pure-u-1-5'>InActive</div>";
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
					echo "<div class='pure-u-2-5'><p><a href='mAllpool.php?Active=" . $row['Active'] . "'>" . $row['Active'] . " </p></div>";									
					echo "<div class='pure-u-1-5'><p><a href='mAllpool.php?Active=" . $row['Active'] . "'>" . $row['InActive'] . " </p></div>";		
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
	}
	else
	{
	
			echo "<br/><br/>";
			echo "<div class='pure-g' style='font-size:14px; font-weight:bold;'>";									
			echo "<div class='pure-u-1'><h4>Number of Active/Cancel Pools</h4></div>";
			echo "</div>";
			echo "<br/>";
			echo "<div class='pure-g' style='font-size:13px;font-weight:bold;'>";
			echo "<div class='pure-u-2-5'>Date</div>";
			echo "<div class='pure-u-2-5'>Active</div>";
			echo "<div class='pure-u-1-5'>InActive</div>";
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
	}
?>	

</br></br>
<?php

	if(($_POST['btnview']))
	{
		
			echo "<br/><br/>";
			echo "<div class='pure-g' style='font-size:14px; font-weight:bold;'>";									
			echo "<div class='pure-u-1'><h4>Drop Pool %</h4></div>";
			echo "</div>";
			echo "<br/>";
			echo "<div class='pure-g' style='font-size:13px;font-weight:bold;'>";
			echo "<div class='pure-u-2-5'>Date</div>";
			echo "<div class='pure-u-1-5'>Open Pools</div>";
			echo "<div class='pure-u-1-5'>Pool Cancel(%)</div>";
			echo "<div class='pure-u-1-5'>No. Of Person Drop</div>";
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
	}
	else
	{
			echo "<br/><br/>";
			echo "<div class='pure-g' style='font-size:14px; font-weight:bold;'>";									
			echo "<div class='pure-u-1'><h4>Drop Pool %</h4></div>";
			echo "</div>";
			echo "<br/>";
			echo "<div class='pure-g' style='font-size:13px;font-weight:bold;'>";
			echo "<div class='pure-u-2-5'>Date</div>";
			echo "<div class='pure-u-1-5'>Open Pools</div>";
			echo "<div class='pure-u-1-5'>Pool Cancel(%)</div>";
			echo "<div class='pure-u-1-5'>No. Of Person Drop</div>";
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

			
	</div>
           </form>
        </body>
       </html>
