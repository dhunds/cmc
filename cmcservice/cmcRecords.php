<?php
include('connection.php');

function current_millis()
{
    list($usec, $sec) = explode(" ", microtime());
    return round(((float)$usec + (float)$sec) * 1000);
}

$CabNameID = '';
$RequestID = '';
if (isset($_REQUEST['CabNameID'])) {
    list($CabNameID, $RequestID) = explode('~', $_REQUEST['CabNameID']);
}
$CabType = '';
if (isset($_REQUEST['CabType'])) {
    $CabType = $_REQUEST['CabType'];
}
$ModeID = '';
if (isset($_REQUEST['ModeID'])) {
    $ModeID = $_REQUEST['ModeID'];
}

$FromLocation = '';
if (isset($_REQUEST['FromLocation'])) {
    $FromLocation = $_REQUEST['FromLocation'];
}
$ToLocation = '';
if (isset($_REQUEST['ToLocation'])) {
    $ToLocation = $_REQUEST['ToLocation'];
}
$FromShortName = '';
if (isset($_REQUEST['FromShortName'])) {
    $FromShortName = $_REQUEST['FromShortName'];
}
$ToShortName = '';
if (isset($_REQUEST['ToShortName'])) {
    $ToShortName = $_REQUEST['ToShortName'];
}
$TravelDate = '';
if (isset($_REQUEST['TravelDate'])) {
    $TravelDate = $_REQUEST['TravelDate'];
}
$TravelTime = '';
if (isset($_REQUEST['TravelTime'])) {
    $TravelTime = $_REQUEST['TravelTime'];
}

$Distance = '';
if (isset($_REQUEST['Distance'])) {
    $Distance = $_REQUEST['Distance'];
}

$ExpTripDuration = '';
if (isset($_REQUEST['ExpTripDuration'])) {
    $ExpTripDuration = $_REQUEST['ExpTripDuration'];
}

$CabId = '';
if (isset($_REQUEST['CabId'])) {
    $CabId = ($_REQUEST['CabId'] == 'null') ? '' : $_REQUEST['CabId'];
}

$FromLat = '';
if (isset($_REQUEST['FromLat'])) {
    $FromLat = $_REQUEST['FromLat'];
}
$ToLat = '';
if (isset($_REQUEST['ToLat'])) {
    $ToLat = $_REQUEST['ToLat'];
}
$MobileNumber = '';
if (isset($_REQUEST['MobileNumber'])) {
    $MobileNumber = $_REQUEST['MobileNumber'];
}
$CabUserName = '';
if (isset($_REQUEST['CabUserName'])) {
    $CabUserName = $_REQUEST['CabUserName'];
}
$BookingRefNo = '';
if (isset($_REQUEST['BookingRefNo'])) {
    $BookingRefNo = $_REQUEST['BookingRefNo'];
}

$DriverName = '';
if (isset($_REQUEST['DriverName'])) {
    $DriverName = $_REQUEST['DriverName'];
}
$DriverNumber = '';
if (isset($_REQUEST['DriverNumber'])) {
    $DriverNumber = $_REQUEST['DriverNumber'];
}
$CarNumber = '';
if (isset($_REQUEST['CarNumber'])) {
    $CarNumber = $_REQUEST['CarNumber'];
}
$CarType = '';
if (isset($_REQUEST['CarType'])) {
    $CarType = $_REQUEST['CarType'];
}

$rideType = $_REQUEST['rideType'];


$sql2 = "INSERT INTO cmccabrecords(CabNameID, CabType, ModeID,FromLocation,ToLocation,FromShortName,ToShortName,TravelDate,TravelTime,Distance,ExpTripDuration,CabId,FromLat,ToLat,MobileNumber,CabUserName,BookingRefNo,DriverName,DriverNumber,CarNumber,CarType, bookingRequestID) VALUES ('$CabNameID','$CabType', '$ModeID','$FromLocation','$ToLocation','$FromShortName','$ToShortName','$TravelDate','$TravelTime','$Distance','$ExpTripDuration','$CabId','$FromLat','$ToLat','$MobileNumber','$CabUserName','$BookingRefNo','$DriverName','$DriverNumber','$CarNumber','$CarType', '$RequestID')";
$stmt2 = $con->prepare($sql2);
$res2 = $stmt2->execute();
$reqid = $con->lastInsertId();

if ($res2 == true) {
    if ($ModeID == "1") {
        if ($CabId == '') {
            $stmt1 = $con->query("SELECT * FROM registeredusers WHERE Trim(MobileNumber) = Trim('$MobileNumber') and PushNotification != 'off'");
            $OwnerExists = $con->query("SELECT FOUND_ROWS()")->fetchColumn();
            if ($OwnerExists > 0) {
                while ($row = $stmt1->fetch()) {
                    $OwnerName = $row['FullName'];
                }
            }

            $CabId = $MobileNumber . current_millis();
            $dateInput = explode('/', $TravelDate);
            $cDate = $dateInput[1] . '/' . $dateInput[0] . '/' . $dateInput[2];

            $expTrip = strtotime($cDate . ' ' . $TravelTime);
            $newdate = $expTrip + $ExpTripDuration;
            $ExpEndDateTime = date('Y-m-d H:i:s', $newdate);

            $startDate = $expTrip;
            $ExpStartDateTime = date('Y-m-d H:i:s', $startDate);

            $sqlCab = "INSERT INTO cabopen(CabId, MobileNumber, OwnerName, FromLocation, ToLocation, FromShortName, ToShortName, sLatLon, eLatLon, TravelDate, TravelTime, Seats, RemainingSeats, Distance, OpenTime, ExpTripDuration,ExpStartDateTime,ExpEndDateTime, rideType,) VALUES ('$CabId','$MobileNumber','$OwnerName','$FromLocation','$ToLocation','$FromShortName','$FromLat','$ToLat','$ToShortName','$TravelDate','$TravelTime','0','0','$Distance',now(),'$ExpTripDuration', '$ExpStartDateTime','$ExpEndDateTime', '$rideType')";
            $stmtCab = $con->prepare($sqlCab);
            $resCab = $stmtCab->execute();

            if ($resCab == true) {
                $sqlUpdate = "UPDATE cmccabrecords set CabId = '$CabId' where reqid = '$reqid'";
                $stmtUpdate = $con->prepare($sqlUpdate);
                $resUpdate = $stmtUpdate->execute();
            }
        }
    }
    echo "SUCCESS";
} else {
    echo "FAILURE";
}
