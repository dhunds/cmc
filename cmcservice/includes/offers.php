<?php

function claimReferalBonus($referralCode, $mobileNumber, $name, $deviceToken, $objNotification)
{
    global $con;
    $resp = '';

    $sql = "SELECT MobileNumber, FullName, DeviceToken FROM registeredusers WHERE referralCode='" . $referralCode . "'";
    $stmt = $con->query($sql);
    $referrer = $stmt->fetch(PDO::FETCH_ASSOC);

    $sql = "SELECT id, amount, maxUse, maxUsePerUser, status FROM offers WHERE status=1 AND validThru > now() AND type='referral'";
    $stmt = $con->query($sql);
    $found = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

    if ($found > 0) {
        $offer = $stmt->fetch(PDO::FETCH_ASSOC);

        //Add credits to User
        $sql = "INSERT INTO credits SET offerId=" . $offer['id'] . ", mobileNumber='" . $mobileNumber . "', credits=" . $offer['amount'] . ", beneficiaryType=2, created=now()";
        $stmt = $con->prepare($sql);
        $stmt->execute();

        //Update total credits
        $sql = "UPDATE registeredusers SET totalCredits = totalCredits +". $offer['amount']." WHERE MobileNumber='".$mobileNumber."'";
        $stmt = $con->prepare($sql);
        $stmt->execute();

        // Send Notification
        $Message = 'You got Reward Points worth Rs.' . $offer['amount'] . ' for joining iShareRyde using a referral code.';
        $body = array('gcmText' => $Message, 'pushfrom' => 'genericnotificationoffers');
        $gcm_array[] = $deviceToken;
        $objNotification->setVariables($gcm_array, $body);
        $objNotification->sendGCMNotification();

        $params = array('NotificationType' => 'Referral_Bonus', 'SentMemberName' => $referrer['FullName'], 'SentMemberNumber' => $referrer['MobileNumber'], 'ReceiveMemberName' => $name, 'ReceiveMemberNumber' => $mobileNumber, 'Message' => $Message, 'DateTime' => 'now()');
        $objNotification->logNotification($params);
        // End Notification

        //Add Credits to Referrer
        $Message = 'You got Reward Points worth Rs.' . $offer['amount'] . '! ' . $name . ' joined iShareRyde with your referral code.';

        $sql = "SELECT COUNT(id) as useCount FROM credits WHERE offerId=" . $offer['id'] . " AND mobileNumber='" . $referrer['MobileNumber'] . "' AND beneficiaryType=1";
        $stmt = $con->query($sql);
        $credits = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($credits['useCount'] < $offer['maxUsePerUser']) {
            $sql = "INSERT INTO credits SET offerId=" . $offer['id'] . ", mobileNumber='" . $referrer['MobileNumber'] . "', credits=" . $offer['amount'] . ", beneficiaryType=1, created=now()";
            $stmt = $con->prepare($sql);
            $stmt->execute();

            $sql = "UPDATE registeredusers SET totalCredits = totalCredits +". $offer['amount']." WHERE MobileNumber='".$referrer['MobileNumber']."'";
            $stmt = $con->prepare($sql);
            $stmt->execute();

            // Send Notification

            $body = array('gcmText' => $Message, 'pushfrom' => 'genericnotificationoffers');
            $gcm_array = array();
            $gcm_array[] = $referrer['DeviceToken'];
            $objNotification->setVariables($gcm_array, $body);
            $objNotification->sendGCMNotification();

            $params = array('NotificationType' => 'Referral_Bonus', 'SentMemberName' => $name, 'SentMemberNumber' => $mobileNumber, 'ReceiveMemberName' => $referrer['FullName'], 'ReceiveMemberNumber' => $referrer['MobileNumber'], 'Message' => $Message, 'DateTime' => 'now()');
            $objNotification->logNotification($params);
            $resp = 'Success';
        }
    } else {
        $resp = 'This offer has expired';
    }

    return $resp;
}

