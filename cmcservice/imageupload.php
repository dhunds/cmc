<?php

include ('connection.php');

	$MobileNumber = $_POST['MobileNumber'];
	$ImageName = $MobileNumber . time() .'.jpg';

	$base=$_REQUEST['imagestr'];
    $handle = fopen('test.txt', 'w');

    $binary=base64_decode($base);
    header('Content-Type: bitmap; charset=utf-8');
	
	if (!$file = fopen('/var/www/html/cmc/cmcservice/ProfileImages/' . $ImageName, 'wb')) {
         echo "Error";
         exit;
    }
	
	if (fwrite($file, $binary) === FALSE) {
        echo "Error";
        exit;
    }
	
    $file = fopen('ProfileImages/' . $ImageName, 'x');
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