<?php
include ('connection.php');
echo "<h2 class='headingText'>Members Name</h2>";
echo "<div>";
echo "<table>";

$id = urldecode($_GET['id']);
try 
{
	$sql = "SELECT * FROM `userpoolsslave` where PoolId ='$id'";
	$stmt = $con->prepare($sql); 
    $stmt->execute();    
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
	if($result)
	{
	echo "<tr><th class='tHeading'>Member(s) Name</th><th class='tHeading' width='50%'>Member(s) Number</th><th></tr>";
	foreach ($result as $row) 
	{	
		echo "<tr>"; 
		echo "<td><p style='margin-left: 10px;'>" . $row['MemberName']. "</p></td>";
		echo "<td><p style='margin-left: 10px;'>" . $row['MemberNumber']. "</p></td>";
		echo "</tr>";
	}
    }
	else
	{
	echo"No Result Found !!";
	}
}
catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
$conn = null;
echo "</table>";
echo "</div>";
?>