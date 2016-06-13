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
        if (trim($fareToPay) !='0') {
            $sql = "SELECT FullName FROM registeredusers WHERE trim(MobileNumber) = '" . $paidBy . "'";
            $stmt = $con->prepare($sql);
            $stmt->execute();
            $user = $stmt->fetch();

            $notificationMessage = 'You need to pay Rs. ' . $fareToPay . ' for your ride. Click here for payment options.';

            $stmt = $con->query("select FullName, Platform, MobileNumber, DeviceToken FROM registeredusers WHERE trim(MobileNumber)='" . $memberNumber . "'");
            $noOfUsers = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

            if ($noOfUsers > 0) {
                while ($row = $stmt->fetch()) {
                    $gcm_array = [];
                    $FriendPlatform = $row['Platform'];
                    if ($FriendPlatform == "A" && trim($row['MobileNumber']) != $paidBy) {
                        $MemberName = $row['FullName'];
                        $MemberNumber = $row['MobileNumber'];

                        $sql = "INSERT INTO notifications(NotificationType,SentMemberName, SentMemberNumber,
                        ReceiveMemberName, ReceiveMemberNumber, Message, CabId, DateTime) VALUES ('tripcompleted','System','','$MemberName', '$MemberNumber','$notificationMessage', '$cabId', now())";
                        $nStmt = $con->prepare($sql);
                        $nStmt->execute();
                        $notificationId = $con->lastInsertId();

                        $body = array('gcmText' => $notificationMessage, 'pushfrom' => 'tripcompleted', 'notificationId' => $notificationId, 'CabId' => $cabId);

                        if ($row['PushNotification'] != 'off') {
                            $gcm_array[] = $row['DeviceToken'];
                            $objNotification->setVariables($gcm_array, $body);
                            $objNotification->sendGCMNotification();
                            $res = true;
                        }
                    }
                }
            }
        } else{
            //Update user settled status if he own Rs. 0 and also check if all users settled fare then archive ride.
            if ($memberNumber==$owner){
                $sql = "UPDATE cabopen SET settled=1 WHERE CabId = '".$cabId."' AND trim(MobileNumber) = '".$memberNumber."'";
            } else {
                $sql = "UPDATE cabmembers SET settled=1 WHERE CabId = '".$cabId."' AND trim(MemberNumber) = '".$memberNumber."'";
            }

            $stmt = $con->prepare($sql);
            $res = $stmt->execute();

            // If All members done fare settlement mark trip as Archived
            $con->query("select MemberNumber FROM cabmembers where CabId = '".$cabId."' AND settled !=1");
            $foundRows = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

            $con->query("select MobileNumber FROM cabopen where CabId = '".$cabId."' AND settled !=1");
            $foundRows1 = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

            if($foundRows < 1 && $foundRows1 < 1){
                $sql = "UPDATE cabopen set CabStatus = 'I' where CabId = '" . $cabId . "'";
                $stmt = $con->prepare($sql);
                $res = $stmt->execute();
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

function mobikwikTransfers ($amount, $fee, $merchantname, $mid, $orderid, $receivercell, $sendercell, $token) {

    $string = "'".$amount ."''". $fee ."''". $merchantname ."''". $mid ."''". $orderid ."''". $receivercell ."''". $sendercell ."''". $token ."'";

    //echo $string;die;

    $checksum = hash_hmac('sha256', $string, API_SECRET);

    $fields = array('amount' => $amount, 'fee' => $fee, 'merchantname' => $merchantname, 'mid' => $mid, 'orderid' => $orderid, 'receivercell' => $receivercell, 'sendercell' => $sendercell, 'token' => $token, 'checksum' => $checksum);

    $strParams = http_build_query($fields);
    $url = PEER_TRANSFER_URL . '?' . $strParams;
        
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => $url,
        CURLOPT_HTTPHEADER => array('Content-Type: application/json')
    ));
    $result = curl_exec($curl);
    curl_close($curl);

    if ($result === FALSE) {
        return FALSE;
    } else {
        $resp = simplexml_load_string($result);

        if ((string)$resp->statuscode == '199') {
            $mobileNumber = '0091'.$sendercell;
            $tokenResp = simplexml_load_string(mobikwikTokenRegenerate($mobileNumber));

            if($tokenResp->status == 'SUCCESS') {
                mobikwikTransfers($amount, $fee, $merchantname, $mid, $orderid, $receivercell, $sendercell, (string)$tokenResp->token);
            }
        }
        return $resp;
    }
}

