<?php
include ('connection.php');

if (isset($_POST['cabId']) && $_POST['cabId'] !='' && isset($_POST['mobileNumber']) &&  $_POST['mobileNumber'] !='') {

    $sql = "SELECT MobileNumber, paidBy, fareToPay, totalFare FROM cabopen WHERE CabId = '".$_POST['cabId']."'";
    $stmt = $con->prepare($sql);
    $stmt->execute();
    $cabDetail = $stmt->fetch();
    $resp = [];

    $paidBy = ($cabDetail['paidBy'])?$cabDetail['paidBy']:'';
    $totalFare = ($cabDetail['totalFare'])?$cabDetail['totalFare']:'';

    if ($cabDetail['MobileNumber']==$_POST['mobileNumber']) {
        $fareToPay = ($cabDetail['fareToPay'])?$cabDetail['fareToPay']:'';
    } else {
        $sql = "SELECT fareToPay FROM cabmembers WHERE CabId = '".$_POST['cabId']."' AND trim(MemberNumber)='"
            .$_POST['mobileNumber']."'";
        $stmt = $con->prepare($sql);
        $stmt->execute();
        $memberDetail = $stmt->fetch();
        $fareToPay = ($memberDetail['fareToPay'])?$memberDetail['fareToPay']:'';
    }

    $resp = array('paidBy'=>$paidBy, 'fareToPay'=>$fareToPay, 'totalFare'=>$totalFare);
    echo json_encode($resp);
    exit;
} else {
    http_response_code(500);
    header('Content-Type: application/json');
    echo '{status:"fail", message:"Invalid Params"}';
    exit;
}

