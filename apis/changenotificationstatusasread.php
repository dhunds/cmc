<?php

 include ('connection.php');
	
 $MobileNumber = $_POST['MemberNumber'];
 $memberUserId = $_POST['memberUserId'];

 
	$sql = "UPDATE `notifications` SET `Status`='R' WHERE Trim(`ReceiveMemberNumber`) = Trim('$MobileNumber')";
	$stmt = $con->query($sql);
	//$no_of_rows = $stmt->rowCount();
	$no_of_rows = $con->query("SELECT FOUND_ROWS()")->fetchColumn();
 if ($no_of_rows > 0)
{
 $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
	echo json_encode($rows);
}
else
{
echo "No Notification !!";
} 
 ?>