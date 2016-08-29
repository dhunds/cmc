<?php
include('connection.php');

$UserNumber = $_POST['UserNumber'];
$Email = $_POST['Email'];
$Gender = $_POST['Gender'];
$DOB = $_POST['DOB'];

if (isset($_POST['fullName'])) {
    $fullName = $_POST['fullName'];
    $sql = "UPDATE registeredusers SET Email='$Email',Gender='$Gender',DOB='$DOB', FullName='$fullName' WHERE MobileNumber =
'$UserNumber'";
} else {
    $sql = "UPDATE registeredusers SET Email='$Email',Gender='$Gender',DOB='$DOB' WHERE MobileNumber = '$UserNumber'";
}

$stmt = $con->prepare($sql);
$res = $stmt->execute();

if ($res === true) {
    echo 'update success';
} else {
    echo 'Error';
}