<?php
	include ('connection.php');
	$phone = $_REQUEST['phone'];
	$imgName =$_REQUEST['imgName'];
	$imgStr=$_REQUEST['imgStr'];
    $binary=base64_decode($imgStr);
    header('Content-Type: bitmap; charset=utf-8');
    $file = fopen('ProfileImages/' . $imgName, 'wb');
    fwrite($file, $binary);
    fclose($file);
	
	$sql = "UPDATE `tblregister` SET `image`='$imgName' WHERE `phone`='$phone'";
	$stmt = $con->prepare($sql);
	$res = $stmt->execute();
	if ($res == true) 
	{
		echo '_JSUCCESS';
	}
	else {
		echo '_JFAIL';
	}
 ?>