<?php

include ('connection.php');
	
$numberlist = $_POST['numberlist'];

$numberlistnew = substr($numberlist, 1, -1);
//$sql = "SELECT a.`FullName`,a.`MobileNumber`,(SELECT `imagename` FROM `userprofileimage` where `MobileNumber` = a.`MobileNumber`) AS `imagename` FROM `registeredusers` a WHERE a.`MobileNumber` IN ($numberlistnew)";

$sql = "SELECT a.`FullName`,a.`MobileNumber`,b.`imagename` FROM `userprofileimage` b, registeredusers a where b.`MobileNumber` = a.`MobileNumber` and a.`MobileNumber` IN ($numberlistnew)";
	$stmt = $con->query($sql);
	//$no_of_users = $stmt->rowCount();
	$no_of_users = $con->query("SELECT FOUND_ROWS()")->fetchColumn();
 if ($no_of_users > 0)
{
 $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
	echo json_encode($rows);
}
else
{
echo "No Users of your contact list in App";
}
 ?>