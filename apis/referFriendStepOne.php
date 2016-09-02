<?php
include('connection.php');
include_once('classes/class.notification.php');
$objNotification = new Notification();

$PoolId = $_POST['ClubId'];
$MemberName = $_POST['MemberName'];
$MemberNumber = $_POST['MemberNumber'];
$ReferedUserName = $_POST['ReferedUserName'];
$ReferedUserNumber = $_POST['ReferedUserNumber'];
$memberUserId = $_POST['memberUserId'];

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

$sqlI = "SELECT ownerUserId, OwnerNumber, PoolName FROM userpoolsmaster WHERE PoolId = '$PoolId' and PoolStatus = 'OPEN' and Active = 1";
$stmtI = $con->query($sqlI);
$row = $stmtI->fetch(PDO::FETCH_ASSOC);
$OwnerNumber = $row['OwnerNumber'];
$ClubName = $row['PoolName'];
$ownerUserId = $row['ownerUserId'];

$stmt = $con->query("SELECT * FROM registeredusers WHERE userId = '$ownerUserId' and PushNotification != 'off'");
$no_of_users = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

if ($no_of_users > 0) {
    while ($row = $stmt->fetch()) {
        $OwnerDeviceToken = $row['DeviceToken'];
        $Platform = $row['Platform'];
        $OwnerName = $row['FullName'];
        $isAdmin = $row['isAdminType'];
        $email = $row['Email'];
    }

    if ($OwnerDeviceToken != '' || $isAdmin) {
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

            $stmtF = $con->query("SELECT userId FROM registeredusers WHERE MobileNumber = '$refNumber[$i]'");
            $FriendExists = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

            if ($FriendExists >0) {
                $friendUserId = $stmtF->fetchColumn();
            } else {
                $friendUserId = 0;
            }

            $Message = $MemberName . " has reffered " . $refName[$i] . " to join your group " . $ClubName;
            $NotificationType = "PoolId_Refered";

            $sqlRef = "INSERT INTO referfriendtoclub (PoolId,MemberNumber,FriendNumber,FriendName,RefDateTime, memberUserId, friendUserId) VALUES ('$PoolId','$MemberNumber','$refNumber[$i]','$refName[$i]',now(), $memberUserId, $friendUserId)";
            $stmtRef = $con->prepare($sqlRef);
            $resRef = $stmtRef->execute();
            $RefId = $con->lastInsertId();

            if ($resRef === true) {
                $man = "INSERT INTO notifications(NotificationType, SentMemberName, SentMemberNumber, ReceiveMemberName, ReceiveMemberNumber, Message, PoolId, DateTime,RefId, sentMemberUserId, receivedMemberUserId) VALUES ('$NotificationType','$MemberName','$MemberNumber','$OwnerName','$OwnerNumber','$Message','$PoolId',now(),'$RefId', $memberUserId, $ownerUserId)";
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

            if ($isAdmin) {

                if ($email !=''){
                    require_once 'mail.php';
                    sendInviteApprovalMail($email);
                }

            } else {
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