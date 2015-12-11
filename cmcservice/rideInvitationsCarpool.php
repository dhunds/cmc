<?php
include('connection.php');
$mobileNumber = $_POST['mobileNumber'];

$sql = "SELECT co.CabId, co.MobileNumber, co.OwnerName, co.FromLocation, co.ToLocation, co.FromShortName, co.ToShortName, co.TravelDate, co.TravelTime, co.Seats, co.Distance, co.ExpTripDuration, co.OpenTime, co.CabStatus, co.status, co.RateNotificationSend, co.ExpStartDateTime, co.ExpEndDateTime, co.OwnerChatStatus, co.FareDetails, co.RemainingSeats, 'N' As IsOwner, CONCAT((co.Seats - co.RemainingSeats),'/', co.Seats) as Seat_Status, co.rideType, co.perKmCharge,
 ui.imagename, cr.BookingRefNo, cn.CabName, cr.DriverName, cr.DriverNumber, cr.CarNumber, cr.CarType
FROM cabopen co
JOIN cabmembers cm ON co.CabId = cm.CabId
LEFT JOIN userprofileimage ui ON co.MobileNumber = ui.MobileNumber
LEFT JOIN cmccabrecords cr ON co.CabId = cr.CabId
LEFT JOIN cabnames cn ON cn.CabNameID = cr.CabNameID
LEFT JOIN acceptedrequest ar ON cm.CabId = ar.CabId
WHERE TRIM(cm.MemberNumber) = '" . $mobileNumber . "'
AND co.rideType =1
AND co.status < 2
AND co.CabStatus ='A'
AND co.RemainingSeats >0
AND ar.CabId IS NULL";

$stmt = $con->query($sql);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($rows) > 0) {
    $finalArray['status'] = 'success';
    $finalArray['data'] = $rows;
    http_response_code(200);
    header('Content-Type: application/json');
    echo json_encode($finalArray);
    exit;
} else {
    http_response_code(500);
    header('Content-Type: application/json');
    echo '{status:"fail", message:"No Records Found"}';
    exit;
}
