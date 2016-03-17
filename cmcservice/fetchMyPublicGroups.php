<?php
include ('connection.php');

if (isset($_POST['mobileNumber']) && $_POST['mobileNumber'] !='') {
    $mobileNumber = $_REQUEST['mobileNumber'];

    $sql = "SELECT pm.PoolId, pm.PoolName FROM userpoolsmaster pm JOIN userpoolsslave ps ON ps.PoolId = pm.PoolId WHERE trim(ps.MemberNumber)='".trim($mobileNumber)."'";

    $stmt = $con->query($sql);
    $found = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

    if ($found > 0) {
        $groups = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $resp['status'] = 'success';
        $resp['data'] = $groups;

        http_response_code(200);
        header('Content-Type: application/json');
        echo json_encode($resp);
        exit;

    } else {
        http_response_code(200);
        header('Content-Type: application/json');
        echo '{status:"fail", message:"No Groups Available"}';
        exit;
    }
} else {
    http_response_code(500);
    header('Content-Type: application/json');
    echo '{"status":"failed", "message":"Invalid Params."}';
}