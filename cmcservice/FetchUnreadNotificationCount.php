<?php

 include ('connection.php');
	
$MobileNumber = $_POST['MobileNumber'];
$sql = "SELECT count(*) FROM `notifications` WHERE Trim(`ReceiveMemberNumber`) = Trim('$MobileNumber') AND Trim(`Status`) = 'U' AND Trim(`StatusArchieve`) = 'No' and `RefStatus` IS NULL";
$result = $con->prepare($sql); 
$result->execute(); 
$number_of_rows = $result->fetchColumn(); 
echo $number_of_rows;
 ?>