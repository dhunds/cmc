<?php
include ('connection.php');

$MobileNumber = $_POST['MobileNumber'];
$singleusepassword = $_POST['singleusepassword'];
$DeviceToken = $_POST['DeviceToken'];
$Platform = $_POST['Platform'];

$stmt1 = $con->query("SELECT * FROM registeredusers WHERE MobileNumber = '$MobileNumber' and SingleUsePassword = '$singleusepassword'");
$rows = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

if($rows > 0)
{
    $stmt = $con->query("SELECT * FROM registeredusers WHERE MobileNumber = '$MobileNumber' and SingleUsePassword = '$singleusepassword' and SingleUseExpiry > NOW()");
    $found = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

    if($found > 0)
    {
        if ($MobileNumber == '00919810000000') { 
            $SingleUseVerified = 0;
        } else {
            $SingleUseVerified = 1;
        }

        if (isset($_POST['appName']) && $_POST['appName']=='itrackryde') {
            $user = $stmt->fetch();
            $DeviceToken = $user['DeviceToken'];
        }

        $sql2 = "UPDATE `registeredusers` SET SingleUseVerified = '1', Platform = '$Platform',DeviceToken = '$DeviceToken', LastLoginDateTime = now() where MobileNumber = '$MobileNumber'";
        $stmt2 = $con->prepare($sql2);
        $res2 = $stmt2->execute();
        echo "SUCCESS";

    } else {
        echo "OTPEXPIRE";
    }
}else{
    echo "FAILURE";
}
