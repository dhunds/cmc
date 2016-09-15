<?php
include('connection.php');
include('../common.php');

$nearbyRides = array();
$privateRides = array();
$groupCities = array();
$myPublicGroupRides = array();

if (isset($_POST['sLatLon']) && isset($_POST['eLatLon']) && isset($_POST['mobileNumber']) && $_POST['mobileNumber'] != ''){

    $fromCity = '';
    $toCity = '';
    $isOldApi = 1;
    $isIntercity = 0;
    $mobileNumber = $_POST['mobileNumber'];
    $userId = $_POST['userId'];
    $startLocation = $_POST['startLocation'];
    $endLocation = $_POST['endLocation'];

    // Log search locations
        $sql = "INSERT INTO searchLocations (fromLocation, toLocation, mobileNumber, userId) VALUES ('$startLocation','$endLocation','$mobileNumber', '$userId')";
        $stmt = $con->prepare($sql);
        $stmt->execute();
    // End Logging


    
    list($sLat, $sLon) = explode(',', $_POST['sLatLon']);
    list($eLat, $eLon) = explode(',', $_POST['eLatLon']);

    $proximity = rideProximity();

    if (isset($_POST['fromCity']) && $_POST['fromCity'] !='' && isset($_POST['toCity']) && $_POST['toCity'] !='') {

        $fromCity = $_POST['fromCity'];
        $toCity = $_POST['toCity'];
        $isOldApi = 0;

        if (isIntracityRide($fromCity, $toCity)){
            $isIntercity =0;
        } else {
            $isIntercity =1;
        }
    }

    if ($isOldApi || !$isIntercity) {

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
            co.CabId, co.userId, co.MobileNumber, co.OwnerName, co.FromLocation, co.ToLocation, co.FromShortName, co.ToShortName, co.fromCity, co.toCity, co.sLatLon, co.eLatLon, co.TravelDate, co.TravelTime, co.Seats, co.Distance, co.ExpTripDuration, co.OpenTime, co.CabStatus, co.status, co.RateNotificationSend, co.ExpStartDateTime, co.ExpEndDateTime, co.OwnerChatStatus, co.FareDetails, co.RemainingSeats, 'N' As IsOwner, CONCAT((co.Seats - co.RemainingSeats),'/', co.Seats) as Seat_Status, co.rideType, co.perKmCharge, co.isIntercity, ui.imagename, cr.BookingRefNo, cn.CabName, cr.DriverName, cr.DriverNumber, cr.CarNumber, cr.CarType, pm.PoolId, pm.PoolName, pm.rGid, v.vehicleModel, vd.registrationNumber, vd.isCommercial, ru.socialType, ru.CreatedOn, (SELECT COUNT(*) FROM acceptedrequest WHERE MemberNumber=co.MobileNumber AND hasBoarded=1) as ridestaken, (SELECT COUNT(*) FROM cabopen WHERE MobileNumber=co.MobileNumber AND status !=0) as ridesgiven
    FROM cabopen co
    LEFT JOIN groupCabs gc ON co.CabId = gc.cabId
    LEFT JOIN userpoolsmaster pm ON gc.groupId = pm.PoolId
    JOIN registeredusers ru ON co.MobileNumber = ru.MobileNumber
    LEFT JOIN userprofileimage ui ON co.userId = ui.userId
    LEFT JOIN cmccabrecords cr ON co.CabId = cr.CabId
    LEFT JOIN cabnames cn ON cn.CabNameID = cr.CabNameID
    LEFT JOIN userVehicleDetail vd ON co.MobileNumber = vd.mobileNumber
    LEFT JOIN vehicle v ON v.id = vd.vehicleId
    WHERE NOW() < DATE_ADD(co.ExpStartDateTime, INTERVAL 30 MINUTE)
    AND co.userId !='$userId'
    AND co.status < 1
    AND co.CabStatus ='A'
    AND co.RemainingSeats >0
    AND NOT EXISTS (SELECT 1 FROM cabmembers cm2 WHERE cm2.CabId = co.CabId AND cm2.memberUserId='$userId')
    HAVING origin < ".$proximity."
    AND destination < ".$proximity."
    ORDER BY ExpStartDateTime";

    } else {

        $sql = "SELECT co.CabId, co.MobileNumber, co.OwnerName, co.FromLocation, co.ToLocation, co.FromShortName, co.ToShortName, co.fromCity, co.toCity, co.sLatLon, co.eLatLon, co.TravelDate, co.TravelTime, co.Seats, co.Distance, co.ExpTripDuration, co.OpenTime, co.CabStatus, co.status, co.RateNotificationSend, co.ExpStartDateTime, co.ExpEndDateTime, co.OwnerChatStatus, co.FareDetails, co.RemainingSeats, 'N' As IsOwner, CONCAT((co.Seats - co.RemainingSeats),'/', co.Seats) as Seat_Status, co.rideType, co.isIntercity, co.perKmCharge, ui.imagename, cr.BookingRefNo, cn.CabName, cr.DriverName, cr.DriverNumber, cr.CarNumber, cr.CarType, pm.PoolId, pm.PoolName, pm.rGid, v.vehicleModel, vd.registrationNumber, vd.isCommercial, ru.socialType, ru.CreatedOn, (SELECT COUNT(*) FROM acceptedrequest WHERE MemberNumber=co.MobileNumber AND hasBoarded=1) as ridestaken, (SELECT COUNT(*) FROM cabopen WHERE MobileNumber=co.MobileNumber AND status !=0) as ridesgiven
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
    AND co.userId !='$userId'
    AND co.status < 1
    AND co.CabStatus ='A'
    AND co.fromCity ='".$fromCity."'
    AND co.toCity ='".$toCity."'
    AND co.isIntercity=1
    AND co.RemainingSeats >0
    AND NOT EXISTS (SELECT 1 FROM cabmembers cm2 WHERE cm2.CabId = co.CabId AND cm2.memberUserId='$userId')
    ORDER BY co.ExpStartDateTime";

    }

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

    $i=1000000;

    foreach ($nearbyGroupIds as $id){
        $tempArr = [];
        $tempRides = [];

        foreach($nearbyRides as $ride){

            if ($id == $ride['PoolId']) {
                if ($isIntercity) {
                    $tempArr['id'] = $i;
                    $tempArr['rGid'] = null;
                    $tempArr['name'] = ucfirst($ride['fromCity']) . ' to ' . ucfirst($ride['toCity']);
                } else {
                    $tempArr['id'] = $ride['PoolId'];
                    $tempArr['rGid'] = $ride['rGid'];
                    $tempArr['name'] = $ride['PoolName'];
                }
                
                $tempRides[] = $ride;
            }
            $i++;
        }
        if (!empty($tempRides)) {
            $tempArr['rides'] = $tempRides;
            $publicRides[] = $tempArr;
        }
    }

    $AllRides['privateRides'] = $privateRides;
    $AllRides['publicRides'] = $publicRides;
    $AllRides['myPublicGroupRides'] = $myPublicGroupRides;

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
