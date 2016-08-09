<?php
include('connection.php');
include('../common.php');

$nearbyRides = array();
$publicGroupRides = array();
$privateRides = array();
$interCityRides = array();


if (isset($_POST['sLatLon']) && isset($_POST['mobileNumber']) && $_POST['mobileNumber'] != '') {

    $mobileNumber = $_POST['mobileNumber'];
    $proximity = rideProximity();

    list($sLat, $sLon) = explode(',', $_POST['sLatLon']);

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
            co.CabId, co.MobileNumber, co.OwnerName, co.FromLocation, co.ToLocation, co.FromShortName, co.ToShortName, co.fromCity, co.toCity,  co.sLatLon, co.eLatLon, co.TravelDate, co.TravelTime, co.Seats, co.Distance, co.ExpTripDuration, co.OpenTime, co.CabStatus, co.status, co.RateNotificationSend, co.ExpStartDateTime, co.ExpEndDateTime, co.OwnerChatStatus, co.FareDetails, co.RemainingSeats, 'N' As IsOwner, CONCAT((co.Seats - co.RemainingSeats),'/', co.Seats) as Seat_Status, co.rideType, co.perKmCharge, co.isIntercity,  ui.imagename, cr.BookingRefNo, cn.CabName, cr.DriverName, cr.DriverNumber, cr.CarNumber, cr.CarType, pm.PoolId, pm.PoolName, pm.rGid, v.vehicleModel, vd.registrationNumber, vd.isCommercial, ru.socialType, ru.CreatedOn, 'nearby' as resultType
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
    AND co.MobileNumber !='$mobileNumber'
    AND co.status < 1
    AND co.CabStatus ='A'
    AND co.RemainingSeats >0
    AND NOT EXISTS (SELECT 1 FROM cabmembers cm2 WHERE cm2.CabId = co.CabId AND cm2.MemberNumber='$mobileNumber')
    HAVING origin < ".$proximity."
    ";

    $sql = " UNION SELECT '', co.CabId, co.MobileNumber, co.OwnerName, co.FromLocation, co.ToLocation, co.FromShortName, co.ToShortName, co.fromCity, co.toCity, co.sLatLon, co.eLatLon, co.TravelDate, co.TravelTime, co.Seats, co.Distance, co.ExpTripDuration, co.OpenTime, co.CabStatus, co.status, co.RateNotificationSend, co.ExpStartDateTime, co.ExpEndDateTime, co.OwnerChatStatus, co.FareDetails, co.RemainingSeats, 'N' As IsOwner, CONCAT((co.Seats - co.RemainingSeats),'/', co.Seats) as Seat_Status, co.rideType, co.perKmCharge, co.isIntercity, ui.imagename, cr.BookingRefNo, cn.CabName, cr.DriverName, cr.DriverNumber, cr.CarNumber, cr.CarType, '', '', '',  v.vehicleModel, vd.registrationNumber, vd.isCommercial, ru.socialType, ru.CreatedOn, 'private' as resultType
    FROM cabopen co
    JOIN cabmembers cm ON co.CabId = cm.CabId
    JOIN registeredusers ru ON co.MobileNumber = ru.MobileNumber
    LEFT JOIN userprofileimage ui ON co.MobileNumber = ui.MobileNumber
    LEFT JOIN cmccabrecords cr ON co.CabId = cr.CabId
    LEFT JOIN cabnames cn ON cn.CabNameID = cr.CabNameID
    LEFT JOIN acceptedrequest ar ON cm.CabId = ar.CabId
    LEFT JOIN userVehicleDetail vd ON co.MobileNumber = vd.mobileNumber
    LEFT JOIN vehicle v ON v.id = vd.vehicleId
    WHERE TRIM(cm.MemberNumber) = '" . $mobileNumber . "'
    AND NOW() < DATE_ADD(co.ExpStartDateTime, INTERVAL 30 MINUTE)
    AND co.MobileNumber !='$mobileNumber'
    AND co.status < 1
    AND co.CabStatus ='A'
    AND co.RemainingSeats >0
    AND NOT EXISTS (SELECT 1 FROM acceptedrequest ar2 WHERE ar2.CabId = co.CabId AND ar2.MemberNumber='$mobileNumber')
    ";


    $qry = "SELECT pm.PoolId FROM userpoolsmaster pm JOIN userpoolsslave ps ON ps.PoolId = pm.PoolId WHERE trim(ps.MemberNumber)='" . trim($mobileNumber) . "' AND poolType=2";

    $stmt = $con->query($qry);
    $found = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

    if ($found) {
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
            $sql .= " UNION

                SELECT '', co.CabId, co.MobileNumber, co.OwnerName, co.FromLocation, co.ToLocation, co.FromShortName, co.ToShortName, co.fromCity, co.toCity, co.sLatLon, co.eLatLon, co.TravelDate, co.TravelTime, co.Seats, co.Distance, co.ExpTripDuration, co.OpenTime, co.CabStatus, co.status, co.RateNotificationSend, co.ExpStartDateTime, co.ExpEndDateTime, co.OwnerChatStatus, co.FareDetails, co.RemainingSeats, 'N' As IsOwner, CONCAT((co.Seats - co.RemainingSeats),'/', co.Seats) as Seat_Status, co.rideType, co.perKmCharge, co.isIntercity, ui.imagename, cr.BookingRefNo, cn.CabName, cr.DriverName, cr.DriverNumber, cr.CarNumber, cr.CarType, pm.PoolId, pm.PoolName, pm.rGid, v.vehicleModel, vd.registrationNumber, vd.isCommercial, ru.socialType, ru.CreatedOn, 'mygroup' as resultType
                FROM cabopen co
                JOIN groupCabs gc ON co.CabId = gc.cabId
                JOIN userpoolsmaster pm ON gc.groupId = pm.PoolId
                JOIN registeredusers ru ON co.MobileNumber = ru.MobileNumber
                LEFT JOIN userprofileimage ui ON co.MobileNumber = ui.MobileNumber
                LEFT JOIN cmccabrecords cr ON co.CabId = cr.CabId
                LEFT JOIN cabnames cn ON cn.CabNameID = cr.CabNameID
                LEFT JOIN userVehicleDetail vd ON co.MobileNumber = vd.mobileNumber
                LEFT JOIN vehicle v ON v.id = vd.vehicleId
                WHERE gc.groupId IN (" . $myPublicGroups . ")
                AND NOW() < DATE_ADD(co.ExpStartDateTime, INTERVAL 30 MINUTE)
                AND co.MobileNumber !='$mobileNumber'
                AND co.status < 1
                AND co.CabStatus ='A'
                AND co.RemainingSeats >0
                AND NOT EXISTS (SELECT 1 FROM cabmembers cm2 WHERE cm2.CabId = co.CabId AND cm2.MemberNumber='$mobileNumber')
                ";
        }

    }

    $sql .= ' GROUP BY CabId ORDER BY ExpStartDateTime';

    echo $sql;

} else {
    http_response_code(500);
    header('Content-Type: application/json');
    echo '{"status":"failed", "message":"Invalid Params."}';
}