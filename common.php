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

function createPublicGroupsNew($con, $sLat, $sLon, $eLat, $eLon, $groupName) {

    $MobileNumber = '00911234567890';

    $sql = "INSERT INTO userpoolsmaster(OwnerNumber, PoolName, PoolStatus, poolType, startLat, startLon, endLat, endLon, Active) VALUES ('$MobileNumber', '$groupName','OPEN', 2, '$sLat', '$sLon', '$eLat', '$eLon','1')";

    $stmt = $con->prepare($sql);
    $res = $stmt->execute();
    $gId =  $con->lastInsertId();

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

function isIntracityRide($fromCity, $toCity){
    global $con;

    $stmt = $con->query("SELECT City, CityGroup FROM groupcities WHERE City = '$fromCity' OR City = '$toCity'");
    $cityRows = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

    $cities = [];

    if ($cityRows > 0) {
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
            $cities[$row['City']] = $row['CityGroup'];
        }
    }

    if ($cities[$fromCity] == $cities[$toCity]) {
        return true;
    } else {
        return false;
    }
}

function getGroupCities($fromCity){
    global $con;
    $cities = [];

    $stmt = $con->query("SELECT City FROM groupcities WHERE  CityGroup = (SELECT CityGroup FROM groupcities WHERE City='".trim($fromCity)."')");
    $rows = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

    if ($rows > 0) {
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
            $cities[] = $row['City'];
        }
        return $cities;
    }

    return $cities;
}