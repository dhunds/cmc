<?php
include ('connection.php');

$MobileNumber = $_REQUEST['MobileNumber'];

$startLimit = 0;
$pageSize = 10;

$sql = "SELECT setValue FROM settings WHERE setName ='ARCHIVEDCABS'";
$stmt = $con->query($sql);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if ($row['setValue'] > 0)
    $pageSize = $row['setValue'];

if (isset($_REQUEST['page']) && $_REQUEST['page'] > 0)
    $startLimit = ($_REQUEST['page'] -1) * $pageSize;


$sql = "SELECT co.CabId, co.MobileNumber, co.OwnerName, co.FromLocation, co.ToLocation, co.FromShortName, co.ToShortName, co.sLatLon, co.eLatLon, co.TravelDate, co.TravelTime, co.Seats, co.Distance, co.ExpTripDuration, co.OpenTime, co.CabStatus, co.status, co.RateNotificationSend, co.ExpStartDateTime, co.ExpEndDateTime, co.OwnerChatStatus, co.FareDetails, co.RemainingSeats, 'N' As IsOwner, CONCAT((co.Seats - co.RemainingSeats),'/', co.Seats) as Seat_Status, co.rideType, co.perKmCharge, ui.imagename, cr.BookingRefNo, cn.CabName, cr.DriverName, cr.DriverNumber, cr.CarNumber, cr.CarType, v.vehicleModel, vd.registrationNumber, vd.isCommercial, ru.socialType, ru.CreatedOn
FROM cabopen co
JOIN cabmembers cm ON co.CabId = cm.CabId
JOIN acceptedrequest ar ON cm.CabId = ar.CabId
JOIN registeredusers ru ON co.MobileNumber = ru.MobileNumber
LEFT JOIN userprofileimage ui ON co.MobileNumber = ui.MobileNumber
LEFT JOIN cmccabrecords cr ON co.CabId = cr.CabId
LEFT JOIN cabnames cn ON cn.CabNameID = cr.CabNameID
LEFT JOIN userVehicleDetail vd ON co.MobileNumber = vd.mobileNumber
JOIN vehicle v ON v.id = vd.vehicleId
WHERE TRIM(cm.MemberNumber) = '" . $MobileNumber . "'
AND cm.DropStatus !='Yes'
AND co.ExpStartDateTime >  '2016-05-27 23:59:00'

UNION

SELECT co.CabId, co.MobileNumber, co.OwnerName, co.FromLocation, co.ToLocation, co.FromShortName, co.ToShortName, co.sLatLon, co.eLatLon, co.TravelDate, co.TravelTime, co.Seats, co.Distance, co.ExpTripDuration, co.OpenTime, co.CabStatus, co.status, co.RateNotificationSend, co.ExpStartDateTime, co.ExpEndDateTime, co.OwnerChatStatus, co.FareDetails, co.RemainingSeats, 'Y' As IsOwner, CONCAT((co.Seats - co.RemainingSeats),'/', co.Seats) as Seat_Status, co.rideType, co.perKmCharge,
 ui.imagename, cr.BookingRefNo, cn.CabName, cr.DriverName, cr.DriverNumber, cr.CarNumber, cr.CarType, v.vehicleModel, vd.registrationNumber, vd.isCommercial, ru.socialType, ru.CreatedOn
FROM cabopen co
JOIN registeredusers ru ON co.MobileNumber = ru.MobileNumber
LEFT JOIN userprofileimage ui ON co.MobileNumber = ui.MobileNumber
LEFT JOIN cmccabrecords cr ON co.CabId = cr.CabId
LEFT JOIN cabnames cn ON cn.CabNameID = cr.CabNameID
LEFT JOIN userVehicleDetail vd ON co.MobileNumber = vd.mobileNumber
JOIN vehicle v ON v.id = vd.vehicleId
WHERE TRIM(co.MobileNumber) = '" . $MobileNumber . "'
AND co.ExpStartDateTime >  '2016-05-27 23:59:00'
ORDER BY OpenTime DESC LIMIT $startLimit, $pageSize
";

$stmt = $con->query($sql);
$stmt->execute();
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
$totalRows = count($data);



if ($totalRows > 0)
{
    echo json_encode($data);
}
else
{
    echo "No Pool Created Yet!!";
}
