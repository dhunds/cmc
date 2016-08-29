Ø<?php
include('connection.php');
include_once('classes/class.notification.php');
$objNotification = new Notification();

$poolid = $_POST['poolid'];
$ClubMembersName = $_POST['ClubMembersName'];
$ClubMembersNumber = $_POST['ClubMembersNumber'];

$ClubMembersNumberNew = substr($ClubMembersNumber, 1, -1);
$ClubMembersNameNew = substr($ClubMembersName, 1, -1);

$memNumber = explode(",", $ClubMembersNumberNew);
$memName = explode(",", $ClubMembersNameNew);

$OwnerNumber = '';
$OwnerName = '';
$ClubName = '';

$stmtO = $con->query("SELECT * FROM userpoolsmaster WHERE PoolId = '$poolid'");
$OwnerExists = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

if ($OwnerExists > 0) {
    while ($row = $stmtO->fetch()) {
        $OwnerNumber = trim((string)$row['OwnerNumber']);
        $ClubName = $row['PoolName'];
    }

    if ($OwnerNumber != '') {
        $stmtI = $con->query("SELECT * FROM registeredusers WHERE MobileNumber = '$OwnerNumber' and PushNotification != 'off'");
        $OwnRegExists = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

        if ($OwnRegExists > 0) {
            while ($row = $stmtI->fetch()) {
                $OwnerName = $row['FullName'];
	            $OwnerReferralCode = $row['referralCode'];
                $OwnerDeviceToken = $row['DeviceToken'];
                $OwnerPlatform = $row['Platform'];
            }
        }
    }
}


$sql = $con->prepare("SELECT setValue FROM settings where setName = 'CLUBLIMIT'");
$sql->execute();
$club_limit = (int)$sql->fetchColumn();

$sql7 = $con->prepare("SELECT PoolStatus FROM userpoolsmaster WHERE PoolId='$poolid'");
$sql7->execute();
$PoolStatus = $sql7->fetchColumn();

$sql8 = $con->prepare("SELECT count(*) FROM userpoolsslave WHERE PoolId='$poolid'");
$sql8->execute();
$club_current_members = $sql8->fetchColumn();


$length = count($memNumber);

$total_club_member = $club_current_members + $length;
if ($PoolStatus == "OPEN") {
    if ($club_limit >= $club_current_members) {
        for ($i = 0; $i < $length; $i++) {
            $MemberNumber = trim((string)$memNumber[$i]);

            $stmtF = $con->query("SELECT * FROM registeredusers WHERE MobileNumber = Trim('$MemberNumber') and PushNotification != 'off' AND DeviceToken !=''");
            $FriendExists = $con->query("SELECT FOUND_ROWS()")->fetchColumn();
            if ($FriendExists > 0) {

                while ($row = $stmtF->fetch()) {
                    $MemberDeviceToken = $row['DeviceToken'];
                    $MemberPlatform = $row['Platform'];
                    $MemberName = $row['FullName'];
                }
                $Message = $OwnerName . " has added you to the group " . $ClubName;
                $gcm_arrayF = array();
                $apns_arrayF = array();
                if ($MemberPlatform == "A") {
                    $gcm_arrayF[] = $MemberDeviceToken;
                } else {
                    $apns_arrayF[] = $MemberDeviceToken;
                }

                $NotificationType = "PoolId_Added";
                $manFriend = "INSERT INTO notifications(NotificationType, SentMemberName, SentMemberNumber, ReceiveMemberName, ReceiveMemberNumber, Message, PoolId, DateTime) VALUES ('$NotificationType','$OwnerName','$OwnerNumber','$MemberName','$MemberNumber','$Message','$poolid',now())";
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
	            $sql = "SELECT amount FROM referral";
	            $stmt = $con->query($sql);
	            $referral = $stmt->fetch(PDO::FETCH_ASSOC);

                $sqlSMS = "SELECT SmsMessage FROM smstemplates WHERE SmsshortCode = 'CLUBADD'";
                $stmtSMS = $con->query($sqlSMS);
                $messageSMS = $stmtSMS->fetchColumn();
                $messageSMS = str_replace("OXXXXX", $OwnerName, $messageSMS);

                $resp = $objNotification->sendSMS("[" . $MemberNumber . "]", $messageSMS);
            }

            $stmt1 = $con->query("SELECT * FROM userpoolsslave WHERE PoolId='$poolid' AND MemberNumber= Trim('$MemberNumber')");
            //$rows = $stmt1->fetchAll(PDO::FETCH_ASSOC);
            //$user_exists = count($rows);
            $user_exists = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

            if ($user_exists == 0) {
                $sql3 = "INSERT INTO userpoolsslave(PoolId,MemberName,MemberNumber, IsActive) VALUES  ('$poolid','$memName[$i]',Trim('$MemberNumber'),'1')";
                $stmt3 = $con->prepare($sql3);
                $res3 = $stmt3->execute();
            }
        }

        $sql9 = $con->prepare("SELECT count(*) FROM userpoolsslave WHERE PoolId='$poolid'");
        $sql9->execute();
        $club_current_member_count = (int)$sql9->fetchColumn();

        if ($club_limit == ($club_current_member_count + 1)) {
            $sql12 = "UPDATE userpoolsmaster set PoolStatus = 'FILLED' where PoolId ='$poolid'";
            $stmt12 = $con->prepare($sql12);
            $res12 = $stmt12->execute();
        }

        echo "SUCCESS";
    } else {
        echo "FILLED";
    }
} else {
    echo "CLOSED";
}
