<?php
include('connection.php');
include_once('classes/class.notification.php');
$objNotification = new Notification();

$RefId = $_POST['RefId'];
$OwnerName = $_POST['OwnerName'];
$OwnerNumber = $_POST['OwnerNumber'];
$Accepted = $_POST['Accepted'];
$PoolId = '';
$PoolName = '';
$MemberNumber = '';
$MemberName = '';
$MemberDeviceToken = '';
$MemberPlatform = '';
$FriendDeviceToken = '';
$FriendPlatform = '';
$FriendNumber = '';
$FriendName = '';
$OwnerName = '';
$OwnerNumber = '';

$stmt = $con->query("SELECT * FROM referfriendtoclub WHERE RefId = '$RefId'");
$no_of_users = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

if ($no_of_users > 0) {
    while ($row = $stmt->fetch()) {
        $MemberNumber = trim((string)$row['MemberNumber']);
        $FriendNumber = trim((string)$row['FriendNumber']);
        $FriendName = $row['FriendName'];
        $PoolId = $row['PoolId'];
    }

    $sqlNotification = "UPDATE notifications SET RefStatus = '$Accepted' where RefId = '$RefId' and PoolId = '$PoolId'";
    $stmtNotification = $con->prepare($sqlNotification);
    $resNotification = $stmtNotification->execute();
    if ($resNotification == true) {
        $sqlI = "SELECT PoolName FROM userpoolsmaster WHERE PoolId = '$PoolId'";
        $stmtI = $con->query($sqlI);
        $PoolName = $stmtI->fetchColumn();

        $sqlO = "SELECT OwnerNumber FROM userpoolsmaster WHERE PoolId = '$PoolId'";
        $stmtO = $con->query($sqlO);
        $OwnerNumber = trim((string)$stmtO->fetchColumn());

        $stmtM = $con->query("SELECT * FROM registeredusers WHERE MobileNumber = '$MemberNumber' and PushNotification != 'off'");
        $MemberExists = $con->query("SELECT FOUND_ROWS()")->fetchColumn();
        if ($MemberExists > 0) {
            while ($row = $stmtM->fetch()) {
                $MemberDeviceToken = $row['DeviceToken'];
                $MemberPlatform = $row['Platform'];
                $MemberName = $row['FullName'];
            }
        }

        $stmtGetCab = $con->query("SELECT * FROM registeredusers WHERE MobileNumber = '$OwnerNumber'");
        $cabExists = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

        if ($cabExists > 0) {
            while ($row = $stmtGetCab->fetch()) {
                $OwnerName = $row['FullName'];
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

            $Message = $OwnerName . "  accepted " . $FriendName . " to club " . $PoolName;

            if ($MemberDeviceToken != '') {
                $gcm_array = array();
                $apns_array = array();

                if ($MemberPlatform == "A") {
                    $gcm_array[] = $MemberDeviceToken;
                } else {
                    $apns_array[] = $MemberDeviceToken;
                }

                $NotificationType = "PoolId_Approved";
                $man = "INSERT INTO notifications(NotificationType, SentMemberName, SentMemberNumber, ReceiveMemberName, ReceiveMemberNumber, Message, PoolId, DateTime) VALUES ('$NotificationType','$OwnerName','$OwnerNumber','$MemberName','$MemberNumber','$Message','$PoolId',now())";
                $manstmt = $con->prepare($man);
                $manres = $manstmt->execute();
                $notificationId =  $con->lastInsertId();

                if ($manres === true) {
                    $body = array('gcmText' => $Message, 'pushfrom' => 'PoolId_', 'notificationId' => $notificationId);
                    if (count($gcm_array) > 0) {
                        $objNotification->setVariables($gcm_array, $body);
                        $res = $objNotification->sendGCMNotification();
                    }

                    if (count($apns_array) > 0) {
                        $objNotification->setVariables($gcm_array, $body);
                        $res = $objNotification->sendIOSNotification();
                    }
                }
            }

            $sql3 = "INSERT INTO userpoolsslave(PoolId,MemberName,MemberNumber, IsActive) VALUES  ('$PoolId','$FriendName',Trim('$FriendNumber'),'1')";
            $stmt3 = $con->prepare($sql3);
            $res3 = $stmt3->execute();
            if ($res3 == true) {
                $Message = $OwnerName . "  added you to club " . $PoolName . " (referred by " . $MemberName . ")";

                if ($FriendDeviceToken != '') {
                    $gcm_arrayF = array();
                    $apns_arrayF = array();
                    if ($FriendPlatform == "A") {
                        $gcm_arrayF[] = $FriendDeviceToken;
                    } else {
                        $apns_arrayF[] = $FriendDeviceToken;
                    }

                    $NotificationType = "PoolId_Added";
                    $manFriend = "INSERT INTO notifications(NotificationType, SentMemberName, SentMemberNumber, ReceiveMemberName, ReceiveMemberNumber, Message, PoolId, DateTime) VALUES ('$NotificationType','$OwnerName','$OwnerNumber','$FriendName','$FriendNumber','$Message','$PoolId',now())";
                    $manstmtFriend = $con->prepare($manFriend);
                    $manresFriend = $manstmtFriend->execute();
                    $notificationId =  $con->lastInsertId();

                    if ($manresFriend === true) {
                        $body = array('gcmText' => $Message, 'pushfrom' => 'PoolId_', 'notificationId' => $notificationId);
                        if (count($gcm_arrayF) > 0) {
                            $objNotification->setVariables($gcm_arrayF, $body);
                            $res = $objNotification->sendGCMNotification();
                        }

                        if (count($apns_arrayF) > 0) {
                            $objNotification->setVariables($apns_arrayF, $body);
                            $objNotification->sendIOSNotification();
                        }
                    }
                } else {
	                $stmtM = $con->query("SELECT FullName, referralCode FROM registeredusers WHERE MobileNumber = '$MemberNumber'");
	                $referrer = $stmtM->fetch(PDO::FETCH_ASSOC);

	                $sql = "SELECT amount FROM referral";
	                $stmt = $con->query($sql);
	                $referral = $stmt->fetch(PDO::FETCH_ASSOC);

                    $sqlSMS = "SELECT SmsMessage FROM smstemplates WHERE SmsshortCode = 'CLUBADD'";
                    $stmtSMS = $con->query($sqlSMS);
                    $messageSMS = $stmtSMS->fetchColumn();
                    $messageSMS = str_replace("OXXXXX", $referrer['FullName'], $messageSMS);

                    $objNotification->sendSMS("[" . $FriendNumber . "]", $messageSMS);
                }
            }
        } else if (strtolower($Accepted) == strtolower('NO')) {
            $Message = $OwnerName . "  rejected referral of " . $FriendName . " to club " . $PoolName;

            if ($MemberDeviceToken != '') {
                $gcm_array = array();
                $apns_array = array();

                if ($MemberPlatform == "A") {
                    $gcm_array[] = $MemberDeviceToken;
                } else {
                    $apns_array[] = $MemberDeviceToken;
                }

                $NotificationType = "PoolId_Rejected";
                $man = "INSERT INTO notifications(NotificationType, SentMemberName, SentMemberNumber, ReceiveMemberName, ReceiveMemberNumber, Message, PoolId, DateTime) VALUES ('$NotificationType','$OwnerName','$OwnerNumber','$MemberName','$MemberNumber','$Message','$PoolId',now())";
                $manstmt = $con->prepare($man);
                $manres = $manstmt->execute();
                $notificationId =  $con->lastInsertId();

                if ($manres === true) {
                    $body = array('gcmText' => $Message, 'pushfrom' => 'PoolId_', 'notificationId' => $notificationId);
                    if (count($gcm_array) > 0) {
                        $objNotification->setVariables($gcm_array, $body);
                        $res = $objNotification->sendGCMNotification();
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
