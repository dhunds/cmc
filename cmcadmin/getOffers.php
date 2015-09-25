<?php
include('connection.php');

if (isset($_POST['mobileNumber']) && $_POST['mobileNumber'] != '') {

    $sql = "SELECT id, title, description, terms, code, amount, type, maxUse, maxUsePerUser, validFrom, validThru, status FROM offers WHERE status=1";

    $stmt = $con->query($sql);
    $found = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

    if ($found > 0) {
        $finalArray['status'] = 'success';

        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($data as $val) {
            $sql = "SELECT COUNT(id) as totalOfferClaims FROM offerClaims WHERE referralId=".$val['id']." AND mobileNumber='".$_POST['mobileNumber']."'";
            $stmt = $con->query($sql);
            $totalOfferClaims = $stmt->fetchColumn();

            if ($totalOfferClaims >= $val['maxUsePerUser']) {
                $val['alreadyClaimed'] = 1;
            } else {
                $val['alreadyClaimed'] = 0;
            }
            $finalArray['data'][] = $val;
        }

        $finalArray = json_encode($finalArray);

        http_response_code(200);
        header('Content-Type: application/json');
        echo $finalArray;
        exit;
    } else {
        http_response_code(500);
        header('Content-Type: application/json');
        echo '{status:"success", message:"No Offers available"}';
        exit;
    }
} else {
    http_response_code(500);
    header('Content-Type: application/json');
    echo '{status:"fail", message:"Invalid Params"}';
    exit;
}

