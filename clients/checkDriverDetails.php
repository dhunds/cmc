<?php
include_once('connection.php');

if (isset($_POST['mobileNumber']) && $_POST['mobileNumber'] !='') {

    $MobileNumber = '0091' . substr(trim($_POST['mobileNumber']), -10);

    $sql = "SELECT ru.FullName, v.vehicleModel, vd.registrationNumber FROM registeredusers ru JOIN userVehicleDetail vd ON ru.MobileNumber=vd.mobileNumber JOIN vehicle v ON vd.vehicleId=v.id WHERE ru.MobileNumber='" . $MobileNumber . "'";
    $stmt = $con->query($sql);
    $driverExists = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

    if ($driverExists) {
        $driverDetail = $stmt->fetch();
        echo $driverDetail['FullName'].'~'.$driverDetail['vehicleModel'].'~'.$driverDetail['registrationNumber'];
    } else {
        echo 'fail';
    }
}
exit();