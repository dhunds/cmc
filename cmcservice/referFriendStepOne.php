<?php
include('connection.php');
include_once('classes/class.notification.php');
$objNotification = new Notification();

$PoolId = $_POST['ClubId'];
$MemberName = $_POST['MemberName'];
$MemberNumber = $_POST['MemberNumber'];
$ReferedUserName = $_POST['ReferedUserName'];
$ReferedUserNumber = $_POST['ReferedUserNumber'];

$Message = '';
$OwnerNumber = '';
$OwnerName = '';
$OwnerDeviceToken = '';
$Platform = '';
$RefId = 0;
$IsSuccess = false;
$ClubName = '';

$ReferedUserNumberNew = substr($ReferedUserNumber, 1, -1);
$ReferedUserNameNew = substr($ReferedUserName, 1, -1);

$sqlI = "SELECT OwnerNumber FROM userpoolsmaster WHERE PoolId = '$PoolId' and PoolStatus = 'OPEN' and Active = 1";
$stmtI = $con->query($sqlI);
$OwnerNumber = $stmtI->fetchColumn();

$sqlP = "SELECT PoolName FROM userpoolsmaster WHERE PoolId = '$PoolId' and PoolStatus = 'OPEN' and Active = 1";
$stmtP = $con->query($sqlP);
$ClubName = $stmtP->fetchColumn();

$stmt = $con->query("SELECT * FROM registeredusers WHERE MobileNumber = '$OwnerNumber' and PushNotification != 'off'");
$no_of_users = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

if ($no_of_users > 0) {
    while ($row = $stmt->fetch()) {
        $OwnerDeviceToken = $row['DeviceToken'];
        $Platform = $row['Platform'];
        $OwnerName = $row['FullName'];
    }

    if ($OwnerDeviceToken != '') {
        $gcm_array = array();
        $apns_array = array();

        if ($Platform == "A") {
            $gcm_array[] = $OwnerDeviceToken;
        } else {
            $apns_array[] = $OwnerDeviceToken;
        }

        $refNumber = explode(",", $ReferedUserNumberNew);
        $refName = explode(",", $ReferedUserNameNew);
        $length = count($refNumber);

        for ($i = 0; $i < $length; $i++) {
            $Message = $MemberName . " has reffered " . $refName[$i] . " to join your group " . $ClubName;
            $NotificationType = "PoolId_Refered";

           $sqlRef = "INSERT INTO referfriendtoclub (PoolId,MemberNumber,FriendNumber,FriendName,RefDateTime) VALUES ('$PoolId','$MemberNumber','$refNumber[$i]','$refName[$i]',now())";
            $stmtRef = $con->prepare($sqlRef);
            $resRef = $stmtRef->execute();
            $RefId = $con->lastInsertId();

            if ($resRef === true) {
               $man = "INSERT INTO notifications(NotificationType, SentMemberName, SentMemberNumber, ReceiveMemberName, ReceiveMemberNumber, Message, PoolId, DateTime,RefId) VALUES ('$NotificationType','$MemberName','$MemberNumber','$OwnerName','$OwnerNumber','$Message','$PoolId',now(),'$RefId')";
                $manstmt = $con->prepare($man);
                $manres = $manstmt->execute();

                if ($manres === true) {
                    $IsSuccess = true;
                } else {
                    $IsSuccess = false;
                }
            } else {
                $IsSuccess = false;
            }
        }
        if ($IsSuccess) {
            if ($OwnerDeviceToken !='admin') {
                $Message = $MemberName . " has referred friend(s) to join your group " . $ClubName;
                $body = array('gcmText' => $Message);
                if (count($gcm_array) > 0) {
                    $objNotification->setVariables($gcm_array, $body);
                    $objNotification->sendGCMNotification();
                }
                if (count($apns_array) > 0) {
                    $objNotification->setVariables($apns_array, $body);
                    $objNotification->sendIOSNotification();
                }
            }
            echo "SUCCESS";
        } else {
            echo "FAILURE";
        }
    } else {
        echo "FAILURE";
    }
} else {
    echo "FAILURE";
}