<?php
require('connection.php');
require('OAuth2/Client.php');
require('OAuth2/GrantType/IGrantType.php');
require('OAuth2/GrantType/AuthorizationCode.php');

$sType = $_REQUEST['type'];

define("REDIRECT_URI", BASEURL."/uberConnect.php");

const ENDPOINT = 'products';
const PRICEENDPOINT = 'estimates/price';
const TIMEENDPOINT = 'estimates/time';
const BOOKENDPOINT = 'requests';


//const SANDBOXUBER_BASE_URL = 'https://sandbox-api.uber.com/v1/';
//const SANDBOXBOOKENDPOINT = 'requests';

if ($sType != '') {
    if ($sType == 'products') {
        $lat = $_REQUEST['lat'];
        $lon = $_REQUEST['lon'];
        $fields = array(
            'latitude' => $lat,
            'longitude' => $lon,
        );
        $url = UBER_BASE_URL . ENDPOINT . '?' . http_build_query($fields);
    } else if ($sType == 'priceestimates') {
        $lat = $_REQUEST['lat'];
        $lon = $_REQUEST['lon'];
        $elat = $_REQUEST['elat'];
        $elon = $_REQUEST['elon'];
        $fields = array(
            'start_latitude' => $lat,
            'start_longitude' => $lon,
            'end_latitude' => $elat,
            'end_longitude' => $elon,
        );
        $url = UBER_BASE_URL . PRICEENDPOINT . '?' . http_build_query($fields);
    } else if ($sType == 'timeestimates') {
        $lat = $_REQUEST['lat'];
        $lon = $_REQUEST['lon'];
        $fields = array(
            'start_latitude' => $lat,
            'start_longitude' => $lon,
        );
        $url = UBER_BASE_URL . TIMEENDPOINT . '?' . http_build_query($fields);
    } else if ($sType == 'bookuber') {

        $lat = $_REQUEST['lat'];
        $lon = $_REQUEST['lon'];
        $elat = $_REQUEST['elat'];
        $elon = $_REQUEST['elon'];
        $product_id = $_REQUEST['productid'];
        $access_token = $_REQUEST['accesstoken'];

        $fields = array(
            'start_latitude' => $lat,
            'start_longitude' => $lon,
            'end_latitude' => $elat,
            'end_longitude' => $elon,
            'product_id' => $product_id
        );
        $url = UBER_BASE_URL . BOOKENDPOINT;
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    if ($sType != 'bookuber') {
        curl_setopt($ch, CURLOPT_HTTPGET, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Token ' . SERVER_TOKEN));
    } else {
        $jsonDataEncoded = json_encode($fields);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonDataEncoded);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: Bearer ' . $access_token));
    }

    curl_setopt($ch, CURLOPT_HEADER, false);
    $result = curl_exec($ch);
    curl_close($ch);

    //echo '<pre>';
    //echo $result;die;

    if ($result === false) {
        echo "Error Number:" . curl_errno($ch) . "<br>";
        echo "Error String:" . curl_error($ch);
    }

    if ($sType != 'bookuber') {
        echo $result;
    } else {
        $bookResult = json_decode($result);

        if ($bookResult->errors) {
            echo $result;
        } else {
            $intAttempt = 1;
            $request_id = $bookResult->request_id;
            $url = UBER_BASE_URL . BOOKENDPOINT . '/' . $request_id;

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
                    echo $result;
                    break;
                } else {
                    $intAttempt++;
                    sleep(5);
                }
            }
        }
    }

} else {
    echo 'Invalid type.';
}
exit;
