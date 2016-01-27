<?php

/**
 * @param string $con
 * @param Array $arrMembersNumberAndFare
 * @param Double $totalFare
 * @param string $cabId
 * @return Boolean
 */
function save_split_fare($con, $arrMembersNumberAndFare, $totalFare, $cabId, $paidBy, $owner) {
    global $objNotification;

    $sql = "UPDATE cabopen SET totalFare =".$totalFare.", paidBy = '".$paidBy."' WHERE
            CabId = '".$cabId."'";

    $stmt = $con->prepare($sql);
    $stmt->execute();

    foreach ($arrMembersNumberAndFare as $val) {
        $val = preg_replace( '/[^[:print:]]/', '',$val);
        list($memberNumber, $fareToPay) = explode('~', $val);

        if(trim($memberNumber)==trim($paidBy)){
            $settled = 1;
        }else{
            $settled = 0;
        }
        if ($memberNumber==$owner){
            $sql = "UPDATE cabopen SET fareToPay = ".$fareToPay.", settled=".$settled." WHERE
            CabId = '".$cabId."' AND trim(MobileNumber) = '".$memberNumber."'";
        }else{
            $sql = "UPDATE cabmembers SET fareToPay = ".$fareToPay.", settled=".$settled." WHERE
            CabId = '".$cabId."' AND trim(MemberNumber) = '".$memberNumber."'";
        }

        $stmt = $con->prepare($sql);
        $stmt->execute();

    //Update Cab Status
        $sql = "UPDATE cabopen set status=3 where CabId = '" . $cabId . "'";
        $stmt = $con->prepare($sql);
        $res = $stmt->execute();

    //Send Notification
        $sql = "SELECT FullName FROM registeredusers WHERE trim(MobileNumber) = '".$paidBy."'";
        $stmt = $con->prepare($sql);
        $stmt->execute();
        $user = $stmt->fetch();

        $notificationMessage = 'You need to pay Rs. '.$fareToPay.' for your ride. Click here for payment options.';

        $stmt = $con->query("select FullName, Platform, MobileNumber, DeviceToken FROM registeredusers WHERE trim(MobileNumber)='".$memberNumber."'");
        $noOfUsers = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

        if ($noOfUsers > 0) {
            while ($row = $stmt->fetch()) {
                $gcm_array = [];
                $FriendPlatform = $row['Platform'];
                if ($FriendPlatform == "A" && trim($row['MobileNumber']) !=$paidBy)
                {
                    $MemberName=$row['FullName'];
                    $MemberNumber=$row['MobileNumber'];

                    $sql = "INSERT INTO notifications(NotificationType,SentMemberName, SentMemberNumber,
                    ReceiveMemberName, ReceiveMemberNumber, Message, CabId, DateTime) VALUES ('tripcompleted','System','','$MemberName', '$MemberNumber','$notificationMessage', '$cabId', now())";
                    $nStmt = $con->prepare($sql);
                    $nStmt->execute();
                    $notificationId = $con->lastInsertId();

                    $body = array('gcmText' => $notificationMessage, 'pushfrom' => 'tripcompleted', 'notificationId' => $notificationId, 'CabId' => $cabId);

                    if ($row['PushNotification'] !='off') {
                        $gcm_array[]= $row['DeviceToken'];
                        $objNotification->setVariables($gcm_array, $body);
                        $objNotification->sendGCMNotification();
                        $res = true;
                    }
                }
            }
        }
    }

    if ($res === true) {
        return true;
    }else{
        return false;
    }
}

function randomString($length = 6)
{
    $str = "";
    $characters = range('a', 'z');
    $max = count($characters) - 1;
    for ($i = 0; $i < $length; $i++) {
        $rand = mt_rand(0, $max);
        $str .= $characters[$rand];
    }
    return $str;
}

function isReferralCodeUnique($code)
{
    global $con;
    $sql = "SELECT MobileNumber FROM registeredusers WHERE referralCode='" . $code . "'";
    $con->query($sql);
    $found = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

    if ($found > 0) {
        return false;
    } else {
        return true;
    }
}

function transferMoney($fields, $merchantURL) {
	$strParams = http_build_query($fields);
	$url = $merchantURL . '?' . $strParams;

	$curl = curl_init();
	curl_setopt_array($curl, array(
		CURLOPT_RETURNTRANSFER => 1,
		CURLOPT_URL => $url,
		CURLOPT_HTTPHEADER => array('Content-Type: application/json')
	));
	$result = curl_exec($curl);
	curl_close($curl);

	return $result;
}

function logTransaction(
	$cell,
	$amount,
	$comment,
	$sender,
	$Message,
	$token,
	$objNotification,
	$NotificationType,
	$senderName,
	$MemberName,
	$MemberNumber,
	$resp
) {
	global $con;

	$cell = '0091' . trim($cell);
	$sql = '';
	if ($resp->status == 'SUCCESS') {
		$sql = "INSERT INTO payments(mobileNumber, amount, comment, sender, DateTime) VALUES ('" . $cell . "', '" . $amount . "', '" . $comment . "', '$sender', now())";
	}
	elseif ($resp->status == 'FAILURE' && $resp->statuscode == '120') {
		$sql = "INSERT INTO payments(mobileNumber, amount, comment, sender, DateTime, status) VALUES ('" . $cell . "', '" . $amount . "','" . $comment . "', '$sender', now(), 0)";
	}
	if ($sql != '') {
		$stmt = $con->prepare($sql);
		$stmt->execute();
	}

	if ($resp->status == 'SUCCESS') {

		$body = array('gcmText' => $Message, 'pushfrom' => 'genericnotification');
		$gcm_array[] = $token;
		$objNotification->setVariables($gcm_array, $body);
		$objNotification->sendGCMNotification();

		$sql = "INSERT INTO notifications(NotificationType, SentMemberName, SentMemberNumber, ReceiveMemberName, ReceiveMemberNumber, Message, DateTime) VALUES ('$NotificationType','$senderName','$sender','$MemberName','$MemberNumber','$Message',now())";
		$stmt = $con->prepare($sql);
		$resp = $stmt->execute();

		return $resp;
	}
	else {
		return true;
	}
}

function createClub($ownerNumber, $poolName){
	global $con;

	$sql = "SELECT PoolId FROM userpoolsmaster WHERE OwnerNumber='" . $ownerNumber . "' AND PoolName='".$poolName."'";
	$stmt = $con->query($sql);
	$found = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

	if ($found > 0) {
		$pool = $stmt->fetch(PDO::FETCH_ASSOC);
		return $pool['PoolId'];
	} else {
		$sql = "INSERT INTO userpoolsmaster(OwnerNumber, PoolName, Active) VALUES ('$ownerNumber', '$poolName','1')";
		$stmt = $con->prepare($sql);
		$stmt->execute();
		$insertedId = $con->lastInsertId();
		return $insertedId;
	}
}

function addToClub($poolId, $memberName, $memberNumber){
	global $con;

	$sql = "SELECT PoolSubId FROM userpoolsslave WHERE MemberNumber='" . $memberNumber . "' AND PoolId='".$poolId."'";
	$stmt = $con->query($sql);
	$found = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

	if ($found > 0) {
		$pool = $stmt->fetch(PDO::FETCH_ASSOC);
		return $pool['PoolSubId'];
	} else {
		$sql = "INSERT INTO userpoolsslave(PoolId, MemberName, MemberNumber, IsActive) VALUES ('" . $poolId . "', '" . $memberName . "','" . $memberNumber . "', '1')";
		$stmt = $con->prepare($sql);
		$resp = $stmt->execute();
		return $resp;
	}
}

