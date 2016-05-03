<?php
include('connection.php');

$MobileNumber = $_POST['MobileNumber'];

if (trim($MobileNumber) == '') {
    echo "ERROR-MobileNumber";
    exit;
}

$sql = "SELECT co.CabId, co.MobileNumber, co.OwnerName, co.FromLocation, co.ToLocation, co.FromShortName, co.ToShortName, co.TravelDate, co.TravelTime, co.Seats, co.Distance, co.ExpTripDuration, co.OpenTime, co.CabStatus, co.status, co.RateNotificationSend, co.ExpStartDateTime, co.ExpEndDateTime, co.OwnerChatStatus, co.FareDetails, co.RemainingSeats, 'N' As IsOwner, CONCAT((co.Seats - co.RemainingSeats),'/', co.Seats) as Seat_Status, co.rideType, co.perKmCharge,
 ui.imagename, cr.BookingRefNo, cn.CabName, cr.DriverName, cr.DriverNumber, cr.CarNumber, cr.CarType, v.vehicleModel, vd.registrationNumber, vd.isCommercial
FROM cabopen co
JOIN cabmembers cm ON co.CabId = cm.CabId
JOIN acceptedrequest ar ON cm.CabId = ar.CabId
LEFT JOIN userprofileimage ui ON co.MobileNumber = ui.MobileNumber
LEFT JOIN cmccabrecords cr ON co.CabId = cr.CabId
LEFT JOIN cabnames cn ON cn.CabNameID = cr.CabNameID
LEFT JOIN userVehicleDetail vd ON co.MobileNumber = vd.mobileNumber
JOIN vehicle v ON v.id = vd.vehicleId
WHERE TRIM(cm.MemberNumber) = '" . $MobileNumber . "'
AND ar.MemberNumber='" . $MobileNumber . "'
AND cm.DropStatus !='Yes'
AND cm.settled !=1
AND co.CabStatus ='A'

UNION

SELECT co.CabId, co.MobileNumber, co.OwnerName, co.FromLocation, co.ToLocation, co.FromShortName, co.ToShortName, co.TravelDate, co.TravelTime, co.Seats, co.Distance, co.ExpTripDuration, co.OpenTime, co.CabStatus, co.status, co.RateNotificationSend, co.ExpStartDateTime, co.ExpEndDateTime, co.OwnerChatStatus, co.FareDetails, co.RemainingSeats, 'Y' As IsOwner, CONCAT((co.Seats - co.RemainingSeats),'/', co.Seats) as Seat_Status, co.rideType, co.perKmCharge,
 ui.imagename, cr.BookingRefNo, cn.CabName, cr.DriverName, cr.DriverNumber, cr.CarNumber, cr.CarType, v.vehicleModel, vd.registrationNumber, vd.isCommercial
FROM cabopen co
LEFT JOIN userprofileimage ui ON co.MobileNumber = ui.MobileNumber
LEFT JOIN cmccabrecords cr ON co.CabId = cr.CabId
LEFT JOIN cabnames cn ON cn.CabNameID = cr.CabNameID
LEFT JOIN userVehicleDetail vd ON co.MobileNumber = vd.mobileNumber
JOIN vehicle v ON v.id = vd.vehicleId
WHERE TRIM(co.MobileNumber) = '" . $MobileNumber . "'
AND co.settled !=1
AND co.CabStatus ='A'
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