function claimFirstRideBonus($mobileNumber, $objNotification, $isOwner)
{
    global $con;
    $resp = '';

    $sql = "SELECT id, amount, maxUse, maxUsePerUser, status FROM offers WHERE status=1 AND validThru > now() AND type='ridecompletion'";
    $stmt = $con->query($sql);
    $found = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

    if ($found > 0) {
        $offer = $stmt->fetch(PDO::FETCH_ASSOC);
        /*
         // OLD Implementation till 22nd Dec 1015
        if ($isOwner) {
            $sql = "SELECT COUNT(id) as useCount FROM credits WHERE offerId=" . $offer['id'] . " AND mobileNumber='" . $mobileNumber . "' AND beneficiaryType=1";
            $stmt = $con->query($sql);
            $credits = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($credits['useCount'] < $offer['maxUsePerUser']) {
                $sql = "INSERT INTO credits SET offerId=" . $offer['id'] . ", mobileNumber='" . $mobileNumber . "', credits=" . $offer['amount'] . ", beneficiaryType=1, created=now()";
                $stmt = $con->prepare($sql);
                $stmt->execute();
                $resp = 'success';
            }
        } else{
            $sql = "INSERT INTO credits SET offerId=" . $offer['id'] . ", mobileNumber='" . $mobileNumber . "', credits=" . $offer['amount'] . ", beneficiaryType=2, created=now()";
            $stmt = $con->prepare($sql);
            $stmt->execute();
            $resp='success';
        }
        */
        if ($isOwner) {
            $beneficiaryType = 1;
        } else {
            $beneficiaryType = 2;
        }
        $sql = "SELECT COUNT(id) as useCount FROM credits WHERE offerId=" . $offer['id'] . " AND mobileNumber='" . $mobileNumber . "'";
        $stmt = $con->query($sql);
        $credits = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($credits['useCount'] < $offer['maxUsePerUser']) {
            $sql = "INSERT INTO credits SET offerId=" . $offer['id'] . ", mobileNumber='" . $mobileNumber . "', credits=" . $offer['amount'] . ", beneficiaryType=".$beneficiaryType.", created=now()";
            $stmt = $con->prepare($sql);
            $stmt->execute();
            $resp = 'success';
        }

        if ($resp=='success') {
            $sql = "UPDATE registeredusers SET totalCredits = totalCredits +". $offer['amount']." WHERE MobileNumber='".$mobileNumber."'";
            $stmt = $con->prepare($sql);
            $stmt->execute();

            // Send Notification
            $sql = "SELECT FullName, DeviceToken FROM registeredusers WHERE MobileNumber ='".$mobileNumber."'";
            $stmt = $con->query($sql);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($isOwner) {
                $Message = 'You got Reward Points worth Rs.' . $offer['amount'] . ' for completing your first ride.';
            } else {
                $Message = 'You got Reward Points worth Rs.' . $offer['amount'] . ' for completing your ride.';
            }

            $body = array('gcmText' => $Message, 'pushfrom' => 'genericnotificationoffers');
            $gcm_array = array();
            $gcm_array[] = $user['DeviceToken'];
            $objNotification->setVariables($gcm_array, $body);
            $objNotification->sendGCMNotification();

            $params = array('NotificationType' => 'RideCompletionBonus', 'SentMemberName' => 'system', 'SentMemberNumber' => '', 'ReceiveMemberName' => $user['FullName'], 'ReceiveMemberNumber' => $mobileNumber, 'Message' => $Message, 'DateTime' => 'now()');
            $objNotification->logNotification($params);
        }

    }else{
        $resp = 'fail';
    }
    return $resp;
}

