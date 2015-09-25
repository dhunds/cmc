<?php
include('connection.php');

$MobileNumber = $_POST['MobileNumber'];

if (trim($MobileNumber) == '') {
    echo "ERROR-MobileNumber";
    exit;
}

$sql = "call fetchmypools('$MobileNumber', @totalRows)";
$data = $con->query($sql)->fetchAll(PDO::FETCH_ASSOC);
$total_count = $con->query("select @totalRows;")->fetch(PDO::FETCH_ASSOC);

if ($total_count["@totalRows"] === NULL) {
    $total_count = 0;
}

if ($total_count > 0) {
    $arrFinal = [];
    foreach ($data as $val) {
        $stmt = $con->query("select MemberNumber FROM cabmembers where CabId = '" . $val['CabId'] . "' AND trim(MemberNumber)='" . $MobileNumber . "' AND
settled=1");
        $foundRows = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

        $stmt = $con->query("select MobileNumber FROM cabopen where CabId = '" . $val['CabId'] . "' AND trim(MobileNumber)='" . $MobileNumber . "' AND
settled=1");
        $foundRows1 = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

        if ($foundRows < 1 && $foundRows1 < 1) {
            $arrFinal[] = $val;
        }
    }
    echo json_encode($arrFinal);
} else {
    echo "No Pool Created Yet!!";
}