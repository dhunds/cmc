<?php
include('connection.php');

$MobileNumber = $_POST['MobileNumber'];

$sql = "SELECT a.*, (SELECT imagename from userprofileimage where MobileNumber = a.SentMemberNumber LIMIT 1) as imagename FROM notifications a WHERE Trim(a.ReceiveMemberNumber) = Trim('$MobileNumber') AND Trim(a.StatusArchieve) = 'No' and RefStatus IS NULL ORDER BY a.DateTime DESC";

$stmt = $con->query($sql);
$no_of_rows = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

if ($no_of_rows > 0) {
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($rows);
} else {
    echo "No Notification !!";
}