function mobikwikTransfersFromMerchant($amount, $merchantName, $merchantId, $orderId, $cell){
    $creditMethod = 'cashback';
    $typeOfMoney = 0;

    $string = "'".$amount ."''". $cell ."''". $creditMethod ."''". $merchantName ."''". $merchantId ."''". $orderId ."''". $typeOfMoney ."''". WALLET_ID."'";

    $checksum = hash_hmac('sha256', $string, API_SECRET);

    $fields = array('amount' => $amount, 'typeofmoney' => $typeOfMoney, 'cell' => $cell, 'orderid' => $orderId, 'creditmethod' => $creditMethod, 'walletid' => WALLET_ID, 'mid' => $merchantId, 'merchantname' => $merchantName, 'checksum' => $checksum);

    $strParams = http_build_query($fields);
    $url = LOAD_MONEY_URL . '?' . $strParams;
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => $url,
        CURLOPT_HTTPHEADER => array('Content-Type: application/json')
    ));
    $result = curl_exec($curl);
    curl_close($curl);

    if ($result === FALSE) {
        return FALSE;
    } else {
        $resp = simplexml_load_string($result);
        return $resp;
    }
}

function merchantTransfer($amount, $cell, $comment, $merchantname, $mid, $msgCode, $orderid, $token, $txntype) {

    $string = "'".$amount ."''". $cell ."''". $comment ."''". $merchantname ."''". $mid ."''". $msgCode ."''". $orderid ."''". $token ."''". $txntype ."'";
    $checksum = hash_hmac('sha256', $string, API_SECRET);

    $fields = array('amount' => $amount, 'cell' => $cell, 'comment' => $comment, 'merchantname' => $merchantname, 'mid' => $mid, 'msgcode' =>$msgCode, 'orderid' => $orderid, 'token' => $token,  'txntype' => $txntype, 'checksum' => $checksum);

    $strParams = http_build_query($fields);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, DEBIT_API);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $strParams);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec ($ch);
    curl_close ($ch);

    if ($result === FALSE) {
        return FALSE;
    } else {
        $resp = simplexml_load_string($result);

        if ((string)$resp->statuscode == '199') {
            $mobileNumber = '0091'.$cell;
            $tokenResp = simplexml_load_string(mobikwikTokenRegenerate($mobileNumber));

            if($tokenResp->status == 'SUCCESS') {
                merchantTransfer ($amount, $cell, $comment, $merchantname, $mid, $msgCode, $orderid, $token, $txntype);
            }
        }
        return $resp;
    }
}

function checkPostForBlank($arrParams){
    $error = 0;
    foreach ($arrParams as $value) {
        if (!isset($_POST[$value]) || $_POST[$value] =='') {
            $error = 1;
        }
    }
    return $error;
}

function sendOwnerRatingNotification ($objNotification, $params, $deviceToken, $Platform, $pushNotification) {

    $gcm_array = array();
    $apns_array = array();

    $notificationId = $objNotification->logNotification($params);

    if ($pushNotification !='off') {
        $body = array('gcmText' => $RateNotificationMessage, 'pushfrom' => 'Cab_Rating', 'notificationId' => $notificationId);

        if ($Platform == "A") {
            $gcm_array[] = $deviceToken;
            $objNotification->setVariables($gcm_arrayF, $body);
            $objNotification->sendGCMNotification();
        } else {
            $apns_array[] = $deviceToken;
            $objNotification->setVariables($apns_array, $body);
            $objNotification->sendIOSNotification();
        }
    }
}

function mobikwikTokenRegenerate ($mobileNumber) {
    global $con;

    $stmt = $con->query("SELECT MobileNumber, mobikwikToken FROM registeredusers WHERE MobileNumber = '".$mobileNumber."'");
    $userExists = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

    if ($userExists) {

        $user = $stmt->fetch();

        $mobileNumber = substr(trim($user['MobileNumber']), -10);
        $msgCode = 507;
        $tokenType = 1;

        $string = "'".$mobileNumber ."''". MERCHANT_NAME ."''". MID ."''". $msgCode ."''". $user['mobikwikToken'] ."''". $tokenType."'";

        $checksum = hash_hmac('sha256', $string, MOBIKWIK_TOKEN_REGENERATE_KEY);

        $fields = array('cell' => $mobileNumber, 'token' => $user['mobikwikToken'], 'tokentype' => $tokenType, 'msgcode' => $msgCode, 'mid' => MID, 'merchantname' => MERCHANT_NAME, 'checksum' => $checksum);

        $strParams = http_build_query($fields);
        $url = TOKEN_REGENERATE_URL . '?' . $strParams;

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $url,
            CURLOPT_HTTPHEADER => array('Content-Type: application/json')
        ));
        $result = curl_exec($curl);
        curl_close($curl);

        $resp = simplexml_load_string($result);

        if ($resp->status =='SUCCESS'){
            saveMobikwikToken($user['MobileNumber'], (string)$resp->token);
        }

        return $resp;
    }

    return false;
}

