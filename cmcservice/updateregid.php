<?php

include ('connection.php');
	
$MobileNumber = $_POST['MobileNumber'];
$DeviceToken = $_POST['DeviceToken'];

if ($MobileNumber !='') {
	$sql2 = "UPDATE `registeredusers` SET `DeviceToken`='$DeviceToken' WHERE `MobileNumber` = '$MobileNumber'";
	$stmt2 = $con->prepare($sql2);
	$res2 = $stmt2->execute();
	
	$sql23 = "UPDATE `acceptedrequest` SET `ChatStatus`='offline' WHERE `MemberNumber` = '$MobileNumber'";
	$stmt23 = $con->prepare($sql23);
	$res23 = $stmt23->execute();
	
	if($res2 == true && $res23 == true)
	{
		echo "device token updated";
	}
	else
	{
		echo "error while device token updating";
	}
} else {
	echo "mobile number empty.";
}
 
?>