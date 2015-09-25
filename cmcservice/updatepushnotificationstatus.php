<?php

include ('connection.php');

//Fetching Values from URL
$MobileNumber = $_POST['MobileNumber'];
$PushStatus = $_POST['PushStatus'];

$sql2 = "UPDATE `registeredusers` SET `PushNotification`='$PushStatus' WHERE `MobileNumber` = '$MobileNumber'";
$stmt2 = $con->prepare($sql2);
$res2 = $stmt2->execute();

if ($res2 === true) 
		{
			echo 'update success';
		}
		else {
			echo 'Error';
		}

?>