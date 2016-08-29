<?php
include('connection.php');

$cabType = $_REQUEST['cabType'];
$productid = $_REQUEST['productid'];
$lat = $_REQUEST['lat'];
$lon = $_REQUEST['lon'];
$elat = $_REQUEST['elat'];
$elon = $_REQUEST['elon'];

if (isset($_REQUEST['cabID'])) {
    $cabID = $_REQUEST['cabID'];
} else {
    $cabID = 0;
}

$sql2 = "INSERT INTO cabbookingrequest(cabtype, product_id, start_latitude, start_longitude, end_latitude, end_longitude, cabID) VALUES ('$cabType','$productid','$lat','$lon','$elat','$elon','$cabID')";
$stmt2 = $con->prepare($sql2);
$res2 = $stmt2->execute();
$cabrequestID = $con->lastInsertId();

echo $cabrequestID;
