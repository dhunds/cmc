<?php
session_start();

define( 'USERNAME', 'admin');
define( 'PASSWORD', 'test123');


if (isset($_POST['submit']) && isset($_POST['username']) && $_POST['username'] !='' && isset($_POST['password']) && $_POST['password'] !=''){
    if ($_POST['username']==USERNAME && $_POST['password']==PASSWORD){
        $_SESSION['username'] = $_POST['username'];
    }
}

if (!(isset($_SESSION['username']) && $_SESSION['username'] !='')){
    header('location:index.php?err=1');
}

$dsn = 'mysql:host=localhost;dbname=cmcdev';
$us = 'root';
$pa = 'root';


try {
    $con = new PDO($dsn, $us, $pa);
} catch (PDOException $e) {
    $error_message = $e->getMessage();
    //include('../errors/err.php');
    echo $error_message;
    exit();
}

