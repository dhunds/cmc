<?php

include_once('config.php');

$error = checkPostForBlank (array('mobileNumber', 'name', 'email', 'vehicle', 'registrationNumber', 'from', 'to', 'startTime', 'seats' ));

if (!$error) {
    $stmt = $con->query("SELECT FullName FROM registeredusers WHERE MobileNumber = '".$_POST['mobileNumber']."'");
    $userExists = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

    if ($userExists < 1) {
        $sql = "INSERT INTO registeredusers (FullName, Password, MobileNumber, DeviceToken, Email, Gender, DOB, Platform, PushNotification, LastLoginDateTime, SingleUsePassword, SingleUseExpiry, SingleUseVerified, ResetPasswordOTP, CreatedOn, isAdminType, referralCode, usedReferralCode, totalCredits, defaultPaymentOption, defaultPaymentAcceptOption, type, status, socialId, socialType, mobikwikToken) VALUES ('".$_POST['name']."', '', '".$_POST['mobileNumber']."', '', '', '', '', '', 'off', CURRENT_TIMESTAMP, NULL, NULL, '0', NULL, NULL, '0', '', '', '', '1', '1', '2', '1', '', '', '')";

        $stmt = $con->prepare($sql);
        $stmt->execute();

        $sql = "INSERT INTO  clubmycab.userprofileimage (MobileNumber ,imagename)VALUES ('".$_POST['mobileNumber']."',  '');";
        $stmt = $con->prepare($sql);
        $stmt->execute();

    } else {
        $stmt = $con->query("SELECT id FROM cabOwners WHERE mobileNumber = '".$_POST['mobileNumber']."' AND cleintId='$client_id'");
        $userAssociated = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

        if ($userAssociated < 1) {
            // Need clarification
            setResponse(array("code"=>200, "status"=>"Error", "message"=>"User is not authorised"));
        }
    }

    $stmt = $con->query("SELECT id FROM vehicle WHERE vehicleModel LIKE '%".$_POST['vehicle']."%'");
    $isValidVehicle = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

    if ($isValidVehicle < 1) {
        setResponse(array("code"=>200, "status"=>"Error", "message"=>"Invalid Vehicle name"));
    } else {
        $vehicle = $stmt->fetch();
        $vehicleId = $vehicle['id'];
    }

    $stmt = $con->query("SELECT id FROM userVehicleDetail WHERE mobileNumber = '".$_POST['mobileNumber']."'");
    $isVehicleAttached = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

    if ($isVehicleAttached < 1) {
        $stmt = $con->prepare("INSERT INTO userVehicleDetail SET vehicleId = '$vehicleId',  isCommercial=1, registrationNumber='".$_POST['registrationNumber']."', mobileNumber = '".$_POST['mobileNumber']."', created=now()");
        $stmt->execute();
    } else {
        $stmt = $con->prepare("UPDATE userVehicleDetail SET vehicleId = '$vehicleId',  isCommercial=1, registrationNumber='".$_POST['registrationNumber']."' WHERE mobileNumber = '".$_POST['mobileNumber']."'");
        $stmt->execute();
    }

    $slatlon = get_lat_long($_POST['from']);
    list($slat, $slong) = explode($slatlon, ',');

    $elatlon = get_lat_long($_POST['to']);
    list($elat, $elong) = explode($elatlon, ',');
    //create ride

} else {
    setResponse(array("code"=>200, "status"=>"Error", "message"=>"One or more parameters is missing."));
}