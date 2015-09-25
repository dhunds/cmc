<?php
include('connection.php');
include('includes/functions.php');

$sql = "SELECT MobileNumber FROM registeredusers";
$stmt = $con->query($sql);
$i=0;
while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $code = randomString();

    if (isReferralCodeUnique($code)) {
        $sql = "UPDATE registeredusers SET referralCode='" . $code . "' WHERE MobileNumber='" . $data['MobileNumber'] . "'";
        $nStmt = $con->prepare($sql);
        $nStmt->execute();
    } else {
        $i++;
    }
}

echo $i . ' referralcode found duplicate';

