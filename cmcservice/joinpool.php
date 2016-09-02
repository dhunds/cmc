<?php
include('connection.php');
include_once('classes/class.notification.php');
$objNotification = new Notification();

$CabId = $_POST['CabId'];
$OwnerName = $_POST['OwnerName'];
$OwnerNumber = $_POST['OwnerNumber'];
$MemberName = $_POST['MemberName'];
$MemberNumber = $_POST['MemberNumber'];
$MemberLocationAddress = $_POST['MemberLocationAddress'];
$MemberLocationlatlong = $_POST['MemberLocationlatlong'];
$MemberEndLocationAddress = $_POST['MemberEndLocationAddress'];
$MemberEndLocationlatlong = $_POST['MemberEndLocationlatlong'];
$Status = $_POST['Status'];
$Message = $_POST['Message'];
$PoolId = $_POST['PoolId'];
$distance = $_POST['distance'];

$sqlI = "SELECT imagename FROM userprofileimage WHERE Trim(MobileNumber) = Trim('$MemberNumber')";
$stmtI = $con->query($sqlI);
$MemberImageName = $stmtI->fetchColumn();

$sth = $con->prepare("SELECT COUNT(*) AS RemainingSeats FROM acceptedrequest WHERE CabId = '$CabId' and Status != 'Dropped'");
$sth->execute();
$RemainingSeats = (int)$sth->fetchColumn();

$sth1 = $con->prepare("SELECT Seats, Distance, perKmCharge FROM cabopen WHERE CabId = '$CabId' and CabStatus = 'A'");
$sth1->execute();
$row = $sth1->fetch(PDO::FETCH_ASSOC);

$Seats = (int)$row['Seats'];
$dist = $row['Distance'];
$perkmCharge = $row['perKmCharge'];

$stmt = $con->query("SELECT MemberName FROM acceptedrequest WHERE CabId = '$CabId' AND MemberNumber='$MemberNumber' Status != 'Dropped'");
$alreadyJoined = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

if ($alreadyJoined) {

    if (($Seats - $RemainingSeats) > 0) {

        if (isset($_POST['rideType']) && $_POST['rideType'] !='' && ($_POST['rideType']=='4' || $_POST['rideType']=='5')){
            $sql = "INSERT INTO cabmembers(CabId, MemberName, MemberNumber) VALUES ('$CabId', '$MemberName', '$MemberNumber')";
            $stmt = $con->prepare($sql);
            $stmt->execute();

            if ($PoolId) {
                $stmt = $con->query("SELECT PoolId FROM userpoolsslave WHERE MemberNumber = '$MemberNumber' AND PoolId = '$PoolId'");
                $isAlreadyMember = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

                if (!$isAlreadyMember) {
                    $sql = "INSERT INTO userpoolsslave(PoolId, MemberName, MemberNumber, IsActive) VALUES ('$PoolId', '$MemberName', '$MemberNumber', '1')";
                    $stmt = $con->prepare($sql);
                    $stmt->execute();
                }

                // Add to reverse pool also
                $stmt = $con->query("SELECT rGid FROM userpoolsmaster WHERE PoolId = '$PoolId'");
                $stmt->execute();
                $reverseGroupId = (int)$stmt->fetchColumn();

                if ($reverseGroupId) {
                    $stmt = $con->query("SELECT PoolId FROM userpoolsslave WHERE MemberNumber = '$MemberNumber' AND PoolId = '$reverseGroupId'");
                    $isAlreadyMember = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

                    if (!$isAlreadyMember) {
                        $sql = "INSERT INTO userpoolsslave(PoolId, MemberName, MemberNumber, IsActive) VALUES ('$reverseGroupId', '$MemberName', '$MemberNumber', '1')";
                        $stmt = $con->prepare($sql);
                        $stmt->execute();
                    }
                }
                // End adding to reverse pool

            }
        }

        $sql2 = "INSERT INTO acceptedrequest(CabId, OwnerName, OwnerNumber, MemberName, MemberNumber, MemberLocationAddress, MemberLocationlatlong, MemberEndLocationAddress, MemberEndLocationlatlong, distance, MemberImageName, Status) VALUES ('$CabId', '$OwnerName','$OwnerNumber','$MemberName', '$MemberNumber','$MemberLocationAddress', '$MemberLocationlatlong','$MemberEndLocationAddress','$MemberEndLocationlatlong', $distance, '$MemberImageName','$Status')";
        $stmt2 = $con->prepare($sql2);
        $res2 = $stmt2->execute();

        if ($res2 === true) {
            if (($Seats - $RemainingSeats) > 0) {
                $updatedRemainingSeats = ($Seats - $RemainingSeats) - 1;
                $upsql2 = "UPDATE cabopen SET RemainingSeats= '$updatedRemainingSeats' WHERE CabId = '$CabId'";
                $upstmt2 = $con->prepare($upsql2);
                $upres2 = $upstmt2->execute();
            }
            
            $isAssociate = isAssociate($OwnerNumber);

            if ($isAssociate) {
                $sql = "SELECT SmsMessage FROM smstemplates WHERE SmsshortCode = 'JOINRIDE'";
                $stmt = $con->query($sql);
                $txtMsg = $stmt->fetchColumn();
                $OwnerNumberWithoutPrefix = substr(trim($OwnerNumber), -10);
                $amount = round($row['Distance'] * $row['perKmCharge']);

                $txtMsg = str_replace("NXXXXX", $OwnerName, $txtMsg);
                $txtMsg = str_replace("MXXXXX", $OwnerNumberWithoutPrefix, $txtMsg);
                $txtMsg = str_replace("AXXXXX", $amount, $txtMsg);
                $MobNumber = '[' . $MobileNumber . ']';
                $objNotification->sendSMS($MobNumber, $txtMsg);
            }

            $NotificationType = "CabId_Joined";

            $params = array('NotificationType' => $NotificationType, 'SentMemberName' => $MemberName, 'SentMemberNumber' => $MemberNumber, 'ReceiveMemberName'=>$OwnerName, 'ReceiveMemberNumber'=>$OwnerNumber, 'Message'=>$Message, 'CabId'=>$CabId, 'DateTime'=>'now()');
            $notificationId = $objNotification->logNotification($params);

            $stmt = $con->query("SELECT * FROM registeredusers WHERE MobileNumber = '$OwnerNumber' and PushNotification != 'off'");
            $no_of_users = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

            if ($no_of_users > 0) {
                while ($row = $stmt->fetch()) {
                    if ($row['Platform'] == "A") {
                        $gcm_array[] = $row['DeviceToken'];
                    } else {
                        $apns_array[] = $row['DeviceToken'];
                    }
                }
                $body = array('gcmText' => $Message, 'pushfrom' => 'CabId_', 'CabId' => $CabId, 'notificationId' => $notificationId);

                if (count($gcm_array) > 0) {
                    $objNotification->setVariables($gcm_array, $body);
                    $objNotification->sendGCMNotification();
                }

                if (count($apns_array) > 0) {
                    $objNotification->setVariables($apns_array, $body);
                    $objNotification->sendIOSNotification();
                }
                echo "success";
            } else {
                echo "no one in database";
            }
        } else {
            echo 'Error';
        }
    } else {
        echo 'Error';
    }
} else {
    echo 'Error';
}

