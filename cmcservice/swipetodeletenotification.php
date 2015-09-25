<?php

 include ('connection.php');
	
 $MobileNumber = $_POST['MemberNumber'];
 $NID = $_POST['NID'];
 
	$sql = "UPDATE `notifications` SET `StatusArchieve` = 'Yes' WHERE Trim(`NotificationId`) = Trim('$NID') AND Trim(`ReceiveMemberNumber`) = Trim('$MobileNumber')";
	$stmt = $con->query($sql);
	//$no_of_rows = $stmt->rowCount();
	$no_of_rows = $con->query("SELECT FOUND_ROWS()")->fetchColumn();
 if ($no_of_rows > 0)
{
	echo "updated";
}
else
{
echo "error";
} 
 ?>