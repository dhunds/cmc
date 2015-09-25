<?php

include ('connection.php');

$Number = $_POST['Number'];

$sql2 = "UPDATE `registeredusers` SET `LastLoginDateTime`='now()' WHERE `MobileNumber`='$Number';";
$stmt2 = $con->prepare($sql2);
$res2 = $stmt2->execute();


if ($res2 == true)
		{
			echo 'Form Submitted Succesfully';
		}
		else {
			echo 'Error';
		}
?>