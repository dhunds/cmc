<?php
include('auth.php');
include('functions.php');

if (isset($_POST['imei']) && $_POST['imei'] != '') {

    $imei = (isset($_POST['imei']))?$_POST['imei']:'';
    $gcmId = (isset($_POST['gcmId']))?$_POST['gcmId']:'';

    $stmt = $con->query("SELECT *  FROM deviceDetails WHERE IMEI='".$_POST['imei']."'");
    $found = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

    if ($found) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        $resp = array('code' => 200, 'status' => 'success', 'message' => '', 'data' => $user);
    } else {
        $sql = "INSERT INTO deviceDetails(deviceId, IMEI, nextCheckDateTime, status,`created`) VALUES  ('$gcmId', $imei, DATE_ADD(now(), INTERVAL 1 MONTH), 1, now())";
        $stmt = $con->prepare($sql);
        $stmt->execute();

        $stmt = $con->query("SELECT *  FROM deviceDetails WHERE IMEI='".$_POST['imei']."'");
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        $resp = array('code' => 200, 'status' => 'success', 'message' => '', 'data' => $user);
    }

} else {
    $resp = array('code' => 200, 'status' => 'fail', 'message' => 'Invalid Params', 'data' => array());
}

setResponse($resp);