<?php

require('OAuth2/Client.php');
require('OAuth2/GrantType/IGrantType.php');
require('OAuth2/GrantType/AuthorizationCode.php');

include('connection.php');

$sType = $_GET['type'];

//const CLIENT_ID = 'ci-UpBdN7CONf8spC18ZdbETbb-sc4MC';
//const SERVER_TOKEN = '97EIxmps8i-URhD995lHiWJSRVn5caxFEO_Dq2_a';
//const CLIENT_SECRET = 'Dl85_kQxc5TXrDC0jaaQwGABc-N1yG0CVujFUUJz';

//Dev
const CLIENT_ID = 'RglrsY1E0pqbcf_zdiHXkk7Tz8jxGarP';
const SERVER_TOKEN = 'ZuTOtu6k0s4AaXCX9_pKuuGz36kKD-wEp0G2ofJ3';
const CLIENT_SECRET = 'yHUs9wPvKNazJKSow076cxSdSl9MQ5xdfdcDxQcX';

const AUTHORIZATION_ENDPOINT = 'https://login.uber.com/oauth/authorize';
const TOKEN_ENDPOINT = 'https://login.uber.com/oauth/token';
const REDIRECT_URI = 'https://180.179.208.23/cmc/cmcservice/uberapi.php';
const REDIRECT_PROCESS_URI = 'http://180.179.208.23/cmc/cmcservice/processing.php';

if (isset($_GET['code'])) {
    $requestID = $_GET['requestid'];
    $code = $_GET['code'];
    //echo $requestID;
    $client = new OAuth2\Client(CLIENT_ID, CLIENT_SECRET);
    $params = array('code' => $_GET['code'], 'redirect_uri' => REDIRECT_URI, 'grant_type' => 'authorization_code', 'client_id' => CLIENT_ID, 'client_secret' => CLIENT_SECRET);
    $response = $client->getAccessToken(TOKEN_ENDPOINT, 'authorization_code', $params);
    $last_authenticated = '';
    $access_token = '';
    $expires_in = '';
    $token_type = '';
    $scope = '';
    $refresh_token = '';
    $info = $response['result'];
    foreach ($info as $key => $val) {
        if ($key == 'last_authenticated') {
            $last_authenticated = $info[$key];
        } else if ($key == 'access_token') {
            $access_token = $info[$key];
        } else if ($key == 'expires_in') {
            $expires_in = $info[$key];
        } else if ($key == 'token_type') {
            $token_type = $info[$key];
        } else if ($key == 'scope') {
            $scope = $info[$key];
        } else if ($key == 'refresh_token') {
            $refresh_token = $info[$key];
        }
    }
    if ($access_token != '') {
        $sql2 = "UPDATE cabbookingrequest SET access_token = '$access_token', expires_in = '$expires_in', token_type = '$token_type', scope = '$scope', refresh_token = '$refresh_token', requestStatus = 'REQUESTDONE', oauth_token = '$code' where requestID = '$requestID'";
        $stmt2 = $con->prepare($sql2);
        $res2 = $stmt2->execute();
    }
    header('Location: ' . REDIRECT_PROCESS_URI);
}

if ($sType != '') {
    if ($sType == 'oauth') {
        $client = new OAuth2\Client(CLIENT_ID, CLIENT_SECRET);
        if (!isset($_GET['code'])) {
            $auth_url = $client->getAuthenticationUrl(AUTHORIZATION_ENDPOINT, REDIRECT_URI);
            header('Location: ' . $auth_url);
        }
    }
}
