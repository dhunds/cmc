<?php

function createPublicGroups($con, $sLat, $sLon, $eLat, $eLon, $From, $To) {

    $MobileNumber = '00911234567890';

    $groupName = $From . ' to ' . $To;

    $sql = "INSERT INTO userpoolsmaster(OwnerNumber, PoolName, PoolStatus, poolType, startLat, startLon, endLat, endLon, Active) VALUES ('$MobileNumber', '$groupName','OPEN', 2, '$sLat', '$sLon', '$eLat', '$eLon','1')";

    $stmt = $con->prepare($sql);
    $res = $stmt->execute();
    $gId =  $con->lastInsertId();

    if ($gId) {
        $groupName = $To . ' to ' . $From;
        $sql = "INSERT INTO userpoolsmaster(OwnerNumber, PoolName, PoolStatus, poolType, startLat, startLon, endLat, endLon, Active, rGid) VALUES ('$MobileNumber', '$groupName','OPEN', 2, '$eLat', '$eLon', '$sLat', '$sLon','1', $gId)";

        $stmt = $con->prepare($sql);
        $res = $stmt->execute();
        $rGid =  $con->lastInsertId();

        if ($rGid) {
            $sql = "UPDATE userpoolsmaster set rGid = $rGid where PoolId = '$gId'";
            $stmt = $con->prepare($sql);
            $res = $stmt->execute();
        }
    }

    if ($res)
        return $gId;
    else
        return false;
}

function rideProximity(){
    global $con;
    $stmt = $con->prepare("select setValue from settings Where setName='SEARCH_RIDE_PROXIMITY'");
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $result[0]['setValue'];
}

function perKMChargeIntercity(){
    global $con;
    $stmt = $con->prepare("select setValue from settings Where setName='PER_KM_CHARGE_INTERCITY'");
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $result[0]['setValue'];
}

function perKMChargeIntracity(){
    global $con;
    $stmt = $con->prepare("select setValue from settings Where setName='PER_KM_CHARGE_INTRACITY'");
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $result[0]['setValue'];
}