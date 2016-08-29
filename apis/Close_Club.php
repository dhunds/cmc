<?php

include ('connection.php');

$poolid = $_POST['poolid'];

$sql12 = "UPDATE `userpoolsmaster` set `PoolStatus` = 'CLOSED' where `PoolId` ='$poolid'";
$stmt12 = $con->prepare($sql12);
$res12 = $stmt12->execute();
if($res12 == true)
{
	echo "SUCCESS";
}	
else
{
	echo "FAILURE";
}
		
?>