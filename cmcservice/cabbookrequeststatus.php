<?php
include('connection.php');

$requestID = $_REQUEST['requestid'];

$stmt = $con->query("SELECT * FROM cabbookingrequest where requestID='$requestID'");
$no_of_rows = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

if ($no_of_rows > 0) {
    $rows = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode($rows);
}
