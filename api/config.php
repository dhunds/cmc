<?php

include_once('db.php');

if (isset($_POST['username']) && $_POST['username'] !='' && isset($_POST['password']) && $_POST['password'] !=''){
    //
} else if (isset($_POST['token']) && $_POST['token'] !=''){
    $token = trim($_POST['token']);
    $stmt = $con->query("SELECT id FROM clients WHERE token='$token'");
    $found = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

    if ($found < 1){
        setResponse(array("code"=>200, "status"=>"Error", "message"=>"Bad Request"));
    } else {
        $client = $stmt->fetch();
        $client_id = $client['id'];
    }
} else{
    setResponse(array("code"=>200, "status"=>"Error", "message"=>"Bad Request"));
}


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