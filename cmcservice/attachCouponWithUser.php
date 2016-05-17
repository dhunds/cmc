<?php
include ('connection.php');
include_once('includes/offers.php');

if (isset($_POST['offerCode']) && $_POST['offerCode'] !='' && isset($_POST['mobileNumber']) && $_POST['mobileNumber'] !='') {
    $attachOffer = attachCouponsToUsers($_POST['offerCode'], $_POST['mobileNumber']);
    http_response_code(500);
    header('Content-Type: application/json');
    echo '{status:"success", message:"'.$attachOffer.'"}';
    exit;
}
} else {
    http_response_code(500);
    header('Content-Type: application/json');
    echo '{status:"fail", message:"Invalid Params"}';
    exit;
}


