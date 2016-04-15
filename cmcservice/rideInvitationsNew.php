<?php
include('connection.php');

$nearbyRides = array();
$publicGroupRides = array();
$privateRides = array();

if (isset($_POST['sLatLon']) && isset($_POST['mobileNumber']) && $_POST['mobileNumber'] != '') {

    $mobileNumber = $_POST['mobileNumber'];
    list($sLat, $sLon) = explode(',', $_POST['sLatLon']);

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
          ) AS origin

        FROM userpoolsmaster
        WHERE poolType=2
        HAVING origin < ".SEARCH_RIDE_PROXIMITY."
        ORDER BY origin";

    $stmt = $con->query($sql);
    $found = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

    if ($found > 0) {
        $groups = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $nearbyGroupIds = array();

        foreach ($groups as $val) {
            $nearbyGroupIds[] = $val['PoolId'];
        }
        $nearbyPublicGroups = implode(',', $nearbyGroupIds);

        $sql = "SELECT co.CabId, co.MobileNumber, co.OwnerName, co.FromLocation, co.ToLocation, co.FromShortName, co.ToShortName, co.TravelDate, co.TravelTime, co.Seats, co.Distance, co.ExpTripDuration, co.OpenTime, co.CabStatus, co.status, co.RateNotificationSend, co.ExpStartDateTime, co.ExpEndDateTime, co.OwnerChatStatus, co.FareDetails, co.RemainingSeats, 'N' As IsOwner, CONCAT((co.Seats - co.RemainingSeats),'/', co.Seats) as Seat_Status, co.rideType, co.perKmCharge, ui.imagename, cr.BookingRefNo, cn.CabName, cr.DriverName, cr.DriverNumber, cr.CarNumber, cr.CarType,
  pm.PoolId, pm.PoolName, pm.rGid
    FROM cabopen co
    JOIN groupCabs gc ON co.CabId = gc.cabId
    JOIN userpoolsmaster pm ON gc.groupId = pm.PoolId
    LEFT JOIN userprofileimage ui ON co.MobileNumber = ui.MobileNumber
    LEFT JOIN cmccabrecords cr ON co.CabId = cr.CabId
    LEFT JOIN cabnames cn ON cn.CabNameID = cr.CabNameID
    LEFT JOIN acceptedrequest ar ON co.CabId = ar.CabId
    WHERE gc.groupId IN (" . $nearbyPublicGroups . ")
    AND NOW() < DATE_ADD(co.ExpEndDateTime, INTERVAL 1 HOUR)
    AND co.MobileNumber !='$mobileNumber'
    AND co.status < 2
    AND co.CabStatus ='A'
    AND co.RemainingSeats >0
    AND ar.CabId IS NULL";

        
        $stmt = $con->query($sql);
        $found = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

        if ($found > 0) {
            $nearbyRides = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }

    $sql = "SELECT pm.PoolId, pm.PoolName FROM userpoolsmaster pm JOIN userpoolsslave ps ON ps.PoolId = pm.PoolId WHERE trim(ps.MemberNumber)='" . trim($mobileNumber) . "' AND poolType=2";

    $stmt = $con->query($sql);
    $found = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

    if ($found > 0) {
        $groups = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $myGroupIds = array();

        foreach ($groups as $val) {

            if (!empty($nearbyGroupIds)) {
                if (!in_array($val['PoolId'], $nearbyGroupIds)) {
                    $myGroupIds[] = $val['PoolId'];
                }
            } else {
                $myGroupIds[] = $val['PoolId'];
            }
        }
        $myPublicGroups = implode(',', $myGroupIds);

        if (!empty($myGroupIds)) {

            $sql = "SELECT co.CabId, co.MobileNumber, co.OwnerName, co.FromLocation, co.ToLocation, co.FromShortName, co.ToShortName, co.TravelDate, co.TravelTime, co.Seats, co.Distance, co.ExpTripDuration, co.OpenTime, co.CabStatus, co.status, co.RateNotificationSend, co.ExpStartDateTime, co.ExpEndDateTime, co.OwnerChatStatus, co.FareDetails, co.RemainingSeats, 'N' As IsOwner, CONCAT((co.Seats - co.RemainingSeats),'/', co.Seats) as Seat_Status, co.rideType, co.perKmCharge, ui.imagename, cr.BookingRefNo, cn.CabName, cr.DriverName, cr.DriverNumber, cr.CarNumber, cr.CarType,
  pm.PoolId, pm.PoolName, pm.rGid
    FROM cabopen co
    JOIN groupCabs gc ON co.CabId = gc.cabId
    JOIN userpoolsmaster pm ON gc.groupId = pm.PoolId
    LEFT JOIN userprofileimage ui ON co.MobileNumber = ui.MobileNumber
    LEFT JOIN cmccabrecords cr ON co.CabId = cr.CabId
    LEFT JOIN cabnames cn ON cn.CabNameID = cr.CabNameID
    LEFT JOIN acceptedrequest ar ON co.CabId = ar.CabId
    WHERE gc.groupId IN (" . $nearbyPublicGroups . ")
    AND co.MobileNumber !='$mobileNumber'
    AND NOW() < DATE_ADD(co.ExpEndDateTime, INTERVAL 1 HOUR)
    AND co.status < 2
    AND co.CabStatus ='A'
    AND co.RemainingSeats >0
    AND ar.CabId IS NULL";

            $stmt = $con->query($sql);
            $found = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

            if ($found > 0) {
                $publicGroupRides = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
        }
    }

    $sql = "SELECT co.CabId, co.MobileNumber, co.OwnerName, co.FromLocation, co.ToLocation, co.FromShortName, co.ToShortName, co.TravelDate, co.TravelTime, co.Seats, co.Distance, co.ExpTripDuration, co.OpenTime, co.CabStatus, co.status, co.RateNotificationSend, co.ExpStartDateTime, co.ExpEndDateTime, co.OwnerChatStatus, co.FareDetails, co.RemainingSeats, 'N' As IsOwner, CONCAT((co.Seats - co.RemainingSeats),'/', co.Seats) as Seat_Status, co.rideType, co.perKmCharge,
     ui.imagename, cr.BookingRefNo, cn.CabName, cr.DriverName, cr.DriverNumber, cr.CarNumber, cr.CarType
    FROM cabopen co
    JOIN cabmembers cm ON co.CabId = cm.CabId
    LEFT JOIN userprofileimage ui ON co.MobileNumber = ui.MobileNumber
    LEFT JOIN cmccabrecords cr ON co.CabId = cr.CabId
    LEFT JOIN cabnames cn ON cn.CabNameID = cr.CabNameID
    LEFT JOIN acceptedrequest ar ON cm.CabId = ar.CabId
    WHERE TRIM(cm.MemberNumber) = '" . $mobileNumber . "'
    AND NOW() < DATE_ADD(co.ExpEndDateTime, INTERVAL 1 HOUR)
    AND co.MobileNumber !='$mobileNumber'
    AND co.status < 2
    AND co.CabStatus ='A'
    AND co.RemainingSeats >0
    AND ar.CabId IS NULL";

    $stmt = $con->query($sql);
    $found = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

    if ($found > 0) {
        $privateRides = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    $publicRides = [];

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

    foreach ($myGroupIds as $id){
        
        $tempArr = [];
        $tempRides = [];
        
        foreach($publicGroupRides as $ride){
            
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