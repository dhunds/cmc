<?php
include('connection.php');

$cabType = $_GET['cabType'];
$productid = $_GET['productid'];
$lat = $_GET['lat'];
$lon = $_GET['lon'];
$elat = $_GET['elat'];
$elon = $_GET['elon'];

if (isset($_GET['cabID'])) {
    $cabID = $_GET['cabID'];
} else {
    $cabID = 0;
}

$sql2 = "INSERT INTO cabbookingrequest(cabtype, product_id, start_latitude, start_longitude, end_latitude, end_longitude, cabID) VALUES ('$cabType','$productid','$lat','$lon','$elat','$elon','$cabID')";
$stmt2 = $con->prepare($sql2);
$res2 = $stmt2->execute();
$cabrequestID = $con->lastInsertId();

echo $cabrequestID;
