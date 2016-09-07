<?php
include('connection.php');

$MobileNumber = $_POST['MobileNumber'];
$userId = $_POST['userId'];

if (trim($MobileNumber) == '' || $userId=='') {
    echo "ERROR-MobileNumber";
    exit;
}

$sql = "SELECT co.CabId, co.userId, co.MobileNumber, co.OwnerName, co.FromLocation, co.ToLocation, co.FromShortName, co.ToShortName, co.sLatLon, co.eLatLon, co.TravelDate, co.TravelTime, co.Seats, co.Distance, co.ExpTripDuration, co.OpenTime, co.CabStatus, co.status, co.RateNotificationSend, co.ExpStartDateTime, co.ExpEndDateTime, co.OwnerChatStatus, co.FareDetails, co.RemainingSeats, 'N' As IsOwner, CONCAT((co.Seats - co.RemainingSeats),'/', co.Seats) as Seat_Status, co.rideType, co.perKmCharge, ui.imagename, cr.BookingRefNo, cn.CabName, cr.DriverName, cr.DriverNumber, cr.CarNumber, cr.CarType, v.vehicleModel, vd.registrationNumber, vd.isCommercial, ru.socialType, ru.CreatedOn
FROM cabopen co
JOIN cabmembers cm ON co.CabId = cm.CabId
JOIN acceptedrequest ar ON cm.CabId = ar.CabId
JOIN registeredusers ru ON co.userId = ru.userId
LEFT JOIN userprofileimage ui ON co.userId = ui.userId
LEFT JOIN cmccabrecords cr ON co.CabId = cr.CabId
LEFT JOIN cabnames cn ON cn.CabNameID = cr.CabNameID
LEFT JOIN userVehicleDetail vd ON co.MobileNumber = vd.mobileNumber
LEFT JOIN vehicle v ON v.id = vd.vehicleId
WHERE TRIM(cm.memberUserId) = '" . $userId . "'
AND ar.memberUserId='" . $userId . "'
AND cm.DropStatus !='Yes'
AND co.CabStatus ='A'

UNION

SELECT co.CabId, co.userId, co.MobileNumber, co.OwnerName, co.FromLocation, co.ToLocation, co.FromShortName, co.ToShortName, co.sLatLon, co.eLatLon, co.TravelDate, co.TravelTime, co.Seats, co.Distance, co.ExpTripDuration, co.OpenTime, co.CabStatus, co.status, co.RateNotificationSend, co.ExpStartDateTime, co.ExpEndDateTime, co.OwnerChatStatus, co.FareDetails, co.RemainingSeats, 'Y' As IsOwner, CONCAT((co.Seats - co.RemainingSeats),'/', co.Seats) as Seat_Status, co.rideType, co.perKmCharge,
 ui.imagename, cr.BookingRefNo, cn.CabName, cr.DriverName, cr.DriverNumber, cr.CarNumber, cr.CarType, v.vehicleModel, vd.registrationNumber, vd.isCommercial, ru.socialType, ru.CreatedOn
FROM cabopen co
JOIN registeredusers ru ON co.userId = ru.userId
LEFT JOIN userprofileimage ui ON co.userId = ui.userId
LEFT JOIN cmccabrecords cr ON co.CabId = cr.CabId
LEFT JOIN cabnames cn ON cn.CabNameID = cr.CabNameID
LEFT JOIN userVehicleDetail vd ON co.MobileNumber = vd.mobileNumber
LEFT JOIN vehicle v ON v.id = vd.vehicleId
WHERE TRIM(co.userId) = '" . $userId . "'
AND co.CabStatus ='A'
ORDER BY ExpStartDateTime
";

$stmt = $con->query($sql);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);


if (count($rows) > 0) {
    echo json_encode($rows);
} else {
    echo "No Pool Created Yet!!";
}
exit;