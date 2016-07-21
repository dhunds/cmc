<?php

$dsn = 'mysql:host=localhost;dbname=cmcdev';
$us = 'root';
$pa = 'root';

try {
    $con = new PDO($dsn, $us, $pa);
} catch (PDOException $e) {
    $error_message = $e->getMessage();
    echo $error_message;
    exit();
}