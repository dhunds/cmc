<?php

include_once('config.php');

if (isset($_POST['username']) && $_POST['username'] !='' && isset($_POST['password']) && $_POST['password'] !=''){
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $stmt = $con->query("SELECT id, username, password FROM clients WHERE username='$username' AND password='$password'");
    $found = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

    if ($found > 0){
        $token = md5($username.$password.time());
        $sql = "UPDATE clients SET token = '$token' updated=now() WHERE username = '$username'";
        $stmt = $con->prepare($sql);
        $stmt->execute();
        setResponse(array("code"=>200, "status"=>"Success", "token"=>$token));
    }
} else{
    setResponse(array("code"=>200, "status"=>"Error", "message"=>"Bad Request"));
}