<?php
include ('connection.php');

$MobileNumber = $_REQUEST['MobileNumber'];
$userId = $_POST['userId'];
$lastcabid = $_REQUEST['LastCabId'];

$startLimit = 0;
$resultsToShow = 10;

$sql = "SELECT setValue FROM settings WHERE setName ='ARCHIVEDCABS'";
$stmt = $con->query($sql);
$stmt->execute();
$pageSize = $stmt->fetch(PDO::FETCH_ASSOC);

if ($pageSize['setValue'] > 0)
    $resultsToShow = $pageSize['setValue'];

$sql = "SELECT co.CabId, co.MobileNumber, co.OwnerName, co.FromLocation, co.ToLocation, co.FromShortName, co.ToShortName, co.sLatLon, co.eLatLon, co.TravelDate, co.TravelTime, co.Seats, co.Distance, co.ExpTripDuration, co.OpenTime, co.CabStatus, co.status, co.RateNotificationSend, co.ExpStartDateTime, co.ExpEndDateTime, co.OwnerChatStatus, co.FareDetails, co.RemainingSeats, 'N' As IsOwner, CONCAT((co.Seats - co.RemainingSeats),'/', co.Seats) as Seat_Status, co.rideType, co.perKmCharge, ui.imagename, cr.BookingRefNo, cn.CabName, cr.DriverName, cr.DriverNumber, cr.CarNumber, cr.CarType, ru.socialType, ru.CreatedOn
FROM cabopen co
JOIN cabmembers cm ON co.CabId = cm.CabId
JOIN registeredusers ru ON co.MobileNumber = ru.MobileNumber
JOIN acceptedrequest ar ON cm.CabId = ar.CabId
LEFT JOIN userprofileimage ui ON co.MobileNumber = ui.MobileNumber
LEFT JOIN cmccabrecords cr ON co.CabId = cr.CabId
LEFT JOIN cabnames cn ON cn.CabNameID = cr.CabNameID
WHERE TRIM(cm.memberUserId) = '" . $userId . "'
AND cm.DropStatus !='Yes'

UNION

SELECT co.CabId, co.MobileNumber, co.OwnerName, co.FromLocation, co.ToLocation, co.FromShortName, co.ToShortName, co.sLatLon, co.eLatLon, co.TravelDate, co.TravelTime, co.Seats, co.Distance, co.ExpTripDuration, co.OpenTime, co.CabStatus, co.status, co.RateNotificationSend, co.ExpStartDateTime, co.ExpEndDateTime, co.OwnerChatStatus, co.FareDetails, co.RemainingSeats, 'Y' As IsOwner, CONCAT((co.Seats - co.RemainingSeats),'/', co.Seats) as Seat_Status, co.rideType, co.perKmCharge,
 ui.imagename, cr.BookingRefNo, cn.CabName, cr.DriverName, cr.DriverNumber, cr.CarNumber, cr.CarType, ru.socialType, ru.CreatedOn
FROM cabopen co
JOIN registeredusers ru ON co.MobileNumber = ru.MobileNumber
LEFT JOIN userprofileimage ui ON co.MobileNumber = ui.MobileNumber
LEFT JOIN cmccabrecords cr ON co.CabId = cr.CabId
LEFT JOIN cabnames cn ON cn.CabNameID = cr.CabNameID
WHERE TRIM(co.userId) = '" . $userId . "'
ORDER BY ExpStartDateTime";

$stmt = $con->query($sql);
$stmt->execute();
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
$totalRows = count($data);

if ($lastcabid !='') {
    foreach ($data as $key => $value) {
        if ($value['CabId'] == trim($lastcabid)) {
            $startLimit = $key+1;
            break;
        }
    }
}

if ($totalRows > 0)
{
    $arrFinal = [];
    for ($i =$startLimit; $i < ($startLimit + $resultsToShow); $i++){
        if (isset($data[$i])) {
            $val = $data[$i];

            if ($val['CabStatus'] == 'A') {
                $stmt = $con->query("select MemberNumber FROM cabmembers where CabId = '" . $val['CabId'] . "' AND trim(memberUserId)='" . $userId . "' AND settled=1");
                $foundRows = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

                $stmt = $con->query("select MobileNumber FROM cabopen where CabId = '" . $val['CabId'] . "' AND trim(userId)='" . $userId . "' AND settled=1");
                $foundRows1 = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

                if ($foundRows > 0 || $foundRows1 > 0) {
                    $val['CabStatus'] = 'I';
                    $arrFinal[] = $val;
                }
            } else {
                $arrFinal[] = $val;
            }
        }
    }
    echo json_encode($arrFinal);
}
else
{
    echo "No Pool Created Yet!!";
}
