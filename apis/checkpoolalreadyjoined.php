<?php
include('connection.php');

$CabId = $_POST['CabId'];
$MemberNumber = $_POST['MemberNumber'];
$MemberUserId = $_POST['memberUserId'];
$rideFare = 'NA';

$stmt = $con->query("SELECT * FROM acceptedrequest WHERE (CabId = '$CabId' AND memberUserId = '$MemberUserId') AND Status != 'Dropped'");
$no_of_rows = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

if ($no_of_rows > 0) {
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($rows[0]['hasBoarded'] !=0){
        $stmt = $con->query("SELECT amountPaidByRider FROM ridePayments WHERE cabId = '$CabId' AND paidByUserId = ".$MemberUserId);
        $found = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

        if ($found) {
            $paymentDetail = $stmt->fetch(PDO::FETCH_ASSOC);
            $rideFare = $paymentDetail['amountPaidByRider'];
        }
    }

    $rows[0]['rideFare'] = $rideFare;

    echo json_encode($rows);
} else {
    echo "fresh pool";
}
$con = null;
