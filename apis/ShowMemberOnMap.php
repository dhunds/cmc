<?php
include('connection.php');

$CabId = $_POST['CabId'];
$MemberNumber = $_POST['MemberNumber'];
$MemberUserId = $_POST['memberUserId'];

$sql = "SELECT ac.*, ru.socialType, ru.CreatedOn, (SELECT COUNT(*) FROM acceptedrequest WHERE MemberNumber=ac.MemberNumber AND hasBoarded !=0) as ridestaken, (SELECT COUNT(*) FROM cabopen WHERE MobileNumber=ac.MemberNumber AND status !=0) as ridesgiven FROM acceptedrequest ac JOIN registeredusers ru ON ac.memberUserId=ru.userId WHERE ac.CabId = '$CabId' AND ac.Status != 'Dropped' AND ac.memberUserId != '$MemberUserId'";

$stmt = $con->query($sql);
$no_of_rows = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

if ($no_of_rows > 0) {
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($rows);
} else {
    echo "No Members joined yet";
}