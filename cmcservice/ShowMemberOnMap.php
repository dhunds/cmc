<?php
include('connection.php');

$CabId = $_POST['CabId'];
$MemberNumber = $_POST['MemberNumber'];

$sql = "SELECT ac.*, ru.socialType, ru.CreatedOn FROM acceptedrequest ac JOIN registeredusers ru ON ac.MemberNumber=ru.MobileNumber WHERE ac.CabId = '$CabId' AND ac.Status != 'Dropped' AND ac.MemberNumber != '$MemberNumber'";

$stmt = $con->query($sql);
$no_of_rows = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

if ($no_of_rows > 0) {
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($rows);
} else {
    echo "No Members joined yet";
}