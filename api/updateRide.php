<?php
include_once('config.php');

if (isset($_POST['cabId']) && $_POST['cabId'] !='' && isset($_POST['startTime']) && $_POST['startTime'] !='') {

    $stmt = $con->query("SELECT c.cleintId FROM cabOwners c JOIN cabopen co ON co.MobileNumber=c.mobileNumber WHERE co.CabId='".$_POST['cabId']."' AND c.cleintId=".$client_id);
    $isOwner = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

    if(!$isOwner) {
        setResponse(array("code"=>200, "status"=>"Error", "message"=>"You are not authorised to change this ride."));
    }

    $sql = $con->query("SELECT ac.MemberNumber FROM acceptedrequest ac JOIN cabopen co ON co.CabId=ac.CabId WHERE co.CabId='".$_POST['cabId']."' AND co.CabStatus='A' AND ac.Status !='Dropped'");
    $rows = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

    if($rows) {
        setResponse(array("code"=>200, "status"=>"Error", "message"=>"You can not change details of this ride either because people have already joined this ride or the ride no longer exists."));
    }

    $stmt = $con->query("SELECT ExpTripDuration FROM cabopen WHERE CabId ='".$_POST['cabId']."'");
    $ExpTripDuration = $stmt->fetchColumn();

    list($TravelDate, $TravelTime) = explode(" ", $_POST['startTime']);
    $CabId = $_POST['cabId'];

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
        setResponse(array("code"=>200, "status"=>"Success", "message"=>"Ride details updated."));
    } else {
        setResponse(array("code"=>200, "status"=>"Error", "message"=>"An Error occured, Please try later."));
    }

} else {
    setResponse(array("code"=>200, "status"=>"Error", "message"=>"One or more parameter is missing."));
}