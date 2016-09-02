<?php
include('connection.php');

$resp = array('header' => 500, 'status' => 'fail', 'message' => '', 'data' => array());

if (isset($_POST['mobileNumber']) && $_POST['mobileNumber'] != '') {

    $userId = $_POST['userId'];

    $sql = "SELECT id, title, description, terms, code, amount, type, maxUse, maxUsePerUser, validFrom, validThru, status  FROM offers WHERE status=1 AND validThru > NOW()";
    $stmt = $con->query($sql);
    $found = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

    if ($found > 0) {

        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $finalArray = array();

        foreach ($data as $val) {

            $sql = "SELECT COALESCE(SUM(credits),0) as credits, COUNT(id) as useCount FROM credits WHERE offerId=" . $val['id'] . " AND userId='" . $_POST['userId'] . "'";
            $stmt = $con->query($sql);
            $credit = $stmt->fetch(PDO::FETCH_ASSOC);

            $val['useCount'] = $credit['useCount'];
            $val['credits'] = $credit['credits'];

            if ($val['type']=='referral'){
                $msg = "You have earned ".$val['credits']." Reward Points for inviting ".$credit['useCount']." friend(s). You can earn referral bonus for maximum ".$val['maxUsePerUser']." invitations";
            } else if($val['type']=='ridecompletion') {
                if ($credit['useCount']){
                    $msg = "You have earned ".$val['credits']." Reward Points for completing your first ride.";
                } else {
                    $msg = "Your have not completed your first ride yet. Complete your ride to earn ".$val['amount']." Reward Points.";
                }
            }else if($val['type']=='offercarpoolride'){
                if ($credit['useCount']){
                    $msg = "You have earned ".$val['credits']." Reward Points for offering ride in your car.";
                } else {
                    $msg = "Your have not offered any ride in your car yet. Offer a ride to earn ".$val['amount']." Reward Points.";
                }
            }

            $val['UserOfferStatus'] = $msg;

            $finalArray[] = $val;
        }

        $resp = array('header' => 200, 'status' => 'success', 'message' => '', 'data' => $finalArray);

    } else {
        $resp = array('header' => 200, 'status' => 'fail', 'message' => 'No Offers available', 'data' => array());
    }
} else {
    $resp = array('header' => 200, 'status' => 'fail', 'message' => 'Invalid Params', 'data' => array());
}

$header = $resp['header'];
unset($resp['header']);

http_response_code($header);
header('Content-Type: application/json');
echo json_encode($resp);
exit;