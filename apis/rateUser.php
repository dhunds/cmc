<?php
include ('connection.php');
include_once('includes/functions.php');

if (isset($_POST['ownerNumber']) && $_POST['ownerNumber'] !='' && isset($_POST['memberNumber']) && $_POST['memberNumber'] !='' && isset($_POST['rating']) && $_POST['rating'] !='' && isset($_POST['cabId']) && $_POST['cabId'] !='') {
    $memberUserId = $_POST['memberUserId'];
    $ownerUserId = $_POST['ownerUserId'];

    $sql = "SELECT raing FROM userRating WHERE ownerUserId='$ownerUserId' AND memberUserId = '$memberUserId' AND cabId='" . $_POST['cabId'] . "'";

    $stmt = $con->query($sql);
    $found = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

    if (!$found)
    {
        $sql = "INSERT INTO userRating (ownerUserId, memberUserId, ownerNumber,memberNumber, cabId, rating, reason, created) VALUES ($ownerUserId, $memberUserId, '".$_POST['ownerNumber']."','".$_POST['memberNumber']."', '".$_POST['cabId']."', ".$_POST['rating'].", '".$_POST['reason']."', now())";

    }

    $nStmt = $con->prepare($sql);

    if ($nStmt->execute()){

        $sql = "UPDATE notifications set StatusArchieve = 'Yes' where NotificationId = '" . $_POST['notificationId'] . "'";
        $stmt = $con->prepare($sql);
        $res = $stmt->execute();

        http_response_code(200);
        header('Content-Type: application/json');
        echo '{"status":"success", "message":"User Rated Successfully."}';
        exit;
    } else {
        http_response_code(200);
        header('Content-Type: application/json');
        echo '{"status":"failed", "message":"An error occured, Please try again after sometime."}';
        exit;
    }
} else {
    http_response_code(200);
    header('Content-Type: application/json');
    echo '{"status":"failed", "message":"Invalid Params."}';
    exit;
}
