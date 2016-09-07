<?php
include('connection.php');
include('includes/functions.php');


if (isset($_POST['payDefault']) && $_POST['payDefault'] !='') {

    $payDefault = $_POST['payDefault'];
    $mobileNumber = $_POST['mobileNumber'];
    $userId = $_POST['userId'];

    $sql = "UPDATE registeredusers set defaultPaymentOption = '$payDefault', defaultPaymentAcceptOption='$payDefault' WHERE userId = '$userId'";
    $stmt = $con->prepare($sql);
    
    if ($stmt->execute()) {
        setResponse(array("code"=>200, "status"=>"success", "message"=>"Default payment Updated"));
    } else {
        setResponse(array("code"=>200, "status"=>"fail", "message"=>"An error occured, please try again."));
    }

} else {
    setResponse(array("code"=>200, "status"=>"fail", "message"=>"Invalid Params"));
}