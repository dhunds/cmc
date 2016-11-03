<?php
$dsn = 'mysql:host=localhost;dbname=applock';
 $us = 'root';
 $pa = 'Vbh4{tc+Nb';
//$pa = 'root';

try {
    $con = new PDO($dsn, $us, $pa);
} catch (PDOException $e) {
    $error_message = $e->getMessage();
    //include('../errors/err.php');
    echo $error_message;
    exit();
}

define("ENC_KEY", "NrD4LMv5xGAXdYvxvzzcxASUgWQkKcZx");