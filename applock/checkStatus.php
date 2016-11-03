<?php
include('auth.php');
include('functions.php');

if (isset($_POST['imei1']) && $_POST['imei1'] != '') {

    $imei1 = (isset($_POST['imei1']))?$_POST['imei1']:'';
    $imei2 = (isset($_POST['imei2']))?$_POST['imei2']:'';

    $gcmId = (isset($_POST['gcmId']))?$_POST['gcmId']:'';

    $sql = "SELECT *  FROM deviceDetails WHERE IMEI1='".$_POST['imei1']."' OR IMEI2='".$_POST['imei1']."'";

    if (isset($_POST['imei2']) && $_POST['imei2'] !='') {
        $sql .= " OR IMEI1='".$_POST['imei2']."' OR IMEI2='".$_POST['imei2']."'";
    }
    $stmt = $con->query($sql);
    $found = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

    if ($found) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        $sql1 = "UPDATE deviceDetails SET deviceId='$gcmId' WHERE IMEI1='".$_POST['imei1']."' OR IMEI2='".$_POST['imei1']."'";

        if (isset($_POST['imei2']) && $_POST['imei2'] !='') {
            $sql1 .= " OR IMEI1='".$_POST['imei2']."' OR IMEI2='".$_POST['imei2']."'";
        }

        $stmt = $con->prepare($sql1);
        $stmt->execute();

        $stmt = $con->query($sql);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        $resp = array('code' => 200, 'status' => 'success', 'message' => '', 'data' => $user);
    } else {
        $sql1 = "INSERT INTO deviceDetails(deviceId, IMEI1, IMEI2, nextCheckDateTime, status,`created`) VALUES  ('$gcmId', $imei1, $imei2, DATE_ADD(now(), INTERVAL 1 MONTH), 1, now())";
        $stmt = $con->prepare($sql1);
        $stmt->execute();

        $stmt = $con->query($sql);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        $resp = array('code' => 200, 'status' => 'success', 'message' => '', 'data' => $user);
    }

} else {
    $resp = array('code' => 200, 'status' => 'fail', 'message' => 'Invalid Params', 'data' => array());
}

setResponse($resp);