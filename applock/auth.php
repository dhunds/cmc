<?php
$dsn = 'mysql:host=localhost;dbname=applock';
 $us = 'root';
// $pa = 'Vbh4{tc+Nb';
$pa = 'root';

try {
    $con = new PDO($dsn, $us, $pa);
} catch (PDOException $e) {
    $error_message = $e->getMessage();
    //include('../errors/err.php');
    echo $error_message;
    exit();
}

$uri = $_SERVER['PHP_SELF'];
$link_array = explode('/',$uri);
$page = end($link_array);


$arrAPI = array(
            'getUserStatus.php'
            
        );

if (in_array($page, $arrAPI)) {
    $string = '';
    if (count($_REQUEST) > 0) {
        $arrParams = $_REQUEST;
        $arrParams = [];

        foreach ($_REQUEST as $key => $value) {
            if ($key != 'auth' && $value != '') {
                $key = strtolower($key);
                $arrParams[$key] = $value;
            }
        }

        ksort($arrParams);

        foreach ($arrParams as $value) {
            $string .= $value;
        }
    }
    $auth = hash_hmac('sha256', $string, CMC_KEY);

    if ($auth != $_POST['auth']) {
        http_response_code(200);
        header('Content-Type: application/json');
        echo '{"status":"fail", "message":"Unauthorized Access"}';
        exit;
    }
    unset($arrParams, $string, $value, $auth);
}