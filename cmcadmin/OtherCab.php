<?php
include('functions.php');

	echo "<h1 class='headingText'>Cab Details</h1>";
	echo "<div class='articleBorder'>";
	echo "<div class='pure-u-1'><p style='text-align:right;'><a href='mcabdetail.php'>Add New Cabs</a><a style='margin-left:20px;' href='exportCabDetails.php'><img src='images/icon_excel.gif'  border='0'/></a></p></div>";
	
	
	echo "<div class='pure-g' style='font-size:13px; font-weight:bold;'>";
	echo "<div class='pure-u-2-24'><p class='tHeading'>&nbsp;</p></div>";	
	echo "<div class='pure-u-2-24'><p class='tHeading'>&nbsp;</p></div>";
	echo "<div class='pure-u-4-24'><p class='tHeading'>&nbsp;</p></div>";		
	echo "<div class='pure-u-2-24'><p class='tHeading'>&nbsp;</p></div>";			
	echo "<div class='pure-u-2-24'><p class='tHeading'>&nbsp;</p></div>";
	echo "<div class='pure-u-2-24'><p class='tHeading'>&nbsp;</p></div>";
	echo "<div class='pure-u-2-24'><p class='tHeading'>&nbsp;</p></div>";
	echo "<div class='pure-u-6-24'><p class='tHeading' style='text-align:center;background:#3B5279;'>Night Time</p></div>";			
	echo "<div class='pure-u-1-24'><p class='tHeading'>&nbsp;</p></div>";
	echo "<div class='pure-u-1-24'><p class='tHeading'>&nbsp;</p></div>";		
	echo "</div>";
	
	
	echo "<div class='pure-g' style='font-size:13px; font-weight:bold;'>";
	echo "<div class='pure-u-2-24'><p class='tHeading'>Cab Name</p></div>";	
	echo "<div class='pure-u-2-24'><p class='tHeading'>Contact No</p></div>";
	echo "<div class='pure-u-4-24'><p class='tHeading'>City</p></div>";		
	echo "<div class='pure-u-2-24'><p class='tHeading'>OutStation</p></div>";			
	echo "<div class='pure-u-2-24'><p class='tHeading'>Base Fare</p></div>";
	echo "<div class='pure-u-2-24'><p class='tHeading'>Base Fare(KM)</p></div>";
	echo "<div class='pure-u-2-24'><p class='tHeading'>Rate/KM</p></div>";
	echo "<div class='pure-u-2-24'><p class='tHeading'>Start Hours</p></div>";	
	echo "<div class='pure-u-2-24'><p class='tHeading'>End Hours</p></div>";	
	echo "<div class='pure-u-2-24'><p class='tHeading'>Rate Multiplier</p></div>";			
	echo "<div class='pure-u-1-24'><p class='tHeading'>Active</p></div>";
	echo "<div class='pure-u-1-24'><p class='tHeading'>Action</p></div>";		
	echo "</div>";

    $sql = "Select * from cabnames a join cabdetails b on a.CabNameID = b.CabNameID order by b.CabNameID,b.City ASC";
	$stmt = $con->prepare($sql);

	if ($stmt->execute())
	{
		$rowCount = (int) $stmt->rowCount();

        $totalpages   = ceil($rowCount/PAGESIZE);

        if(isset($_REQUEST['page']) && $_REQUEST['page']!=''){
            $page = $_REQUEST['page'];
        }else{
            $page = 1;
        }

        $start= ($page-1) * PAGESIZE;
        $sql .=" LIMIT $start , ".PAGESIZE;

        $stmt = $con->prepare($sql);
        $stmt->execute();

		$result = $stmt->fetchAll(PDO::FETCH_ASSOC);				
		if ($rowCount > 0)
		{
			foreach ($result as $row) 
			{																
				echo "<div class='pure-g pure-g1' style='font-size:13px;'>";
				echo "<div class='pure-u-2-24'><p>" . $row['CabName'] . "</p></div>";	
				echo "<div class='pure-u-2-24'><p>" . $row['CabContactNo'] . "</p></div>";				
				echo "<div class='pure-u-4-24'><p>" . $row['City'] . "</p></div>";
				echo "<div class='pure-u-2-24'><p style='text-align:center;'>" . $row['Outstation'] . "</p></div>";			
				echo "<div class='pure-u-2-24'><p style='text-align:center;'>" . $row['BaseFare'] . " </p></div>";
				echo "<div class='pure-u-2-24'><p style='text-align:center;'>" . $row['BaseFareKM'] . " </p></div>";				
				echo "<div class='pure-u-2-24'><p style='text-align:center;'>" . $row['RatePerKMAfterBaseFare'] . "</p></div>";	
				echo "<div class='pure-u-2-24'><p style='text-align:center;'>" . $row['NightTimeStartHours'] . "</p></div>";	
				echo "<div class='pure-u-2-24'><p style='text-align:center;'>" . $row['NightTimeEndHours'] . "</p></div>";
				echo "<div class='pure-u-2-24'><p style='text-align:center;'>" . $row['NightTimeRateMultiplier'] . "</p></div>";								
				echo "<div class='pure-u-1-24'><p>" . $row['Active'] . "</p></div>";	
				echo "<div class='pure-u-1-24'><p><a href='mcabdetail.php?id=" . $row['CabDetailID'] . "'>Edit</a></p></div>";
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

    echo pagination_search($totalpages, $page);

	echo "</div>";
?>
