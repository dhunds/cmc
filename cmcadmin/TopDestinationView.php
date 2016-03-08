<?php
include ('connection.php');
echo "<h2 class='headingText'>Top Destinations</h2>";
echo "<div>";
echo "<table>";
echo "<tr><th class='tHeading'>OwnerName</th><th class='tHeading' width='30%'>From Location</th><th class='tHeading' width='30%'>To Location</th><th class='tHeading'>Travel Date</th><th class='tHeading'>Travel Time</th><th></tr>";
$location = urldecode($_GET['local']);
try 
{
	$sql = "SELECT OwnerName,FromLocation,ToLocation,TravelDate,TravelTime FROM cabopen where ToLocation like '%$location%'";
	$stmt = $con->prepare($sql); 
    $stmt->execute();    
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
	foreach ($result as $row) 
	{	
		echo "<tr>"; 
		echo "<td><p>" . $row['OwnerName']. "</p></td>";
		echo "<td><p>" . $row['FromLocation']. "</p></td>";
		echo "<td><p>" . $row['ToLocation']. "</p></td>";
		echo "<td><p style='text-align:center;'>" . $row['TravelDate']. "</p></td>";
		echo "<td><p style='text-align:center;'>" . $row['TravelTime']. "</p></td>";
		echo "</tr>";
	}		    
}
catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
$conn = null;
echo "</table>";
echo "</div>";
?>