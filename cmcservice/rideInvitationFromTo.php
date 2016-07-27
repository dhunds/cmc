<?php
include('connection.php');
include('../common.php');

$nearbyRides = array();
$privateRides = array();
$groupCities = array();

if (isset($_POST['sLatLon']) && isset($_POST['eLatLon']) && isset($_POST['mobileNumber']) && $_POST['mobileNumber'] != ''){

    $mobileNumber = $_POST['mobileNumber'];
    
    list($sLat, $sLon) = explode(',', $_POST['sLatLon']);
    list($eLat, $eLon) = explode(',', $_POST['eLatLon']);

    $startLocation = $_POST['startLocation'];
    $endLocation = $_POST['endLocation'];

    // Log search locations
    $sql = "INSERT INTO searchLocations (fromLocation, toLocation, mobileNumber) VALUES ('$startLocation','$endLocation','$mobileNumber')";
    $stmt = $con->prepare($sql);
    $stmt->execute();
    // End Logging

    $proximity = rideProximity();

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
         (
            6371 * acos (
              cos ( radians($eLat) )
              * cos( radians( eLat ) )
              * cos( radians( eLon ) - radians($eLon) )
              + sin ( radians($eLat) )
              * sin( radians( eLat ) )
            )
          ) AS destination,
            co.CabId, co.MobileNumber, co.OwnerName, co.FromLocation, co.ToLocation, co.FromShortName, co.ToShortName, co.sLatLon, co.eLatLon, co.TravelDate, co.TravelTime, co.Seats, co.Distance, co.ExpTripDuration, co.OpenTime, co.CabStatus, co.status, co.RateNotificationSend, co.ExpStartDateTime, co.ExpEndDateTime, co.OwnerChatStatus, co.FareDetails, co.RemainingSeats, 'N' As IsOwner, CONCAT((co.Seats - co.RemainingSeats),'/', co.Seats) as Seat_Status, co.rideType, co.perKmCharge, ui.imagename, cr.BookingRefNo, cn.CabName, cr.DriverName, cr.DriverNumber, cr.CarNumber, cr.CarType, pm.PoolId, pm.PoolName, pm.rGid, v.vehicleModel, vd.registrationNumber, vd.isCommercial, ru.socialType, ru.CreatedOn
    FROM cabopen co
    LEFT JOIN groupCabs gc ON co.CabId = gc.cabId
    LEFT JOIN userpoolsmaster pm ON gc.groupId = pm.PoolId
    JOIN registeredusers ru ON co.MobileNumber = ru.MobileNumber
    LEFT JOIN userprofileimage ui ON co.MobileNumber = ui.MobileNumber
    LEFT JOIN cmccabrecords cr ON co.CabId = cr.CabId
    LEFT JOIN cabnames cn ON cn.CabNameID = cr.CabNameID
    LEFT JOIN userVehicleDetail vd ON co.MobileNumber = vd.mobileNumber
    LEFT JOIN vehicle v ON v.id = vd.vehicleId
    WHERE NOW() < DATE_ADD(co.ExpStartDateTime, INTERVAL 30 MINUTE)
    AND co.MobileNumber !='$mobileNumber'
    AND co.status < 1
    AND co.CabStatus ='A'
    AND co.RemainingSeats >0
    AND NOT EXISTS (SELECT 1 FROM cabmembers cm2 WHERE cm2.CabId = co.CabId AND cm2.MemberNumber='$mobileNumber')
    HAVING origin < ".$proximity."
    AND destination < ".$proximity;

    if (isset($_POST['fromCity']) && $_POST['fromCity'] !='' && isset($_POST['toCity']) && $_POST['toCity'] !='') {
        $fromCity = $_POST['fromCity'];
        $toCity = $_POST['toCity'];

        $groupCities = getGroupCities($fromCity);

        $sql .= " UNION

        SELECT
        (
            6371 * acos (
              cos ( radians($sLat) )
              * cos( radians( sLat ) )
              * cos( radians( sLon ) - radians($sLon) )
              + sin ( radians($sLat) )
              * sin( radians( sLat ) )
            )
          ) AS origin,
         (
            6371 * acos (
              cos ( radians($eLat) )
              * cos( radians( eLat ) )
              * cos( radians( eLon ) - radians($eLon) )
              + sin ( radians($eLat) )
              * sin( radians( eLat ) )
            )
          ) AS destination,
            co.CabId, co.MobileNumber, co.OwnerName, co.FromLocation, co.ToLocation, co.FromShortName, co.ToShortName, co.sLatLon, co.eLatLon, co.TravelDate, co.TravelTime, co.Seats, co.Distance, co.ExpTripDuration, co.OpenTime, co.CabStatus, co.status, co.RateNotificationSend, co.ExpStartDateTime, co.ExpEndDateTime, co.OwnerChatStatus, co.FareDetails, co.RemainingSeats, 'N' As IsOwner, CONCAT((co.Seats - co.RemainingSeats),'/', co.Seats) as Seat_Status, co.rideType, co.perKmCharge, ui.imagename, cr.BookingRefNo, cn.CabName, cr.DriverName, cr.DriverNumber, cr.CarNumber, cr.CarType, pm.PoolId, pm.PoolName, pm.rGid, v.vehicleModel, vd.registrationNumber, vd.isCommercial, ru.socialType, ru.CreatedOn
    FROM cabopen co
    LEFT JOIN groupCabs gc ON co.CabId = gc.cabId
    LEFT JOIN userpoolsmaster pm ON gc.groupId = pm.PoolId
    JOIN registeredusers ru ON co.MobileNumber = ru.MobileNumber
    LEFT JOIN userprofileimage ui ON co.MobileNumber = ui.MobileNumber
    LEFT JOIN cmccabrecords cr ON co.CabId = cr.CabId
    LEFT JOIN cabnames cn ON cn.CabNameID = cr.CabNameID
    LEFT JOIN userVehicleDetail vd ON co.MobileNumber = vd.mobileNumber
    LEFT JOIN vehicle v ON v.id = vd.vehicleId
    WHERE NOW() < DATE_ADD(co.ExpStartDateTime, INTERVAL 30 MINUTE)
    AND co.MobileNumber !='$mobileNumber'
    AND co.status < 1
    AND co.CabStatus ='A'
    AND co.fromCity ='".$fromCity."'
    AND co.toCity ='".$toCity."'
    AND co.RemainingSeats >0
    AND NOT EXISTS (SELECT 1 FROM cabmembers cm2 WHERE cm2.CabId = co.CabId AND cm2.MemberNumber='$mobileNumber')";
    }

    $sql .= " ORDER BY ExpStartDateTime";

    $stmt = $con->query($sql);
    $found = $con->query("SELECT FOUND_ROWS()")->fetchColumn();
    $nearbyGroupIds = array();

    if ($found > 0) {

        while ($rides = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $nearbyRides[] = $rides;
            $nearbyGroupIds[] = $rides['PoolId'];
        }
    }

    $publicRides = [];
    $nearbyGroupIds = array_unique($nearbyGroupIds);

    foreach ($nearbyGroupIds as $id){
        $tempArr = [];
        $tempRides = [];

        foreach($nearbyRides as $ride){

            if ($id == $ride['PoolId']) {
                if (strtolower($ride['fromCity']) == strtolower($fromCity) && (!in_array($ride['toCity'], $groupCities))) {
                    $tempArr['id'] = null;
                    $tempArr['rGid'] = null;
                    $tempArr['name'] = ucfirst($fromCity) . ' to ' . ucfirst($city);
                    $ride['isIntercity'] = "1";
                } else {
                    $tempArr['id'] = $ride['PoolId'];
                    $tempArr['rGid'] = $ride['rGid'];
                    $tempArr['name'] = $ride['PoolName'];
                    $ride['isIntercity'] = "0";
                }
                
                $tempRides[] = $ride;
            }
        }
        if (!empty($tempRides)) {
            $tempArr['rides'] = $tempRides;
            $publicRides[] = $tempArr;
        }
    }

    $AllRides['privateRides'] = $privateRides;
    $AllRides['publicRides'] = $publicRides;

    if (count($AllRides) > 0) {
        $finalArray['status'] = 'success';
        $finalArray['data'] = $AllRides;
        http_response_code(200);
        header('Content-Type: application/json');
        echo json_encode($finalArray);
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
