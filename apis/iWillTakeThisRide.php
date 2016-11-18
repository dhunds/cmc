<?php
include('connection.php');
include('../common.php');

$AllRides = [];

if (isset($_POST['cabId']) && isset($_POST['cabId']) && isset($_POST['userId']) && $_POST['userId'] != '') {

    $userId = $_POST['userId'];
    $cabId = $_POST['cabId'];

    $sql = "SELECT userId, FullName, MobileNumber  FROM registeredusers WHERE userId=".$userId;
    $stmt = $con->query($sql);
    $found = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

    if ($found) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        $sql = "SELECT id FROM cabopen WHERE userId=".$userId." AND CabStatus='A'";
        $stmt = $con->query($sql);
        $found = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

        if ($found) {
            http_response_code(200);
            header('Content-Type: application/json');
            echo '{"status":"fail", "message":"You already have an active ride"}';
            exit;
        } else {
            $sql = "UPDATE cabopen SET userId=".$userId.", MobileNumber='".$user['MobileNumber']."', OwnerName ='".$user['FullName']."' WHERE CabId='".$cabId."'";
            $stmt = $con->prepare($sql);

            if ($stmt->execute()) {
                http_response_code(200);
                header('Content-Type: application/json');
                echo '{"status":"success", "message":"success"}';
                exit;
            } else {
                http_response_code(500);
                header('Content-Type: application/json');
                echo '{"status":"fail", "message":"An error occured."}';
                exit;
            }
        }

    } else {
        http_response_code(200);
        header('Content-Type: application/json');
        echo '{"status":"fail", "message":"Invalid User"}';
        exit;
    }
} else {
    http_response_code(500);
    header('Content-Type: application/json');
    echo '{"status":"failed", "message":"Invalid Params."}';
}