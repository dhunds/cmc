<?php
include('connection.php');
include('../common.php');

$nearbyRides = array();
$publicGroupRides = array();
$privateRides = array();
$interCityRides = array();
$interCityRideCabIds = array();
$toCities = array();

if (isset($_POST['sLatLon']) && isset($_POST['mobileNumber']) && $_POST['mobileNumber'] != '') {

    $mobileNumber = $_POST['mobileNumber'];
    $userId = $_POST['userId'];

    list($sLat, $sLon) = explode(',', $_POST['sLatLon']);

    $proximity = rideProximity();

    //$fromCity = $_POST['fromCity'];

    $fromCity = getCity($sLat, $sLon);
    $groupCities = getGroupCities($fromCity);

    $sql = "SELECT co.CabId, co.userId, co.MobileNumber, co.OwnerName, co.FromLocation, co.ToLocation, co.FromShortName, co.ToShortName, co.fromCity, co.toCity, co.sLatLon, co.eLatLon, co.TravelDate, co.TravelTime, co.Seats, co.Distance, co.ExpTripDuration, co.OpenTime, co.CabStatus, co.status, co.RateNotificationSend, co.ExpStartDateTime, co.ExpEndDateTime, co.OwnerChatStatus, co.FareDetails, co.RemainingSeats, 'N' As IsOwner, CONCAT((co.Seats - co.RemainingSeats),'/', co.Seats) as Seat_Status, co.rideType, co.perKmCharge, ui.imagename, cr.BookingRefNo, cn.CabName, cr.DriverName, cr.DriverNumber, cr.CarNumber, cr.CarType, v.vehicleModel, vd.registrationNumber, vd.isCommercial, ru.socialType, ru.CreatedOn, (SELECT COUNT(*) FROM acceptedrequest WHERE MemberNumber=co.MobileNumber AND hasBoarded=1) as ridestaken, (SELECT COUNT(*) FROM cabopen WHERE MobileNumber=co.MobileNumber AND status !=0) as ridesgiven
    FROM cabopen co
    JOIN cabmembers cm ON co.CabId = cm.CabId
    JOIN registeredusers ru ON co.userId = ru.userId
    LEFT JOIN userprofileimage ui ON co.MobileNumber = ui.MobileNumber
    LEFT JOIN cmccabrecords cr ON co.CabId = cr.CabId
    LEFT JOIN cabnames cn ON cn.CabNameID = cr.CabNameID
    LEFT JOIN acceptedrequest ar ON cm.CabId = ar.CabId
    LEFT JOIN userVehicleDetail vd ON co.MobileNumber = vd.mobileNumber
    LEFT JOIN vehicle v ON v.id = vd.vehicleId
    WHERE TRIM(cm.memberUserId) = '" . $userId . "'
    AND NOW() < DATE_ADD(co.ExpStartDateTime, INTERVAL 30 MINUTE)
    AND co.userId !='$userId'
    AND co.status < 1
    AND co.CabStatus ='A'
    AND co.RemainingSeats >0
    AND NOT EXISTS (SELECT 1 FROM acceptedrequest ar2 WHERE ar2.CabId = co.CabId AND ar2.memberUserId='$userId')
    ORDER BY co.ExpStartDateTime";

    $stmt = $con->query($sql);
    $found = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

    $PrivateRideCabIds = [];

    if ($found > 0) {
        while ($val = $stmt->fetch(PDO::FETCH_ASSOC)) {

            if (strtolower($val['fromCity']) == strtolower($fromCity) && (!in_array($val['toCity'], $groupCities))) {

                $interCityRides[] = $val;
                $toCities[] = strtolower($val['toCity']);
                $interCityRideCabIds[] = $val['CabId'];
            } else {

                $privateRides[] = $val;
            }

            $PrivateRideCabIds[] = $val['CabId'];
        }
    }

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
            co.CabId, co.userId, co.MobileNumber, co.OwnerName, co.FromLocation, co.ToLocation, co.FromShortName, co.ToShortName, co.fromCity, co.toCity,  co.sLatLon, co.eLatLon, co.TravelDate, co.TravelTime, co.Seats, co.Distance, co.ExpTripDuration, co.OpenTime, co.CabStatus, co.status, co.RateNotificationSend, co.ExpStartDateTime, co.ExpEndDateTime, co.OwnerChatStatus, co.FareDetails, co.RemainingSeats, 'N' As IsOwner, CONCAT((co.Seats - co.RemainingSeats),'/', co.Seats) as Seat_Status, co.rideType, co.perKmCharge, ui.imagename, cr.BookingRefNo, cn.CabName, cr.DriverName, cr.DriverNumber, cr.CarNumber, cr.CarType, pm.PoolId, pm.PoolName, pm.rGid, v.vehicleModel, vd.registrationNumber, vd.isCommercial, ru.socialType, ru.CreatedOn, (SELECT COUNT(*) FROM acceptedrequest WHERE MemberNumber=co.MobileNumber AND hasBoarded=1) as ridestaken, (SELECT COUNT(*) FROM cabopen WHERE MobileNumber=co.MobileNumber AND status !=0) as ridesgiven
    FROM cabopen co
    JOIN groupCabs gc ON co.CabId = gc.cabId
    JOIN userpoolsmaster pm ON gc.groupId = pm.PoolId
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
    AND co.RemainingSeats >0
    AND NOT EXISTS (SELECT 1 FROM cabmembers cm2 WHERE cm2.CabId = co.CabId AND cm2.memberUserId='$userId')
    HAVING origin < ".$proximity."
    ORDER BY co.ExpStartDateTime";

    $stmt = $con->query($sql);
    $found = $con->query("SELECT FOUND_ROWS()")->fetchColumn();
    $nearbyGroupIds = array();
    $publicGroupIds = array();
    $nearbyRideCabIds = array();

    if ($found > 0) {

        while ($rides = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if (!empty($PrivateRideCabIds)) {
                if (!in_array($rides['CabId'], $PrivateRideCabIds)) {
                    $nearbyRides[] = $rides;
                    $nearbyGroupIds[] = $rides['PoolId'];
                }
            } else {
                $nearbyRides[] = $rides;
                $nearbyGroupIds[] = $rides['PoolId'];
            }
            $nearbyRideCabIds[] = $rides['CabId'];
        }
    }
    // End Nearby Rides
    $privateAndNearbyCabIds = array_merge($PrivateRideCabIds, $nearbyRideCabIds);
    $privateAndNearbyCabIds = array_unique($privateAndNearbyCabIds);

    // My Public Group Rides

    $sql = "SELECT co.CabId, co.userId, co.MobileNumber, co.OwnerName, co.FromLocation, co.ToLocation, co.FromShortName, co.ToShortName, co.fromCity, co.toCity, co.sLatLon, co.eLatLon, co.TravelDate, co.TravelTime, co.Seats, co.Distance, co.ExpTripDuration, co.OpenTime, co.CabStatus, co.status, co.RateNotificationSend, co.ExpStartDateTime, co.ExpEndDateTime, co.OwnerChatStatus, co.FareDetails, co.RemainingSeats, 'N' As IsOwner, CONCAT((co.Seats - co.RemainingSeats),'/', co.Seats) as Seat_Status, co.rideType, co.perKmCharge, ui.imagename, cr.BookingRefNo, cn.CabName, cr.DriverName, cr.DriverNumber, cr.CarNumber, cr.CarType, pm.PoolId, pm.PoolName, pm.rGid, v.vehicleModel, vd.registrationNumber, vd.isCommercial, ru.socialType, ru.CreatedOn, (SELECT COUNT(*) FROM acceptedrequest WHERE MemberNumber=co.MobileNumber AND hasBoarded=1) as ridestaken, (SELECT COUNT(*) FROM cabopen WHERE MobileNumber=co.MobileNumber AND status !=0) as ridesgiven
        FROM cabopen co
        JOIN groupCabs gc ON co.CabId = gc.cabId
        JOIN userpoolsmaster pm ON gc.groupId = pm.PoolId
        JOIN registeredusers ru ON co.MobileNumber = ru.MobileNumber
        LEFT JOIN userprofileimage ui ON co.MobileNumber = ui.MobileNumber
        LEFT JOIN cmccabrecords cr ON co.CabId = cr.CabId
        LEFT JOIN cabnames cn ON cn.CabNameID = cr.CabNameID
        LEFT JOIN userVehicleDetail vd ON co.MobileNumber = vd.mobileNumber
        LEFT JOIN vehicle v ON v.id = vd.vehicleId
            WHERE EXISTS (SELECT 1 FROM userpoolsmaster pm1 JOIN userpoolsslave ps1 ON ps1.PoolId = pm1.PoolId WHERE trim(ps1.memberUserId)='" . trim($userId) . "' AND pm1.poolType=2 AND pm1.PoolId = pm.PoolId)
        AND NOW() < DATE_ADD(co.ExpStartDateTime, INTERVAL 30 MINUTE)
        AND co.userId !='$userId'
        AND co.status < 1
        AND co.CabStatus ='A'
        AND co.RemainingSeats >0
        AND NOT EXISTS (SELECT 1 FROM cabmembers cm2 WHERE cm2.CabId = co.CabId AND cm2.memberUserId='$userId')
        ORDER BY co.ExpStartDateTime";

    $stmt = $con->query($sql);
    $found = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

    if ($found > 0) {
        while ($rides = $stmt->fetch(PDO::FETCH_ASSOC)) {

            if (!in_array($rides['PoolId'], $nearbyGroupIds)){
                if (!empty($privateAndNearbyCabIds)) {
                    if (!in_array($rides['CabId'], $PrivateRideCabIds)) {
                        $publicGroupRides[] = $rides;
                        $publicGroupIds[] = $rides['PoolId'];
                    }
                } else {
                    $publicGroupRides[] = $rides;
                    $publicGroupIds[] = $rides['PoolId'];
                }
            }
        }
    }

    // End My public Group Rides

    $publicRides = [];
    $myPublicGroupRides = [];

    $nearbyGroupIds = array_unique($nearbyGroupIds);

    foreach ($nearbyGroupIds as $id){
        $tempArr = [];
        $tempRides = [];

        foreach($nearbyRides as $ride){
            // Check For Intercity Rides

            if (strtolower($ride['fromCity']) == strtolower($fromCity) && (!in_array($ride['toCity'], $groupCities))) {

                $interCityRides[] = $ride;
                $toCities[] = strtolower($ride['toCity']);
                $interCityRideCabIds[] = $ride['CabId'];
            } else {

                if ($id == $ride['PoolId']) {
                    $tempArr['id'] = $ride['PoolId'];
                    $tempArr['rGid'] = $ride['rGid'];
                    $tempArr['name'] = $ride['PoolName'];
                    $ride['isIntercity'] = "0";
                    $tempRides[] = $ride;
                }
            }
        }
        if (!empty($tempRides)) {
            $tempArr['rides'] = $tempRides;
            $publicRides[] = $tempArr;
        }
    }

    $publicGroupIds = array_unique($publicGroupIds);

    foreach ($publicGroupIds as $id){

        $tempArr = [];
        $tempRides = [];

        foreach($publicGroupRides as $ride){

            if (strtolower($ride['fromCity']) == strtolower($fromCity) && (!in_array($ride['toCity'], $groupCities))) {

                $interCityRides[] = $ride;
                $toCities[] = strtolower($ride['toCity']);
                $interCityRideCabIds[] = $ride['CabId'];
            } else {

                if ($id == $ride['PoolId']) {
                    $tempArr['id'] = $ride['PoolId'];
                    $tempArr['rGid'] = $ride['rGid'];
                    $tempArr['name'] = $ride['PoolName'];

                    $tempRides[] = $ride;
                }
            }
        }

        if (!empty($tempRides)) {
            $tempArr['rides'] = $tempRides;
            $myPublicGroupRides[] = $tempArr;
        }
    }

    // Start Intercity Rides
    $condition = '';

    if (!empty($groupCities)) {
        $groupCitiesStr = implode("','", $groupCities);
        $groupCitiesStr = "'".$groupCitiesStr."'";
        $condition = ' AND co.toCity NOT IN ('.$groupCitiesStr.') ';
    } else {
        $condition = " AND co.toCity !='".$fromCity."'";
    }

    $sql = "SELECT  co.CabId, co.userId, co.MobileNumber, co.OwnerName, co.FromLocation, co.ToLocation, co.FromShortName, co.ToShortName, co.fromCity, co.toCity, co.sLatLon, co.eLatLon, co.TravelDate, co.TravelTime, co.Seats, co.Distance, co.ExpTripDuration, co.OpenTime, co.CabStatus, co.status, co.RateNotificationSend, co.ExpStartDateTime, co.ExpEndDateTime, co.OwnerChatStatus, co.FareDetails, co.RemainingSeats, 'N' As IsOwner, CONCAT((co.Seats - co.RemainingSeats),'/', co.Seats) as Seat_Status, co.rideType, co.perKmCharge, ui.imagename, cr.BookingRefNo, cn.CabName, cr.DriverName, cr.DriverNumber, cr.CarNumber, cr.CarType, pm.PoolId, pm.PoolName, pm.rGid, v.vehicleModel, vd.registrationNumber, vd.isCommercial, ru.socialType, ru.CreatedOn, (SELECT COUNT(*) FROM acceptedrequest WHERE MemberNumber=co.MobileNumber AND hasBoarded=1) as ridestaken, (SELECT COUNT(*) FROM cabopen WHERE MobileNumber=co.MobileNumber AND status !=0) as ridesgiven
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
    AND co.fromCity ='$fromCity'
    ".$condition."
    AND co.RemainingSeats >0
    AND NOT EXISTS (SELECT 1 FROM cabmembers cm2 WHERE cm2.CabId = co.CabId AND cm2.memberUserId='$mobileNumber')
    ORDER BY co.ExpStartDateTime";

    $stmt = $con->query($sql);
    $found = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

    if ($found > 0) {
        while ($ride = $stmt->fetch(PDO::FETCH_ASSOC)) {

            if (!in_array($ride['CabId'], $interCityRideCabIds)){
                $interCityRides[] = $ride;
                $toCities[] = strtolower($ride['toCity']);
            }

        }
    }

    $toCities = array_unique($toCities);

    $i=1000000;

    foreach ($toCities as $city){

        $tempArr = [];
        $tempRides = [];

        foreach($interCityRides as $ride) {
            if (strtolower($city) == strtolower($ride['toCity'])) {
                $tempArr['id'] = $i;
                $tempArr['rGid'] = null;
                $tempArr['name'] = ucfirst($fromCity) . ' to ' . ucfirst($city);
                $ride['isIntercity'] = "1";
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
