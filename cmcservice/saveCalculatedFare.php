<?php
include('connection.php');
include('includes/functions.php');
include_once('classes/class.notification.php');
$objNotification = new Notification();


/**
 * Api Endpoint - saveCalculatedFare.php
 * Short Description  -  Save calculated  fare from app to cabmembers table
 */

if (isset($_POST['numberAndFare']) && $_POST['numberAndFare'] !='') {

    $stmt = $con->query("SELECT ru.FullName FROM cabopen co JOIN registeredusers ru ON co.paidBy=ru.MobileNumber WHERE co.paidBy !='' AND co.CabId='".$_POST['cabId']."'");
    $numRows = $con->query("SELECT FOUND_ROWS()")->fetchColumn();
    $row = $stmt->fetch();

    if ($numRows > 0){
        http_response_code(200);
        header('Content-Type: application/json');
        echo '{status:"fail", message:"Fare already paid by '.$row['FullName'].'"}';
        exit;
    }

    $totalFare = $_POST['totalFare'];
    $paidBy = $_POST['paidBy'];
    $owner = $_POST['owner'];
    $cabId = $_POST['cabId'];
    $numberAndFare = $_POST['numberAndFare'];
    $arrMembersNumberAndFare = explode(',', $numberAndFare);

    $save = save_split_fare($con, $arrMembersNumberAndFare, $totalFare, $cabId, $paidBy, $owner);

    if ($save) {
        http_response_code(200);
        header('Content-Type: application/json');
        echo '{status:"success", message:"Updated sucessfully"}';
        exit;
    } else {
        http_response_code(500);
        header('Content-Type: application/json');
        echo '{status:"failed", message:"An error occured, Please try again after sometime."}';
    }
} else {
    http_response_code(500);
    header('Content-Type: application/json');
    echo '{status:"failed", message:"Invalid Params."}';
}
