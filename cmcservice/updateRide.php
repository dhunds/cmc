<?php
include('connection.php');
include_once('includes/functions.php');

if (isset($_POST['CabId']) && $_POST['CabId'] !='' && isset($_POST['TravelDate']) && $_POST['TravelDate'] !='' && isset($_POST['TravelTime']) && $_POST['TravelTime'] !='') {

    $CabId = $_POST['CabId'];
    $TravelDate = $_POST['TravelDate'];
    $TravelTime = $_POST['TravelTime'];

    $dateInput = explode('/', $TravelDate);
    $cDate = $dateInput[1] . '/' . $dateInput[0] . '/' . $dateInput[2];

    $expTrip = strtotime($cDate . ' ' . $TravelTime);
    $newdate = $expTrip + $ExpTripDuration;
    $ExpEndDateTime = date('Y-m-d H:i:s', $newdate);

    $startDate = $expTrip;
    $ExpStartDateTime = date('Y-m-d H:i:s', $startDate);

    $sql = "UPDATE cabopen SET TravelDate='$TravelDate', TravelTime='$TravelTime', ExpEndDateTime='$ExpEndDateTime', ExpStartDateTime='$ExpStartDateTime', OpenTime=now() WHERE CabId='$CabId'";

    $stmt = $con->prepare($sql);

    if ($stmt->execute()){
        setResponse(array("code"=>200, "status"=>"success", "message"=>"Ride details updated."));
    } else {
        setResponse(array("code"=>200, "status"=>"Error", "message"=>"An error occurred, please try again later."));
    }

} else {
    setResponse(array("code"=>200, "status"=>"Error", "message"=>"Invalid Parameters"));
}