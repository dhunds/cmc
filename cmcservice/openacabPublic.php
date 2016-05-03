<?php
include('connection.php');
include('../common.php');

if (isset($_POST['sLatLon']) && isset($_POST['eLatLon']) && $_POST['sLatLon'] != '' && $_POST['eLatLon'] != '' && isset($_POST['CabId']) && $_POST['CabId'] != '' && isset($_POST['MobileNumber']) && $_POST['MobileNumber'] != '') {

    list($sLat, $sLon) = explode(',', $_POST['sLatLon']);
    list($eLat, $eLon) = explode(',', $_POST['eLatLon']);


    $proximity = rideProximity();

    $CabId = $_POST['CabId'];
    $MobileNumber = $_POST['MobileNumber'];
    $OwnerName = $_POST['OwnerName'];
    $FromLocation = $_POST['FromLocation'];
    $ToLocation = $_POST['ToLocation'];
    $TravelDate = $_POST['TravelDate'];
    $TravelTime = $_POST['TravelTime'];
    $Seats = $_POST['Seats'];
    $RemainingSeats = $_POST['RemainingSeats'];
    $Distance = $_POST['Distance'];

    $ExpTripDuration = $_POST['ExpTripDuration'];
    $FromShortName = $_POST['FromShortName'];
    $ToShortName = $_POST['ToShortName'];

    $rideType = '';
    $perKmCharge = perKMChargeIntracity();

    if (isset($_POST['rideType']) && $_POST['rideType'] != '') {
        $rideType = $_POST['rideType'];
    }

    if (isset($_POST['perKmCharge']) && $_POST['perKmCharge'] != '') {
        $perKmCharge = $_POST['perKmCharge'];
    }

    $dateInput = explode('/', $TravelDate);
    $cDate = $dateInput[1] . '/' . $dateInput[0] . '/' . $dateInput[2];

    $expTrip = strtotime($cDate . ' ' . $TravelTime);
    $newdate = $expTrip + $ExpTripDuration;
    $ExpEndDateTime = date('Y-m-d H:i:s', $newdate);

    $startDate = $expTrip;
    $ExpStartDateTime = date('Y-m-d H:i:s', $startDate);

    $sql = "SELECT
              PoolId,
              PoolName,
              (
                6371 * acos (
                  cos ( radians($sLat) )
                  * cos( radians( startLat ) )
                  * cos( radians( startLon ) - radians($sLon) )
                  + sin ( radians($sLat) )
                  * sin( radians( startLat ) )
                )
              ) AS origin,
              (
                6371 * acos (
                  cos ( radians($eLat) )
                  * cos( radians( endLat ) )
                  * cos( radians( endLon ) - radians($eLon) )
                  + sin ( radians($eLat) )
                  * sin( radians( endLat ) )
                )
              ) AS destination

            FROM userpoolsmaster
            WHERE poolType=2
            HAVING origin < ".$proximity." AND destination < ".$proximity."
            ORDER BY origin, destination LIMIT 0,1";


    $stmt = $con->query($sql);
    $found = $con->query("SELECT FOUND_ROWS()")->fetchColumn();
    $createGroup = 0;

    if ($found < 1) {
        $createGroup = createPublicGroups($con, $sLat, $sLon, $eLat, $eLon, $FromShortName, $ToShortName);
        $groupId = $createGroup;
    } else {
        $nearbyGroup = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $groupId = $nearbyGroup[0]['PoolId'];
    }

    if ($found > 0 || $createGroup) {

        $sql = "INSERT INTO cabopen(CabId, MobileNumber, OwnerName, FromLocation, ToLocation, FromShortName, ToShortName, TravelDate, TravelTime, Seats, RemainingSeats, Distance, OpenTime, ExpTripDuration,ExpStartDateTime,ExpEndDateTime,rideType,perKmCharge) VALUES ('$CabId','$MobileNumber','$OwnerName','$FromLocation','$ToLocation','$FromShortName','$ToShortName','$TravelDate','$TravelTime','$Seats','$RemainingSeats','$Distance',now(),'$ExpTripDuration', '$ExpStartDateTime','$ExpEndDateTime','$rideType','$perKmCharge')";

        $stmt = $con->prepare($sql);
        $res = $stmt->execute();

        $sql = "INSERT INTO groupCabs(groupId, cabId) VALUES ($groupId, '$CabId')";

        $stmt = $con->prepare($sql);
        $res = $stmt->execute();

        if ($res) {

            http_response_code(200);
            header('Content-Type: application/json');
            echo '{"status":"success", "message":"Ride created."}';
            exit;

        } else {
            http_response_code(200);
            header('Content-Type: application/json');
            echo '{"status":"fail", "message":"An Error Occured, Please try again later!"}';
            exit;
        }
    } else {
        http_response_code(500);
        header('Content-Type: application/json');
        echo '{"status":"failed", "message":"An Error occured, Please try later."}';
    }

} else {
    http_response_code(500);
    header('Content-Type: application/json');
    echo '{"status":"failed", "message":"Invalid Params."}';
}