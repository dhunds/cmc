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

            $Message = 'You got Reward Points worth Rs.' . $offer['amount'] . ' for offering ride in your car.';

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

function checkForOffers ($mobileNumber) {
    global $con;

    $offerCode = '1STFREE';
    $sql = "SELECT o.id, o.amount, o.maxUse, o.maxUsePerUser, o.status FROM offers o JOIN userOffers uo ON o.id = uo.offerId WHERE o.status=1 AND o.validThru > now() AND o.code='".$offerCode."' AND uo.mobileNumber='".$mobileNumber."' AND uo.status=1";
    $stmt = $con->query($sql);
    $found = $con->query("SELECT FOUND_ROWS()")->fetchColumn();


    if ($found > 0) {
        $offer = $stmt->fetch(PDO::FETCH_ASSOC);


        $sql = "SELECT COUNT(id) as useCount FROM availedOffers WHERE offerId=" . $offer['id'] . " AND mobileNumber='" . $mobileNumber . "'";
        $stmt = $con->query($sql);
        $credits = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($credits['useCount'] < $offer['maxUsePerUser']) {
            return $offer;
        } else {
            return false;
        }
    }
}

function attachCouponsToUsers ($offerCode, $mobileNumber) {
    global $con;
    $sql = "SELECT id, amount, maxUse, maxUsePerUser, status FROM offers WHERE status=1 AND validThru > now() AND code='".$offerCode."'";
    $stmt = $con->query($sql);
    $found = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

    if ($found > 0) {
        $offer = $stmt->fetch(PDO::FETCH_ASSOC);

        $sql = "SELECT id FROM userOffers WHERE offerId=" . $offer['id'] . " AND mobileNumber='" . $mobileNumber . "'";
        $con->query($sql);
        $found = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

        if ($found > 0) {
            $msg ="Coupon Already Applied";
        } else {
            $sql = "SELECT id FROM userOffers WHERE mobileNumber='" . $mobileNumber . "'";
            $stmt = $con->query($sql);
            $found = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

            if ($found > 0) {
                $sql = "UPDATE userOffers SET offerId=".$offer['id']." WHERE mobileNumber='" . $mobileNumber . "'";
                $stmt = $con->prepare($sql);
                $stmt->execute();

                $msg = "Coupon Applied";
            } else {
                $sql = "INSERT INTO userOffers(mobileNumber, offerId) VALUES ('$mobileNumber',".$offer['id'].")";
                $nStmt = $con->prepare($sql);
                $nStmt->execute();
                $msg = "Coupon Applied";
            }
        }
    } else {
        $msg = "Either invalid coupon code or coupon expired.";
    }

    return $msg;
}

function checkOfferUseCount(){
    global $con;
    $con->query("SELECT id FROM availedOffers WHERE mobileNumber = '".$mobileNumber."'");
    $offerCount = $con->query("SELECT FOUND_ROWS()")->fetchColumn();
    return $offerCount;
}

function checkOffers ($mobileNumber) {
    global $con;

    $stmt = $con->query("SELECT f.title, f.description, f.terms, f.code FROM userOffers uf JOIN offers f ON uf.offerId = f.id WHERE uf.mobileNumber = '".$mobileNumber."' AND uf.status=1");
    $offerExists = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

    if ($offerExists) {
        $offer = $stmt->fetch(PDO::FETCH_ASSOC);
        return $offer;
    }
    return false;
}