function checkMobikwikWalletBalance ($mobileNumber) {
    global $con;

    $stmt = $con->query("SELECT MobileNumber, mobikwikToken FROM registeredusers WHERE MobileNumber = '".$mobileNumber."'");
    $userExists = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

    if ($userExists) {
        $user = $stmt->fetch();

        $mobileNumber = substr(trim($user['MobileNumber']), -10);
        $msgCode = 501;

        $string = "'".$mobileNumber ."''". MERCHANT_NAME ."''". MID ."''". $msgCode ."''". $user['mobikwikToken'] ."'";

        $checksum = hash_hmac('sha256', $string, API_SECRET);

        $fields = array('cell' => $mobileNumber, 'token' => $user['mobikwikToken'],  'msgcode' => $msgCode, 'mid' => MID, 'merchantname' => MERCHANT_NAME, 'checksum' => $checksum);

        $strParams = http_build_query($fields);
        $url = MOBIKWIK_BALANCE_CHECK_URL . '?' . $strParams;

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $url,
            CURLOPT_HTTPHEADER => array('Content-Type: application/json')
        ));
        $result = curl_exec($curl);
        curl_close($curl);

        $resp = simplexml_load_string($result);

        if ($resp->status =='SUCCESS'){

            mobikwikTokenRegenerate($user['MobileNumber']);

        } else if ((string)$resp->statuscode == '199') {

            $tokenResp = simplexml_load_string(mobikwikTokenRegenerate($mobileNumber));

            if($tokenResp->status == 'SUCCESS') {
                checkMobikwikWalletBalance($mobileNumber);
            }
        }

        return $resp;
    }

    return false;
}

function saveMobikwikToken ($mobileNumber, $token) {
    global $con;

    if ($mobileNumber !='' && $token !=''){
        $sql = "UPDATE registeredusers set mobikwikToken = '" . $token . "' where MobileNumber = '" . $mobileNumber . "'";
        $stmt = $con->prepare($sql);
        $stmt->execute();
        return true;
    } else {
        return false;
    }
}

function getMobikwikToken ($mobileNumber) {
    global $con;

    $stmt = $con->query("SELECT mobikwikToken FROM registeredusers WHERE MobileNumber = '".$mobileNumber."'");
    $userExists = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

    if ($userExists) {
        $user = $stmt->fetch();
        return $user['mobikwikToken'];
    }
    return false;
}

function getUserByMobileNumber ($mobileNumber) {
    global $con;

    $stmt = $con->query("SELECT * FROM registeredusers WHERE MobileNumber = '".$mobileNumber."'");
    $userExists = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

    if ($userExists) {
        $user = $stmt->fetch();
        return $user;
    }
    return false;
}

function logMobikwikTransaction ($transactionId, $sender, $receiver, $amount, $cabId, $status, $transactionType, $serviceCharge=0.0, $serviceTax=0.0, $description='') {
    global $con;;
    $sql = "INSERT INTO mobikwikTransactions(transactionId, transactionDate, sender, receiver, amount, serviceCharge, serviceTax, transactionType, cabId, status, description) VALUES ('$transactionId', now(), '$sender','$receiver', $amount, $serviceCharge, $serviceTax, $transactionType, '$cabId', '$status', '$description')";
    $stmt = $con->prepare($sql);
    $stmt->execute();
    $insertedId = $con->lastInsertId();
    return $insertedId;
}

function logRidePayment ($sender, $receiver, $amount, $cabId, $status, $serviceCharge, $serviceTax) {
    global $con;
    $sql = "INSERT INTO paymentLogs(mobileNumberFrom, mobileNumberTo, amount, serviceCharge, serviceTax, transactionDate, cabId, status) VALUES ('$sender', '$receiver', $amount, $serviceCharge, $serviceTax, now(), '$cabId', '$status')";
    $stmt = $con->prepare($sql);
    $stmt->execute();
    $insertedId = $con->lastInsertId();
    return $insertedId;
}

