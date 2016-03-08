<?php 
include ('connection.php');

?>

<h2 class="headingText">Configuration Settings</H4>
<div>
      <form action="visibilty.php" method="post">
		<table width="400">
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
				<td>
					<input class="cBtn" type=submit  value='Search'>
				</td>
				<td>
					<input class="cBtn" type=button onClick="location.href='madd.php'" value='Add'>
				</td>
			</tr>
		</table>
	  
<?php
	if($_POST)
	{
		$sql = "SELECT * FROM `country` Where `countryCode`='" . $_POST['drpCountry'] . "'";
		$stmt = $con->prepare($sql); 
		$stmt->execute();
		$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$intVal = 0;
		
		echo "<table width='400' style='margin-top:20px;'>";
		foreach ($result as $row) 
		{		
			if(ctype_space($row['countryCode']) == false)
			{					
				if($intVal === 0)
				{				
					echo "<tr><th class='tHeading'>Country Code</th><th class='tHeading'>Field Name</th><th class='tHeading'>Visibility</th><th></tr>";			
				}
				$intVal = $intVal + 1;
				echo "<tr>"; 
				echo "<td align='center'><p>" . $row['countryCode']. "</p></td>";
				echo "<td align='center'><p>" . $row['fieldName']. "</p></td>";
				echo "<td align='center'><p>" . $row['visibilityOption']. "</p></td>";
				echo "</tr>";
			}
		}
		echo "</table>";		
	}
?>
</form>
</div>
