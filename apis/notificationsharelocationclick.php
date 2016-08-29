<?php

include ('connection.php');
	
$CabId = $_POST['CabId'];
$rnum = $_POST['rnum'];
$nid = $_POST['nid'];

$sql2 = "UPDATE `notifications` SET `Status`='R' WHERE `ReceiveMemberNumber` = '$rnum' AND `NotificationId` = '$nid'";
$stmt2 = $con->prepare($sql2);
$res2 = $stmt2->execute();

 ?>