<?php

 include ('connection.php');
	
$userId = $_POST['userId'];

$sql = "SELECT count(*) FROM notifications WHERE Trim(receivedMemberUserId) = Trim('$userId') AND Trim(Status) = 'U' AND Trim(StatusArchieve) = 'No' and RefStatus IS NULL";
$result = $con->prepare($sql); 
$result->execute(); 
$number_of_rows = $result->fetchColumn(); 
echo $number_of_rows;