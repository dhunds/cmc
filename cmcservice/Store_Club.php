<?php
include('connection.php');
include_once('classes/class.notification.php');
$objNotification = new Notification();

$OwnerName = $_POST['OwnerName'];
$OwnerNumber = $_POST['OwnerNumber'];
$ClubName = $_POST['ClubName'];
$ClubMembersName = $_POST['ClubMembersName'];
$ClubMembersNumber = $_POST['ClubMembersNumber'];

$sql = "SELECT referralCode FROM registeredusers WHERE MobileNumber='".trim($OwnerNumber)."'";
$stmt = $con->query($sql);
$usr = $stmt->fetch(PDO::FETCH_ASSOC);
$OwnerReferralCode = $usr['referralCode'];


$ClubMembersNumberNew = substr($ClubMembersNumber, 1, -1);
$ClubMembersNameNew = substr($ClubMembersName, 1, -1);

$sql = $con->prepare("SELECT setValue FROM settings where setName = 'CLUBLIMIT'");
$sql->execute();
$club_limit = (int)$sql->fetchColumn();


$stmt = $con->query("Select * From userpoolsmaster Where PoolName='$ClubName' AND OwnerNumber='$OwnerNumber'");
//$no_of_users = $stmt->rowCount();
$no_of_users = $con->query("SELECT FOUND_ROWS()")->fetchColumn();
if ($no_of_users > 0) {
    echo 'Club Already Exist';
} else {
    $sql2 = "INSERT INTO userpoolsmaster(OwnerNumber, PoolName, Active) VALUES ('$OwnerNumber','$ClubName','1')";
    $stmt2 = $con->prepare($sql2);
    $res2 = $stmt2->execute();
    $poolid = $con->lastInsertId();

    if ($res2 == true) {
        $IsCreated = false;
        $memNumber = explode(",", $ClubMembersNumberNew);
        $memName = explode(",", $ClubMembersNameNew);
        $length = count($memNumber);
        if ($length < $club_limit) {
            for ($i = 0; $i < $length; $i++) {

                $FriendDeviceToken = '';
                $FriendPlatform = '';
                $FriendName = '';
                $FriendNumber = '';

                $FriendNumber = trim((string)$memNumber[$i]);
                $FriendName = trim((string)$memName[$i]);

                if ($FriendNumber != trim($OwnerNumber)) {

                    $Message = $OwnerName . ' added you to a group ' . $ClubName;

                    $stmtF = $con->query("SELECT * FROM registeredusers WHERE MobileNumber = '$FriendNumber' and PushNotification != 'off'");
                    $FriendExists = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

                    if ($FriendExists > 0) {
                        while ($row = $stmtF->fetch()) {
                            $FriendDeviceToken = $row['DeviceToken'];
                            $FriendPlatform = $row['Platform'];
                            $FriendName = $row['FullName'];
                        }
                    }

                    if ($FriendDeviceToken != '') {
                        $gcm_arrayF = array();
                        $apns_arrayF = array();
                        if ($FriendPlatform == "A") {
                            $gcm_arrayF[] = $FriendDeviceToken;
                        } else {
                            $apns_arrayF[] = $FriendDeviceToken;
                        }

                        $NotificationType = "PoolId_Added";
                        $manFriend = "INSERT INTO notifications(NotificationType, SentMemberName, SentMemberNumber, ReceiveMemberName, ReceiveMemberNumber, Message, PoolId, DateTime) VALUES ('$NotificationType','$OwnerName','$OwnerNumber','$FriendName','$FriendNumber','$Message','$poolid',now())";
                        $manstmtFriend = $con->prepare($manFriend);
                        $manresFriend = $manstmtFriend->execute();
                        $notificationId = $con->lastInsertId();

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
                        $objNotification->sendSMS("[" . $FriendNumber . "]", $messageSMS);
                    }

                    $sql3 = "INSERT INTO userpoolsslave(PoolId,MemberName,MemberNumber, IsActive) VALUES ('$poolid','$memName[$i]','$memNumber[$i]','1')";
                    $stmt3 = $con->prepare($sql3);
                    $res3 = $stmt3->execute();
                    if ($res3 == true) {
                        $IsCreated = true;
                    }
                }
            }
        }

        if ($IsCreated == false) {
            echo 'FAILURE';
        } else {
            echo 'SUCCESS';
        }
    } else {
        echo 'FAILURE';
    }
}
?>