function logRidePayments ($paidBy, $paidTo, $transactionId, $orderId, $amount, $serviceCharge, $serviceTax, $payableByRider, $payableByMerchant, $walletId, $cabId) {
    global $con;
    $sql = "INSERT INTO ridePayments(paidBy, paidTo, transactionId, orderId, amount, serviceCharge, serviceTax, amountPaidByRider, amountPaidByMerchant, walletId, cabId) VALUES ('$paidBy', '$paidTo', '$transactionId', '$orderId', $amount, $serviceCharge, $serviceTax, $payableByRider, $payableByMerchant, $walletId, '$cabId')";
    $stmt = $con->prepare($sql);
    $stmt->execute();
    $insertedId = $con->lastInsertId();
    return $insertedId;
}

function hasAlreadyPaidForTheRide($mobileNumber, $cabId) {
    global $con;

    $mobileNumber = '0091'.substr(trim($mobileNumber), -10);

    $con->query("SELECT id FROM ridePayments WHERE 	paidBy='" . $mobileNumber . "' AND cabId='".$cabId."'");
    $transactionExists = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

    if ($transactionExists) {
        return true;
    }

    return false;
}

function isAssociate($mobileNumber) {
    global $con;

    $con->query("SELECT mobileNumber FROM cabOwners WHERE mobileNumber='" . $mobileNumber . "'");
    $isAssociate = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

    if ($isAssociate) {
        return true;
    }

    return false;
}

function getUserWalletForAcceptingPayment($mobileNumber) {
    global $con;

    $stmt = $con->query("SELECT po.id, po.name FROM  registeredusers ru JOIN paymentOptions po  ON po.id = ru.defaultPaymentAcceptOption WHERE ru.MobileNumber='" . $mobileNumber . "'");
    $wallet = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

    if ($wallet) {
        $walletDetail = $stmt->fetch(PDO::FETCH_ASSOC);
        return $walletDetail;
    }
    return false;
}

function updateOfferUsed($mobileNumber, $offerId, $cabId) {
    global $con;

    $sql = "INSERT INTO availedOffers(mobileNumber, offerId, cabId) VALUES ('$mobileNumber', '$offerId', '$cabId')";
    $stmt = $con->prepare($sql);
    if ($stmt->execute()) {
        return true;
    }
    return false;
}

function updateCreditUsed($mobileNumber, $debitFromCredits, $debitAmount, $cabId) {
    global $con;

    $sql = "UPDATE registeredusers set 	totalCredits = '$debitAmount' WHERE MobileNumber = '$mobileNumber'";
    $stmt = $con->prepare($sql);
    if ($stmt->execute()) {
        $sql = "INSERT INTO usedCredits(mobileNumber, amount, cabId) VALUES ('$mobileNumber', '$debitFromCredits', '$cabId')";
        $stmt = $con->prepare($sql);
        $stmt->execute();
        return true;
    }
    return false;
}

function updateBoardedStatus($mobileNumber, $cabId, $hasBoarded)
{
    global $con;

    $sql = "UPDATE acceptedrequest set hasBoarded = '$hasBoarded' where CabId = '$cabId' AND MemberNumber='$mobileNumber'";
    $stmt = $con->prepare($sql);

    if ($stmt->execute()) {
        return true;
    }
    return false;
}

function sendNotification($mobileNumber, $NotificationType, $Message, $cabId, $objNotification)
{
    global $con;

    $stmt = $con->query("SELECT MobileNumber, FullName, DeviceToken, Platform FROM registeredusers WHERE MobileNumber = '$mobileNumber'");
    $userExists = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

    if ($userExists) {
        $row = $stmt->fetch();

        $receiverName = $row['FullName'];
        $receiverMobileNumber = $row['MobileNumber'];
        $receiverPlatform = $row['Platform'];
        $receiverDeviceToken = $row['DeviceToken'];

        $paramsReceiver = array('NotificationType' => $NotificationType, 'SentMemberName' => 'system', 'SentMemberNumber' => '', 'ReceiveMemberName' => $receiverName, 'ReceiveMemberNumber' => $receiverMobileNumber, 'Message' => $Message, 'CabId' => $cabId, 'DateTime' => 'now()');

        $notificationId = $objNotification->logNotification($paramsReceiver);

        $body = array('gcmText' => $Message, 'pushfrom' => $NotificationType, 'notificationId' => $notificationId);

        if ($receiverPlatform == 'A') {
            $gcm_array = [];
            $gcm_array[] = $receiverDeviceToken;
            $objNotification->setVariables($gcm_array, $body);
            $res = $objNotification->sendGCMNotification();
        } else {
            $apns_array = [];
            $apns_array[] = $receiverDeviceToken;
            $objNotification->setVariables($apns_array, $body);
            $objNotification->sendIOSNotification();
        }
    }
}



function setResponse($args){
    $code = $args['code'];
    unset($args['code']);

    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($args);
    exit;
}
