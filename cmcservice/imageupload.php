<?php
include ('connection.php');

$MobileNumber = $_POST['MobileNumber'];
$imageName = $MobileNumber . '.png';
$file = 'ProfileImages/'. $imageName;


$base=$_REQUEST['imagestr'];
$base = str_replace(' ', '+', $base);
$binary = base64_decode($base);

if (file_put_contents($file, $binary)){
	$sql = "UPDATE userprofileimage SET imagename= '$imageName' WHERE MobileNumber = '$MobileNumber'";
	$stmt = $con->prepare($sql);
	$stmt->execute();
	echo $imageName;
	exit;
} else {
	echo "Error";
    exit;
}