<?php

$dsn = 'mysql:host=localhost;dbname=cmcdev';
$us = 'root';
$pa = 'root';
define('PAGESIZE', 30);

try {
    $con = new PDO($dsn, $us, $pa);
} catch (PDOException $e) {
    $error_message = $e->getMessage();
    echo $error_message;
    exit();
}

if (isset($_POST['username']) && $_POST['username'] !='' && isset($_POST['password']) && $_POST['password'] !=''){
    //
} else if (isset($_POST['token']) && $_POST['token'] !=''){
    $token = trim($_POST['token']);
    $stmt = $con->query("SELECT id FROM clients WHERE token='$token'");
    $found = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

    if ($found < 1){
        setResponse(array("code"=>200, "status"=>"failure", "message"=>"Bad Request"));
    }
} else{
    setResponse(array("code"=>200, "status"=>"failure", "message"=>"Bad Request"));
}


function setResponse($args){
    $code = $args['code'];
    unset($args['code']);

    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($args);
    exit;
}