<?php

include_once('db.php');

$address = getAddessModel('Sikanderpur, DLF Phase 2, Gurgaon, Haryana');
echo createShortAddress($address->{'results'}[0]->{'formatted_address'});

die;

//    $lat = $json->{'results'}[0]->{'geometry'}->{'location'}->{'lat'};
//    $long = $json->{'results'}[0]->{'geometry'}->{'location'}->{'lng'};
//    return $lat.','.$long;

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