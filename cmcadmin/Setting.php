<?php 
	
	echo "<h1 class='headingText'>Settings</h1>";
	echo "<div class='articleBorder'>";
//	echo "<div class='pure-u-1'><p style='text-align:right;'><a href='mEditGroupCities.php'>Add New Cities</a></p></div>";
	echo "<div class='pure-g' style='font-size:13px; font-weight:bold;'>";	
	echo "<div class='pure-u-2-5'><p class='tHeading'>Name</p></div>";
	echo "<div class='pure-u-1-5'><p class='tHeading'>Value</p></div>";
	echo "<div class='pure-u-1-5'><p class='tHeading'>Description</p></div>";
	echo "<div class='pure-u-1-5'><p class='tHeading'>Action</p></div>";		
	echo "</div>";
	
	$stmt = $con->prepare("Select * from `settings`");														
	if ($stmt->execute())
	{
		$rowCount = (int) $stmt->rowCount();
		$result = $stmt->fetchAll(PDO::FETCH_ASSOC);				
		if ($rowCount > 0)
		{
			foreach ($result as $row) 
			{																
				echo "<div class='pure-g pure-g1' style='font-size:13px;'>";							
				echo "<div class='pure-u-2-5'><p>" . $row['setName'] . " </p></div>";	
				echo "<div class='pure-u-1-5'><p>" . $row['setValue'] . " </p></div>";
				echo "<div class='pure-u-1-5'><p>" . $row['setDescription'] . " </p></div>";
				echo "<div class='pure-u-1-5'><p><a href='mEditSetting.php?id=" . $row['setID'] . "'>Edit</a></p></div>";
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
