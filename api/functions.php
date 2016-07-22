<?php

function setResponse($args){
    $code = $args['code'];
    unset($args['code']);

    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($args);
    exit;
}

function checkPostForBlank($arrParams){
    $error = 0;
    foreach ($arrParams as $value) {
        if (!isset($_POST[$value]) || $_POST[$value] =='') {
            $error = 1;
        }
    }
    return $error;
}

function getAddessModel($address){

    $address = str_replace(" ", "+", $address);

    $json = file_get_contents("http://maps.google.com/maps/api/geocode/json?address=$address&sensor=false");

    $json = json_decode($json);

    return $json;
}

function createShortAddress($address)
{
    $shortAddress = '';

    if ($address != '') {
        $arrAddress = explode(',', $address);
        $addressCount = count($arrAddress);

        if ($addressCount == 4) {
            $shortAddress = trim($arrAddress[0]) . ', ' . trim($arrAddress[1]);
        } else if ($addressCount == 5) {
            $shortAddress = trim($arrAddress[1]) . ', ' . trim($arrAddress[2]);
        } else if ($addressCount == 6) {
            $shortAddress = trim($arrAddress[2]) . ', ' . trim($arrAddress[3]);
        } else {
            $shortAddress = trim($arrAddress[0]) . ', ' . trim($arrAddress[1]);
        }
    }
    return $shortAddress;
}

function rideProximity(){
    global $con;
    $stmt = $con->prepare("select setValue from settings Where setName='SEARCH_RIDE_PROXIMITY'");
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $result[0]['setValue'];
}

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

function perKMChargeIntracity(){
    global $con;
    $stmt = $con->prepare("select setValue from settings Where setName='PER_KM_CHARGE_INTRACITY'");
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $result[0]['setValue'];
}

function getDrivingDistance($lat1, $lat2, $long1, $long2)
{
    $url = "https://maps.googleapis.com/maps/api/distancematrix/json?origins=".$lat1.",".$long1."&destinations=".$lat2.",".$long2."&mode=driving&language=pl-PL";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    $response = curl_exec($ch);
    curl_close($ch);
    $response_a = json_decode($response, true);
    $dist = $response_a['rows'][0]['elements'][0]['distance']['text'];
    $time = $response_a['rows'][0]['elements'][0]['duration']['text'];

    return array('distance' => $dist, 'time' => $time);
}