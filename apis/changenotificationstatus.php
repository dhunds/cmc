<?php

 include ('connection.php');
	
 $userId = $_POST['userId'];
 
 $sql = "UPDATE notifications SET StatusArchieve='Yes' WHERE Trim(receivedMemberUserId) = Trim('$userId')";
 $stmt = $con->query($sql);
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
