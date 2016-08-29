<?php
include('connection.php');

$CabId = $_POST['CabId'];
$MemberNumber = $_POST['MemberNumber'];
$chatstatus = $_POST['chatstatus'];
$IsOwner = $_POST['IsOwner'];

if ($IsOwner == "N") {
    $sql2 = "UPDATE acceptedrequest SET ChatStatus='$chatstatus' WHERE CabId = '$CabId' AND MemberNumber = '$MemberNumber'";
    $stmt2 = $con->prepare($sql2);
    $res2 = $stmt2->execute();
} else if ($IsOwner == "Y") {
    $sql2 = "UPDATE cabopen SET OwnerChatStatus='$chatstatus' WHERE CabId = '$CabId' AND MobileNumber = '$MemberNumber'";
    $stmt2 = $con->prepare($sql2);
    $res2 = $stmt2->execute();

    if ($res2 == true) {
        //echo "success";
    } else {
        //echo "error";
    }
} else if ($IsOwner == "") {
    $IsSuccess = false;
    $sql2 = "UPDATE acceptedrequest SET ChatStatus='$chatstatus' WHERE MemberNumber = '$MemberNumber'";
    $stmt2 = $con->prepare($sql2);
    $res2 = $stmt2->execute();
    if ($res2 == true) {
        $IsSuccess = true;
    }

    $sql21 = "UPDATE cabopen SET OwnerChatStatus='$chatstatus' WHERE MobileNumber = '$MemberNumber'";
    $stmt21 = $con->prepare($sql21);
    $res21 = $stmt21->execute();
    if ($res21 == true) {
        $IsSuccess = true;
    }
}
$sql = "UPDATE registeredusers SET LastLoginDateTime = now() where Trim(MobileNumber) = Trim('$MemberNumber')";
$stmtregistered = $con->prepare($sql);
$resregistered = $stmtregistered->execute();

if (isset($_POST['platform']) && $_POST['platform']=='I'){
    $qry = "SELECT setValue FROM settings where setName = 'IOSAPPVERSION'";
} else {
    $qry = "SELECT setValue FROM settings where setName = 'APPVERSION'";
}

$sql = $con->prepare($qry);
$sql->execute();
$app_version = $sql->fetchColumn();
echo $app_version;