function offerCarpoolRideBonus($mobileNumber, $objNotification){
    global $con;
    $resp = '';

    $sql = "SELECT id, amount, maxUse, maxUsePerUser, status FROM offers WHERE status=1 AND validThru > now() AND type='offercarpoolride'";
    $stmt = $con->query($sql);
    $found = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

    if ($found > 0) {
        $offer = $stmt->fetch(PDO::FETCH_ASSOC);


        $sql = "SELECT COUNT(id) as useCount FROM credits WHERE offerId=" . $offer['id'] . " AND mobileNumber='" . $mobileNumber . "'";
        $stmt = $con->query($sql);
        $credits = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($credits['useCount'] < $offer['maxUsePerUser']) {
            $sql = "INSERT INTO credits SET offerId=" . $offer['id'] . ", mobileNumber='" . $mobileNumber . "', credits=" . $offer['amount'] . ", created=now()";
            $stmt = $con->prepare($sql);
            $stmt->execute();
            $resp = 'success';
        }
        if ($resp=='success') {
            $sql = "UPDATE registeredusers SET totalCredits = totalCredits +". $offer['amount']." WHERE MobileNumber='".$mobileNumber."'";
            $stmt = $con->prepare($sql);
            $stmt->execute();

            // Send Notification
            $sql = "SELECT FullName, DeviceToken FROM registeredusers WHERE MobileNumber ='".$mobileNumber."'";
            $stmt = $con->query($sql);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            $Message = 'You got Reward Points worth Rs.' . $offer['amount'] . ' for offering ride ride in your car.';

            $body = array('gcmText' => $Message, 'pushfrom' => 'genericnotificationoffers');
            $gcm_array = array();
            $gcm_array[] = $user['DeviceToken'];
            $objNotification->setVariables($gcm_array, $body);
            $objNotification->sendGCMNotification();

            $params = array('NotificationType' => 'OfferCarpoolBonus', 'SentMemberName' => 'system', 'SentMemberNumber' => '', 'ReceiveMemberName' => $user['FullName'], 'ReceiveMemberNumber' => $mobileNumber, 'Message' => $Message, 'DateTime' => 'now()');
            $objNotification->logNotification($params);
        }
    } else {
        $resp = 'fail';
    }

    return $resp;
}

function joinCarpoolRideBonus($mobileNumber, $objNotification){
    global $con;
    $resp = '';

    $sql = "SELECT id, amount, maxUse, maxUsePerUser, status FROM offers WHERE status=1 AND validThru > now() AND type='joincarpoolride'";
    $stmt = $con->query($sql);
    $found = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

    if ($found > 0) {
        $offer = $stmt->fetch(PDO::FETCH_ASSOC);


        $sql = "SELECT COUNT(id) as useCount FROM credits WHERE offerId=" . $offer['id'] . " AND mobileNumber='" . $mobileNumber . "'";
        $stmt = $con->query($sql);
        $credits = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($credits['useCount'] < $offer['maxUsePerUser']) {
            $sql = "INSERT INTO credits SET offerId=" . $offer['id'] . ", mobileNumber='" . $mobileNumber . "', credits=" . $offer['amount'] . ", created=now()";
            $stmt = $con->prepare($sql);
            $stmt->execute();
            $resp = 'success';
        }
        if ($resp=='success') {
            $sql = "UPDATE registeredusers SET totalCredits = totalCredits +". $offer['amount']." WHERE MobileNumber='".$mobileNumber."'";
            $stmt = $con->prepare($sql);
            $stmt->execute();

            // Send Notification
            $sql = "SELECT FullName, DeviceToken FROM registeredusers WHERE MobileNumber ='".$mobileNumber."'";
            $stmt = $con->query($sql);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            $Message = 'You got Reward Points worth Rs.' . $offer['amount'] . ' for joining carpool ride.';

            $body = array('gcmText' => $Message, 'pushfrom' => 'genericnotificationoffers');
            $gcm_array = array();
            $gcm_array[] = $user['DeviceToken'];
            $objNotification->setVariables($gcm_array, $body);
            $objNotification->sendGCMNotification();

            $params = array('NotificationType' => 'JoinCarpoolBonus', 'SentMemberName' => 'system', 'SentMemberNumber' => '', 'ReceiveMemberName' => $user['FullName'], 'ReceiveMemberNumber' => $mobileNumber, 'Message' => $Message, 'DateTime' => 'now()');
            $objNotification->logNotification($params);
        }
    } else {
        $resp = 'fail';
    }

    return $resp;
}