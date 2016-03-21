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


$sql = "SELECT co.CabId, co.MobileNumber, co.OwnerName, co.FromLocation, co.ToLocation, co.FromShortName, co.ToShortName, co.TravelDate, co.TravelTime, co.Seats, co.Distance, co.ExpTripDuration, co.OpenTime, co.CabStatus, co.status, co.RateNotificationSend, co.ExpStartDateTime, co.ExpEndDateTime, co.OwnerChatStatus, co.FareDetails, co.RemainingSeats, 'N' As IsOwner, CONCAT((co.Seats - co.RemainingSeats),'/', co.Seats) as Seat_Status, co.rideType, co.perKmCharge,
 ui.imagename, cr.BookingRefNo, cn.CabName, cr.DriverName, cr.DriverNumber, cr.CarNumber, cr.CarType
FROM cabopen co
JOIN cabmembers cm ON co.CabId = cm.CabId
JOIN acceptedrequest ar ON cm.CabId = ar.CabId
LEFT JOIN userprofileimage ui ON co.MobileNumber = ui.MobileNumber
LEFT JOIN cmccabrecords cr ON co.CabId = cr.CabId
LEFT JOIN cabnames cn ON cn.CabNameID = cr.CabNameID
WHERE TRIM(cm.MemberNumber) = '" . $MobileNumber . "'
AND cm.DropStatus !='Yes'

UNION

SELECT co.CabId, co.MobileNumber, co.OwnerName, co.FromLocation, co.ToLocation, co.FromShortName, co.ToShortName, co.TravelDate, co.TravelTime, co.Seats, co.Distance, co.ExpTripDuration, co.OpenTime, co.CabStatus, co.status, co.RateNotificationSend, co.ExpStartDateTime, co.ExpEndDateTime, co.OwnerChatStatus, co.FareDetails, co.RemainingSeats, 'Y' As IsOwner, CONCAT((co.Seats - co.RemainingSeats),'/', co.Seats) as Seat_Status, co.rideType, co.perKmCharge,
 ui.imagename, cr.BookingRefNo, cn.CabName, cr.DriverName, cr.DriverNumber, cr.CarNumber, cr.CarType
FROM cabopen co
LEFT JOIN userprofileimage ui ON co.MobileNumber = ui.MobileNumber
LEFT JOIN cmccabrecords cr ON co.CabId = cr.CabId
LEFT JOIN cabnames cn ON cn.CabNameID = cr.CabNameID
WHERE TRIM(co.MobileNumber) = '" . $MobileNumber . "'
ORDER BY OpenTime DESC LIMIT $startLimit, $pageSize
";

$stmt = $con->query($sql);
$stmt->execute();
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
$totalRows = count($data);



if ($totalRows > 0)
{
    $arrFinal = [];
    foreach ($data as $val){
            if ($val['CabStatus'] == 'A') {
                $stmt = $con->query("select MemberNumber FROM cabmembers where CabId = '" . $val['CabId'] . "' AND trim(MemberNumber)='" . $MobileNumber . "' AND settled=1");
                $foundRows = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

                $stmt = $con->query("select MobileNumber FROM cabopen where CabId = '" . $val['CabId'] . "' AND trim(MobileNumber)='" . $MobileNumber . "' AND
    settled=1");
                $foundRows1 = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

                if ($foundRows > 0 || $foundRows1 > 0) {
                    $val['CabStatus'] = 'I';
                    $arrFinal[] = $val;
                }
            } else {
                $arrFinal[] = $val;
            }

    }
    echo json_encode($arrFinal);
}
else
{
    echo "No Pool Created Yet!!";
}