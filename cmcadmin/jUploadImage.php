<?php

include ('connection.php');
$imgName =  $_REQUEST['phone'].'.jpg';

     $imageStr=$_REQUEST['imageStr'];
     $binary=base64_decode($imageStr);
    header('Content-Type: bitmap; charset=utf-8');
    $file = fopen('ProfileImages/' . $ImageName, 'wb');
    fwrite($file, $binary);
    fclose($file);
	
$sql3 = "UPDATE `userprofileimage` SET `imagename`= '$ImageName' WHERE `MobileNumber` = '$MobileNumber'";
$stmt3 = $con->prepare($sql3);
$res3 = $stmt3->execute();

if ($res3 === true) 
		{
			 echo $ImageName;
		}
		else {
			echo 'Error';
		}
 ?>