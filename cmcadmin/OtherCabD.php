<?php 
	
	echo "<h1 class='headingText'>Cab Master</h1>";
	echo "<div class='articleBorder'>";
	echo "<div class='pure-u-1'><p style='text-align:right;'><a href='mEditOtherCabs.php'>Add New Cabs</a></p></div>";
	echo "<div class='pure-g' style='font-size:13px; font-weight:bold;'>";	
	echo "<div class='pure-u-4-24'><p class='tHeading'>CabName</p></div>";
	echo "<div class='pure-u-4-24'><p class='tHeading'>Full Name</p></div>";	
	echo "<div class='pure-u-4-24'><p class='tHeading'>Address</p></div>";
	echo "<div class='pure-u-3-24'><p class='tHeading'>MobileSite</p></div>";
	echo "<div class='pure-u-3-24'><p class='tHeading'>Package Name</p></div>";
	echo "<div class='pure-u-3-24'><p class='tHeading'>Mode</p></div>";	
	echo "<div class='pure-u-3-24'><p class='tHeading'>Action</p></div>";		
	echo "</div>";
	
	$stmt = $con->prepare("Select * from `cabnames`");														
	if ($stmt->execute())
	{
		$rowCount = (int) $stmt->rowCount();
		$result = $stmt->fetchAll(PDO::FETCH_ASSOC);				
		if ($rowCount > 0)
		{
			foreach ($result as $row) 
			{																
				echo "<div class='pure-g pure-g1' style='font-size:13px;'>";							
				echo "<div class='pure-u-4-24'><p>" . $row['CabName'] . " </p></div>";	
				echo "<div class='pure-u-4-24'><p>" . $row['CabFullName'] . " </p></div>";
				echo "<div class='pure-u-4-24'><p>" . $row['CabAddress'] . "</p></div>";						
				echo "<div class='pure-u-3-24'><p>" . $row['CabMobileSite'] . "</p></div>";
				echo "<div class='pure-u-3-24'><p>" . $row['CabPackageName'] . "</p></div>";
				echo "<div class='pure-u-3-24'><p>" . $row['CabMode'] . "</p></div>";					
				echo "<div class='pure-u-3-24'><p><a href='mEditOtherCabs.php?id=" . $row['CabNameID'] . "'>Edit</a></p></div>";						
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
	
?>
