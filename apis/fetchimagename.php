<?php
include('connection.php');
$userId = $_POST['userId'];

$stmt = $con->query("SELECT imagename FROM userprofileimage WHERE userId = '$userId'");
$no_of_users = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

if ($no_of_users > 0) {
    $result = $stmt->fetchColumn();
    echo $result;
} else {
    echo "No Data";
}