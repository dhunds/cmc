<?php
ini_set('display_errors', 1);
include ('connection.php');
$con->setAttribute(PDO::ATTR_EMULATE_PREPARES, 0);
$customerNumber = $_GET['customerNumber'];
if($customerNumber !=''){
    $sql = "DELETE FROM acceptedrequest WHERE MemberNumber='".$customerNumber."';
        DELETE FROM cabmembers WHERE MemberNumber='".$customerNumber."';
        DELETE FROM cabopen WHERE MobileNumber='".$customerNumber."';
        DELETE FROM feedback WHERE MemberNumber='".$customerNumber."';
        DELETE FROM referfriendtoclub WHERE MemberNumber='".$customerNumber."';
        DELETE FROM registeredusers WHERE MobileNumber='".$customerNumber."';
        DELETE FROM userpoolsmaster WHERE OwnerNumber='".$customerNumber."';
        DELETE FROM userpoolsslave WHERE MemberNumber='".$customerNumber."';
        DELETE FROM userprofileimage WHERE MobileNumber='".$customerNumber."';
        DELETE FROM notifications WHERE ReceiveMemberNumber='".$customerNumber."';
        DELETE FROM cronnotifications WHERE ReceiveMemberNumber='".$customerNumber."';
        ";
}

try {
    $con->exec($sql);
} catch (PDOException $e) {
    echo $e->getMessage();
    die();
}
