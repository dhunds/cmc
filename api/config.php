<?php

include_once('db.php');
include_once('functions.php');

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