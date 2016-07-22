<?php

include_once('config.php');

$error = checkPostForBlank (array('mobileNumber', 'name', 'email', 'vehicle', 'registrationNumber', 'from', 'to', 'startTime', 'seats' ));

if (!$error) {
    $_POST['mobileNumber'] = '0091' . substr(trim($_POST['mobileNumber']), -10);

    $stmt = $con->query("SELECT FullName FROM registeredusers WHERE MobileNumber = '".$_POST['mobileNumber']."'");
    $userExists = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

    if ($userExists < 1) {
        $sql = "INSERT INTO registeredusers (FullName, Password, MobileNumber, DeviceToken, Email, Gender, DOB, Platform, PushNotification, LastLoginDateTime, SingleUsePassword, SingleUseExpiry, SingleUseVerified, ResetPasswordOTP, CreatedOn, isAdminType, referralCode, usedReferralCode, totalCredits, defaultPaymentOption, defaultPaymentAcceptOption, type, status, socialId, socialType, mobikwikToken) VALUES ('".$_POST['name']."', '', '".$_POST['mobileNumber']."', '', '', '', '', '', 'off', CURRENT_TIMESTAMP, NULL, NULL, '0', NULL, NULL, '0', '', '', '', '1', '1', '2', '1', '', '', '')";

        $stmt = $con->prepare($sql);
        $stmt->execute();

        $sql = "INSERT INTO  userprofileimage (MobileNumber ,imagename)VALUES ('".$_POST['mobileNumber']."',  '');";
        $stmt = $con->prepare($sql);
        $stmt->execute();

    } else {
        $stmt = $con->query("SELECT id, cleintId FROM cabOwners WHERE mobileNumber = '".$_POST['mobileNumber']."'");
        $userAssociated = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

        if ($userAssociated > 0) {
            $cabOwner = $stmt->fetch();

            if ($cabOwner['clientId'] != $client_id){
                setResponse(array("code"=>200, "status"=>"Error", "message"=>"User is not authorised"));
            }
        } else {
            $sql = "INSERT INTO  cabOwners (`mobileNumber` ,`Name`, `cleintId`)VALUES ('".$_POST['mobileNumber']."',  '".$_POST['name']."', '$client_id');";
            $stmt = $con->prepare($sql);
            $stmt->execute();
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

    $addressModelFrom = getAddessModel($_POST['from']);
    $addressModelTo = getAddessModel($_POST['to']);

    $FromLocation = $addressModelFrom->{'results'}[0]->{'formatted_address'};
    $ToLocation = $addressModelTo->{'results'}[0]->{'formatted_address'};
    $sLat = $addressModelFrom->{'results'}[0]->{'geometry'}->{'location'}->{'lat'};
    $sLon = $addressModelFrom->{'results'}[0]->{'geometry'}->{'location'}->{'lng'};
    $eLat = $addressModelFrom->{'results'}[0]->{'geometry'}->{'location'}->{'lat'};
    $eLon = $addressModelFrom->{'results'}[0]->{'geometry'}->{'location'}->{'lng'};

    $sLatLon = $sLat.','.$sLon;
    $eLatLon = $eLat.','.$eLon;

    $FromShortName =  createShortAddress($FromLocation);
    $ToShortName =  createShortAddress($ToLocation);

    $proximity = rideProximity();
    $CabId = $_POST['mobileNumber'].time();

    // Search Nearby groups

    $sql = "SELECT
                  PoolId,
                  PoolName,
                  (
                    6371 * acos (
                      cos ( radians($sLat) )
                      * cos( radians( startLat ) )
                      * cos( radians( startLon ) - radians($sLon) )
                      + sin ( radians($sLat) )
                      * sin( radians( startLat ) )
                    )
                  ) AS origin,
                  (
                    6371 * acos (
                      cos ( radians($eLat) )
                      * cos( radians( endLat ) )
                      * cos( radians( endLon ) - radians($eLon) )
                      + sin ( radians($eLat) )
                      * sin( radians( endLat ) )
                    )
                  ) AS destination

                FROM userpoolsmaster
                WHERE poolType=2
                HAVING origin < " . $proximity . " AND destination < " . $proximity . "
                ORDER BY origin, destination LIMIT 0,1";

    $stmt = $con->query($sql);
    $found = $con->query("SELECT FOUND_ROWS()")->fetchColumn();
    $createGroup = 0;

    if ($found < 1) {
        $createGroup = createPublicGroups($con, $sLat, $sLon, $eLat, $eLon, $FromShortName, $ToShortName);
        $groupId = $createGroup;

        if ($groupId) {
            // Send Mail to support
            require_once '../cmcservice/mail.php';
            $groupName = $FromShortName . ' to ' . $ToShortName;
            sendGroupCreationMail($groupName);
        }

    } else {
        $nearbyGroup = $stmt->fetch(PDO::FETCH_ASSOC);
        $groupId = $nearbyGroup['PoolId'];
    }

    // Create Ride
    if ($found > 0 || $createGroup) {

        $MobileNumber = '0091' . substr(trim($_POST['mobileNumber']), -10);
        $OwnerName = $_POST['name'];

        list($TravelDate, $TravelTime) = explode(" ", $_POST['startTime']);

        $TravelTime = strtoupper($TravelTime);
        $Seats = $_POST['seats'];
        $RemainingSeats = $_POST['seats'];

        $dist = getDrivingDistance($sLat, $sLon, $eLat, $eLon);
        $Distance = $dist['distance'];
        $ExpTripDuration = $dist['time'];

        $rideType = 4;
        $perKmCharge = perKMChargeIntracity();

        $dateInput = explode('/', $TravelDate);
        $cDate = $dateInput[1] . '/' . $dateInput[0] . '/' . $dateInput[2];

        $expTrip = strtotime($cDate . ' ' . $TravelTime);
        $newdate = $expTrip + $ExpTripDuration;
        $ExpEndDateTime = date('Y-m-d H:i:s', $newdate);

        $startDate = $expTrip;

        $ExpStartDateTime = date('Y-m-d H:i:s', $startDate);

        $TravelTime = date('g:i A', strtotime($TravelTime));

        $sql = "INSERT INTO cabopen(CabId, MobileNumber, OwnerName, FromLocation, ToLocation, FromShortName, ToShortName, sLatLon, eLatLon, sLat, sLon, eLat, eLon, TravelDate, TravelTime, Seats, RemainingSeats, Distance, OpenTime, ExpTripDuration,ExpStartDateTime,ExpEndDateTime,rideType,perKmCharge) VALUES ('$CabId','$MobileNumber','$OwnerName','$FromLocation','$ToLocation','$FromShortName','$ToShortName','$sLatLon','$eLatLon', '$sLat', '$sLon', '$eLat', '$eLon','$TravelDate','$TravelTime','$Seats','$RemainingSeats','$Distance',now(),'$ExpTripDuration', '$ExpStartDateTime','$ExpEndDateTime','$rideType','$perKmCharge')";
        //echo $sql;die;
        $stmt = $con->prepare($sql);
        $res = $stmt->execute();

        $sql = "INSERT INTO groupCabs(groupId, cabId) VALUES ($groupId, '$CabId')";

        $stmt = $con->prepare($sql);
        $res = $stmt->execute();

        if ($res) {
            setResponse(array("code"=>200, "status"=>"Success", "cabId"=>$CabId));
        } else {
            setResponse(array("code"=>200, "status"=>"Error", "message"=>"An Error occured, Please try later."));
        }
    } else {
        setResponse(array("code"=>200, "status"=>"Error", "message"=>"An Error occured, Please try later."));
    }

} else {
    setResponse(array("code"=>200, "status"=>"Error", "message"=>"One or more parameter is missing."));
}