<?php
include('connection.php');

$CabId = $_POST['CabId'];
$MemberNumber = $_POST['MemberNumber'];
$MemberUserId = $_POST['MemberUserId'];

$stmt = $con->query("SELECT * FROM acceptedrequest WHERE (CabId = '$CabId' AND memberUserId = '$MemberUserId') AND Status != 'Dropped'");
$no_of_rows = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

if ($no_of_rows > 0) {
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($rows);
} else {
    echo "fresh pool";
}
$con = null;
?>