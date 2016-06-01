<?php

require('OAuth2/Client.php');
require('OAuth2/GrantType/IGrantType.php');
require('OAuth2/GrantType/AuthorizationCode.php');

$sType = $_GET['type'];

const CLIENT_ID = '-Q9kL1722l5XeCTUK5vS8YHiY0mJUijo';
const SERVER_TOKEN = 'l4534hMQeJg5JREbbhRfOHJuM9-UbwFKPfrYpwAi';
const CLIENT_SECRET = '02UIbwOsXX1FXGPdOyU_95jjuW60YBKJAJd4seBX';

const AUTHORIZATION_ENDPOINT = 'https://login.uber.com/oauth/authorize';
const TOKEN_ENDPOINT = 'https://login.uber.com/oauth/token';
const REDIRECT_URI = 'https://ishareryde.com/cmcservice/uberConnect.php';

const BASE_URL = 'https://api.uber.com/v1/';
const ENDPOINT = 'products';
const PRICEENDPOINT = 'estimates/price';
const TIMEENDPOINT = 'estimates/time';
const BOOKENDPOINT = 'requests';


//const SANDBOXBASE_URL = 'https://sandbox-api.uber.com/v1/';
//const SANDBOXBOOKENDPOINT = 'requests';

if ($sType != '') {
    if ($sType == 'products') {
        $lat = $_GET['lat'];
        $lon = $_GET['lon'];
        $fields = array(
            'latitude' => $lat,
            'longitude' => $lon,
        );
        $url = BASE_URL . ENDPOINT . '?' . http_build_query($fields);
    } else if ($sType == 'priceestimates') {
        $lat = $_GET['lat'];
        $lon = $_GET['lon'];
        $elat = $_GET['elat'];
        $elon = $_GET['elon'];
        $fields = array(
            'start_latitude' => $lat,
            'start_longitude' => $lon,
            'end_latitude' => $elat,
            'end_longitude' => $elon,
        );
        $url = BASE_URL . PRICEENDPOINT . '?' . http_build_query($fields);
    } else if ($sType == 'timeestimates') {
        $lat = $_GET['lat'];
        $lon = $_GET['lon'];
        $fields = array(
            'start_latitude' => $lat,
            'start_longitude' => $lon,
        );
        $url = BASE_URL . TIMEENDPOINT . '?' . http_build_query($fields);
    } else if ($sType == 'bookuber') {
        $lat = $_GET['lat'];
        $lon = $_GET['lon'];
        $elat = $_GET['elat'];
        $elon = $_GET['elon'];
        $product_id = $_GET['productid'];
        $access_token = $_GET['accesstoken'];

        $fields = array(
            'start_latitude' => $lat,
            'start_longitude' => $lon,
            'end_latitude' => $elat,
            'end_longitude' => $elon,
            'product_id' => $product_id,
            'surge_confirmation_id' => ''
        );
        $url = BASE_URL . BOOKENDPOINT;
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    if ($sType != 'bookuber') {
        curl_setopt($ch, CURLOPT_HTTPGET, true);
    } else {
        $jsonDataEncoded = json_encode($fields);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonDataEncoded);
    }
    curl_setopt($ch, CURLOPT_HEADER, false);
    if ($sType != 'bookuber') {
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Token ' . SERVER_TOKEN));
    } else {
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: Bearer ' . $access_token));
    }
    $result = curl_exec($ch);

    if ($result === false) {
        echo "Error Number:" . curl_errno($ch) . "<br>";
        echo "Error String:" . curl_error($ch);
    }
    curl_close($ch);
    if ($sType != 'bookuber') {
        echo $result;
    } else {
        $myFile = "uberBooking" . date('YmdHis') . ".xml";
        $intAttempt = 1;
        $bookResult = json_decode($result);
        file_put_contents($myFile, "Booking Response : " . $result, FILE_APPEND | LOCK_EX);
        $request_id = $bookResult->request_id;
        //file_put_contents($myFile, "Request ID : " . $request_id, FILE_APPEND | LOCK_EX);
        $url = BASE_URL . BOOKENDPOINT . '/' . $request_id;
        while ($intAttempt < 6) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_HTTPGET, true);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: Bearer ' . $access_token));
            $result = curl_exec($ch);
            if ($result === false) {
                echo "Error Number:" . curl_errno($ch) . "<br>";
                echo "Error String:" . curl_error($ch);
            }
            curl_close($ch);
            $responseResult = json_decode($result);
            if ($responseResult->status == "accepted") {
                break;
            } else {
                file_put_contents($myFile, "<br /><br /> intAttempt : " . $intAttempt, FILE_APPEND | LOCK_EX);
                file_put_contents($myFile, $result, FILE_APPEND | LOCK_EX);
                $intAttempt = $intAttempt + 1;
                sleep(5);
            }
        }
        echo $result;
    }
}
