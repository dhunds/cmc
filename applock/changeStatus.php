<?php
include('auth.php');
include('functions.php');


if (isset($_POST['imei']) && $_POST['imei'] != '' && isset($_POST['status']) && $_POST['status'] != '') {

    $imei = (isset($_POST['imei']))?$_POST['imei']:'';
    $status = (isset($_POST['status']))?$_POST['status']:'';

    $stmt = $con->query("SELECT *  FROM deviceDetails WHERE IMEI='".$_POST['imei']."'");
    $found = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

    if ($found) {
        $sql = "UPDATE deviceDetails SET status=$status WHERE imei='$imei'";
        $stmt = $con->prepare($sql);
        $stmt->execute();
        $resp = array('code' => 200, 'status' => 'success');
    } else {
        
        $resp = array('code' => 200, 'status' => 'fail', 'message' => 'Invalid IMEI ID');
    }

} else {
    $resp = array('code' => 200, 'status' => 'fail', 'message' => 'Invalid Params', 'data' => array());
}

setResponse($resp);