<?php
include('connection.php');
include('../common.php');

$nearbyRides = array();
$privateRides = array();

if (isset($_POST['sLatLon']) && isset($_POST['mobileNumber']) && $_POST['mobileNumber'] != '') {

    $mobileNumber = $_POST['mobileNumber'];
    list($sLat, $sLon) = explode(',', $_POST['sLatLon']);

    $proximity = rideProximity();

    $sql = "SELECT co.CabId, co.MobileNumber, co.OwnerName, co.FromLocation, co.ToLocation, co.FromShortName, co.ToShortName, co.sLatLon, co.eLatLon, co.TravelDate, co.TravelTime, co.Seats, co.Distance, co.ExpTripDuration, co.OpenTime, co.CabStatus, co.status, co.RateNotificationSend, co.ExpStartDateTime, co.ExpEndDateTime, co.OwnerChatStatus, co.FareDetails, co.RemainingSeats, 'N' As IsOwner, CONCAT((co.Seats - co.RemainingSeats),'/', co.Seats) as Seat_Status, co.rideType, co.perKmCharge,
     ui.imagename, cr.BookingRefNo, cn.CabName, cr.DriverName, cr.DriverNumber, cr.CarNumber, cr.CarType, v.vehicleModel, vd.registrationNumber, vd.isCommercial
    FROM cabopen co
    JOIN cabmembers cm ON co.CabId = cm.CabId
    LEFT JOIN userprofileimage ui ON co.MobileNumber = ui.MobileNumber
    LEFT JOIN cmccabrecords cr ON co.CabId = cr.CabId
    LEFT JOIN cabnames cn ON cn.CabNameID = cr.CabNameID
    LEFT JOIN acceptedrequest ar ON cm.CabId = ar.CabId
    LEFT JOIN userVehicleDetail vd ON co.MobileNumber = vd.mobileNumber
    JOIN vehicle v ON v.id = vd.vehicleId
    WHERE TRIM(cm.MemberNumber) = '" . $mobileNumber . "'
    AND NOW() < DATE_ADD(co.ExpStartDateTime, INTERVAL 30 MINUTE)
    AND co.MobileNumber !='$mobileNumber'
    AND co.status < 1
    AND co.CabStatus ='A'
    AND co.RemainingSeats >0
    AND NOT EXISTS (SELECT 1 FROM acceptedrequest ar2 WHERE ar2.CabId = co.CabId AND ar2.MemberNumber='$mobileNumber')
    ORDER BY co.ExpStartDateTime";

    $stmt = $con->query($sql);
    $found = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

    if ($found > 0) {
        $privateRides = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    $PrivateRideCabIds = [];
    foreach ($privateRides as $val) {
        $PrivateRideCabIds[] = $val['CabId'];
    }

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
            co.CabId, co.MobileNumber, co.OwnerName, co.FromLocation, co.ToLocation, co.FromShortName, co.ToShortName, co.sLatLon, co.eLatLon, co.TravelDate, co.TravelTime, co.Seats, co.Distance, co.ExpTripDuration, co.OpenTime, co.CabStatus, co.status, co.RateNotificationSend, co.ExpStartDateTime, co.ExpEndDateTime, co.OwnerChatStatus, co.FareDetails, co.RemainingSeats, 'N' As IsOwner, CONCAT((co.Seats - co.RemainingSeats),'/', co.Seats) as Seat_Status, co.rideType, co.perKmCharge, ui.imagename, cr.BookingRefNo, cn.CabName, cr.DriverName, cr.DriverNumber, cr.CarNumber, cr.CarType, pm.PoolId, pm.PoolName, pm.rGid, v.vehicleModel, vd.registrationNumber, vd.isCommercial
    FROM cabopen co
    JOIN groupCabs gc ON co.CabId = gc.cabId
    JOIN userpoolsmaster pm ON gc.groupId = pm.PoolId
    LEFT JOIN userprofileimage ui ON co.MobileNumber = ui.MobileNumber
    LEFT JOIN cmccabrecords cr ON co.CabId = cr.CabId
    LEFT JOIN cabnames cn ON cn.CabNameID = cr.CabNameID
    LEFT JOIN cabmembers cm ON co.CabId = cm.CabId
    LEFT JOIN userVehicleDetail vd ON co.MobileNumber = vd.mobileNumber
    JOIN vehicle v ON v.id = vd.vehicleId
    WHERE NOW() < DATE_ADD(co.ExpStartDateTime, INTERVAL 30 MINUTE)
    AND co.MobileNumber !='$mobileNumber'
    AND co.status < 1
    AND co.CabStatus ='A'
    AND co.RemainingSeats >0
    AND NOT EXISTS (SELECT 1 FROM cabmembers cm2 WHERE cm2.CabId = co.CabId AND cm2.MemberNumber='$mobileNumber')
    HAVING origin < ".$proximity."
    ORDER BY co.ExpStartDateTime";

    $stmt = $con->query($sql);
    $found = $con->query("SELECT FOUND_ROWS()")->fetchColumn();
    $nearbyGroupIds = array();

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
        }
    }

    $publicRides = [];
    
    $nearbyGroupIds = array_unique($nearbyGroupIds);

    foreach ($nearbyGroupIds as $id){
        $tempArr = [];
        $tempRides = [];

        foreach($nearbyRides as $ride){
            if ($id == $ride['PoolId']) {
                $tempArr['id'] = $ride['PoolId'];
                $tempArr['rGid'] = $ride['rGid'];
                $tempArr['name'] = $ride['PoolName'];
                
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
