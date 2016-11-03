<?php
include('auth.php');
include('functions.php');

if (isset($_REQUEST['id']) && $_REQUEST['id'] != '') {

    $sql = "SELECT *  FROM deviceDetails WHERE id=".$_REQUEST['id'];

    $stmt = $con->query($sql);
    $found = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

    if ($found) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user['deviceId']){
            $result = sendFCMNotification($user, $user['deviceId']);
            echo $result;
        } else {
            echo 'Device id not available';
        }

    } else {
        echo 'Device not found';
    }

} else {
    echo 'Invalid Params';
}