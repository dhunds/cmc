<?php
include('connection.php');
include_once('classes/class.notification.php');
$objNotification = new Notification();

$RefId = $_POST['RefId'];
$OwnerName = $_POST['OwnerName'];
$OwnerNumber = $_POST['OwnerNumber'];
$Accepted = $_POST['Accepted'];
$CabId = '';
$PoolName = '';
$MemberNumber = '';
$MemberName = '';
$MemberDeviceToken = '';
$MemberPlatform = '';
$FriendDeviceToken = '';
$FriendPlatform = '';
$FriendNumber = '';
$FriendName = '';
$OwnerName == '';
$OwnerNumber == '';
$FromShortAddress = '';
$ToShortAddress = '';

$stmt = $con->query("SELECT * FROM referfriendtoride WHERE RefId = '$RefId'");
$no_of_users = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

if ($no_of_users > 0) {
    while ($row = $stmt->fetch()) {
        $MemberNumber = (string)$row['MemberNumber'];
        $FriendNumber = (string)$row['FriendNumber'];
        $FriendName = $row['FriendName'];
        $CabId = $row['CabId'];
    }

    $sqlNotification = "UPDATE notifications SET RefStatus = '$Accepted' where RefId = '$RefId' and CabId = '$CabId'";
    $stmtNotification = $con->prepare($sqlNotification);
    $resNotification = $stmtNotification->execute();
    if ($resNotification == true) {
        $stmtM = $con->query("SELECT * FROM registeredusers WHERE MobileNumber = '$MemberNumber' and PushNotification != 'off'");

        $MemberExists = $con->query("SELECT FOUND_ROWS()")->fetchColumn();
        if ($MemberExists > 0) {
            while ($row = $stmtM->fetch()) {
                $MemberDeviceToken = $row['DeviceToken'];
                $MemberPlatform = $row['Platform'];
                $MemberName = $row['FullName'];
            }
        }

        $stmtGetCab = $con->query("SELECT * FROM cabopen WHERE CabId = '$CabId'");
        $cabExists = $con->query("SELECT FOUND_ROWS()")->fetchColumn();
        if ($cabExists > 0) {
            while ($row = $stmtGetCab->fetch()) {
                $OwnerNumber = (string)$row['MobileNumber'];
                $OwnerName = $row['OwnerName'];
                $FromShortAddress = $row['FromShortName'];
                $ToShortAddress = $row['ToShortName'];
            }
        }
        if (strtolower($Accepted) == strtolower('YES')) {
            $stmtF = $con->query("SELECT * FROM registeredusers WHERE MobileNumber = '$FriendNumber' and PushNotification != 'off'");
            $FriendExists = $con->query("SELECT FOUND_ROWS()")->fetchColumn();
            if ($FriendExists > 0) {
                while ($row = $stmtF->fetch()) {
                    $FriendDeviceToken = $row['DeviceToken'];
                    $FriendPlatform = $row['Platform'];
                    $FriendName = $row['FullName'];
                }
            }

            $Message = $MemberName . " referred you to share a cab from " . $FromShortAddress . " to " . $ToShortAddress;

            if ($FriendDeviceToken != '') {
                $gcm_arrayF = array();
                $apns_arrayF = array();
                if ($FriendPlatform == "A") {
                    $gcm_arrayF[] = $FriendDeviceToken;
                } else {
                    $apns_arrayF[] = $FriendDeviceToken;
                }
                $NotificationType = "CabId_Invited";
                $params = array('NotificationType' => $NotificationType, 'SentMemberName' => $OwnerName, 'SentMemberNumber' => $OwnerNumber, 'ReceiveMemberName'=>$FriendName, 'ReceiveMemberNumber'=>$FriendNumber, 'Message'=>$Message, 'CabId'=>$CabId, 'DateTime'=>'now()');
                $notificationId = $objNotification->logNotification($params);

                if ($notificationId >0) {
                    $body = array('gcmText' => $Message, 'pushfrom' => 'CabId_', 'notificationId' => $notificationId);
                    if (count($gcm_arrayF) > 0) {
                        $objNotification->setVariables($gcm_arrayF, $body);
                        $objNotification->sendGCMNotification();
                    }

                    if (count($apns_arrayF) > 0) {
                        $objNotification->setVariables($apns_arrayF, $body);
                        $objNotification->sendIOSNotification();
                    }
                }
            } else {
                $sqlSMS = "SELECT SmsMessage FROM smstemplates WHERE SmsshortCode = 'INVITE'";
                $stmtSMS = $con->query($sqlSMS);
                $messageSMS = $stmtSMS->fetchColumn();

                $messageSMS = str_replace("OXXXXX", $FriendName, $messageSMS);
                $messageSMS = str_replace("FXXXXX", $FromShortAddress, $messageSMS);
                $messageSMS = str_replace("TXXXXX", $ToShortAddress, $messageSMS);
                $objNotification->sendSMS("[" . $FriendNumber . "]", $messageSMS);
            }
        } else if (strtolower($Accepted) == strtolower('NO')) {
            $Message = $OwnerName . " rejected referral of " . $FriendName . " for sharing ride  from " . $FromShortAddress . " to " . $ToShortAddress;
            if ($MemberDeviceToken != '') {
                $gcm_array = array();
                $apns_array = array();
                if ($MemberPlatform == "A") {
                    $gcm_array[] = $MemberDeviceToken;
                } else {
                    $apns_array[] = $MemberDeviceToken;
                }
                $NotificationType = "CabId_Rejected";
                $params = array('NotificationType' => $NotificationType, 'SentMemberName' => $OwnerName, 'SentMemberNumber' => $OwnerNumber, 'ReceiveMemberName'=>$MemberName, 'ReceiveMemberNumber'=>$MemberNumber, 'Message'=>$Message, 'CabId'=>$CabId, 'DateTime'=>'now()');
                $notificationId = $objNotification->logNotification($params);

                if ($notificationId > 0) {
                    $body = array('gcmText' => $Message, 'pushfrom' => 'CabId_', 'notificationId' => $notificationId);
                    if (count($gcm_array) > 0) {
                        $objNotification->setVariables($gcm_array, $body);
                        $objNotification->sendGCMNotification();
                    }

                    if (count($apns_array) > 0) {
                        $objNotification->setVariables($apns_array, $body);
                        $objNotification->sendIOSNotification();
                    }
                }
            }
        }
        echo "SUCCESS";
    } else {
        echo "FAILURE";
    }

} else {
    echo "FAILURE";
}

?>