<?php
include('connection.php');
include('../common.php');

$AllRides = [];

if (isset($_POST['sLatLon']) && isset($_POST['userId']) && $_POST['userId'] != '') {

    $userId = $_POST['userId'];

    list($sLat, $sLon) = explode(',', $_POST['sLatLon']);
    $proximity = rideProximity();

    // Nearby Rides
    $sql = "SELECT
        (
            6371 * acos (
              cos ( radians($sLat) )
              * cos( radians( sLat ) )
              * cos( radians( sLon ) - radians($sLon) )
              + sin ( radians($sLat) )
              * sin( radians( sLat ) )
            )
          ) AS origin,
            co.CabId, co.FromLocation, co.ToLocation, co.FromShortName, co.ToShortName, co.TravelDate, co.TravelTime, CONCAT((co.Seats - co.RemainingSeats),'/', co.Seats) as Seat_Status
    FROM cabopen co
    WHERE NOW() < DATE_ADD(co.ExpStartDateTime, INTERVAL 30 MINUTE)
    AND co.cabType = 2
    AND co.userId !='$userId'
    AND co.status < 1
    AND co.CabStatus ='A'
    AND co.RemainingSeats >0
    HAVING origin < ".$proximity."
    ORDER BY co.ExpStartDateTime";

    $stmt = $con->query($sql);
    $rides = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $AllRides['nearbyRides'] = $rides;

    $sql = "SELECT id FROM cabopen WHERE userId='".$_POST['userId']."' AND CabStatus='A'";
    $stmt = $con->query($sql);
    $found = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

    if ($found) {
        $hasActiveRide ='1';
    } else {
        $hasActiveRide ='0';
    }

    if (count($AllRides) > 0) {
        $finalArray['status'] = 'success';
        $finalArray['hasActiveRide'] = $hasActiveRide;
        $finalArray['data'] = $AllRides;
        http_response_code(200);
        header('Content-Type: application/json');
        echo json_encode($AllRides);
        exit;
    } else {
        http_response_code(500);
        header('Content-Type: application/json');
        echo '{"status":"fail", "message":"No Records Found"}';
        exit;
    }

} else {
    http_response_code(500);
    header('Content-Type: application/json');
    echo '{"status":"failed", "message":"Invalid Params."}';
}