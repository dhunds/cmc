<?php
include('connection.php');
include('includes/functions.php');
include('includes/offers.php');
include_once('classes/class.notification.php');

$objNotification = new Notification();

$paymentFailed = 0;
$curlFailed = 0;
$error = 0;

$error = checkPostForBlank (array('sendercell', 'receivercell', 'amount', 'cabId'));

if (!$error) {
    //Check User has already paid for this ride
    $hasAlreadyPaid = 0;

    $stmt = $con->query("SELECT *  FROM acceptedrequest WHERE MemberNumber='0091" . $_POST['sendercell'] . "' AND cabId='".$_POST['cabId']."'");
    $memberExists = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

    if ($memberExists) {
        $stmt = $con->query("SELECT id FROM mobikwikTransactions WHERE sender='" . $_POST['sendercell'] . "' AND cabId='".$_POST['cabId']."'");
        $transactionExists = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

        if ($transactionExists) {
            $hasAlreadyPaid = 1;
        }
    }

    if (!$hasAlreadyPaid) {

        $amount = $_POST['amount'];
        $sendercell = $_POST['sendercell'];
        $receivercell = $_POST['receivercell'];
        $fee = 0;
        $comment = 'serviceCharge';
        $msgCode = '503';
        $txntype = 'debit';
        $cabId = $_POST['cabId'];
        $orderid = time() . mt_rand(1, 10000);
        $merchantname = MERCHANT_NAME;
        $mid = MID;
        $merchantTransfer = 1;
        $paidTo = 1;
        $sendercellNew = '0091' . $sendercell;
        $receivercellNew = '0091' . $receivercell;
        $token = getMobikwikToken($sendercellNew);
        $isAssociate = 0;
        $serviceCharge = 5;
        $serviceTax = (14 / 100) * 5;
        $totalDeductible = $serviceCharge + $serviceTax;
        $paidAmount = 0;
        $paymentStatus = '';
        $debitFromCredits=0;

        $transferFromMerchantAccount = 0; // 0-Complete tranfer From member, 1-partialTransfer from member and partial from merchant, 2- Complete Transfer from merchant

        // Check for discounts
        $member = getUserByMobileNumber($sendercellNew);

        $credit = $member ['totalCredits'];
        $offerDetail = checkForOffers($sendercellNew);

        if ($offerDetail) {
            $discount = $offerDetail['amount'];
        } else {
            $discount = 0;
        }
        // End check for discounts

        // Check for associate Number
        $stmt = $con->query("SELECT mobileNumber FROM cabOwners");
        $ownNumbers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (in_array($receivercellNew, $ownNumbers)) {
            $isAssociate = 1;
        }
        // End Check for associate Number

        if (($discount + $credit) < 1) {
            $transferFromMerchantAccount = 0;
        } else {
            if ($amount <= $discount || ($amount <= ($discount + $credit))) {
                $transferFromMerchantAccount = 2;
            } else {
                $transferFromMerchantAccount = 1;
            }
        }
        // Start Transactions

        if ($isAssociate) {   // In Case car owner is associate
            $paidAmount = 0;
            if ($transferFromMerchantAccount == '0') {
                $paidAmount = $amount;
                $respPeerTransfer = mobikwikTransfers($amount, $fee, $merchantname, $mid, $orderid, MERCHANT_NUMBER, $sendercell, $token);
            } elseif ($transferFromMerchantAccount == '1') {
                $paidAmount = $amount - ($discount + $credit);
                $respPeerTransfer = mobikwikTransfers($paidAmount, $fee, $merchantname, $mid, $orderid, MERCHANT_NUMBER, $sendercell, $token);
            }

            if ($transferFromMerchantAccount == '0' || $transferFromMerchantAccount == '1') {

                if ($respPeerTransfer === FALSE) {
                    $paymentStatus = 'failed';
                } else {
                    logMobikwikTransaction($respPeerTransfer->refId, $sendercell, MERCHANT_NUMBER, $paidAmount, $cabId, $respPeerTransfer->status, 3, $serviceCharge, $serviceTax, $respPeerTransfer->statusdescription);

                    if ($respPeerTransfer->status == 'SUCCESS') {
                        mobikwikTokenRegenerate($sendercellNew);
                        $paymentStatus = 'success';
                    } else {
                        $paymentStatus = 'failed';
                    }
                }
            }

            logRidePayment($sendercellNew, $receivercellNew, $amount, $cabId, $paymentStatus, 0.0, 0.0);

        } else {
            if ($transferFromMerchantAccount == '0') {   // When No credits or discount

                $respPeerTransfer = mobikwikTransfers($amount, $fee, $merchantname, $mid, $orderid, $receivercell, $sendercell, $token);

                if ($respPeerTransfer === FALSE) {
                    $paymentStatus = 'failed';
                } else {
                    logMobikwikTransaction($respPeerTransfer->refId, $sendercell, $receivercell, $amount, $cabId, $respPeerTransfer->status, 1, 0.0, 0.0, $respPeerTransfer->statusdescription);

                    if ($respPeerTransfer->status == 'SUCCESS') {
                        mobikwikTokenRegenerate($sendercellNew);

                        $paymentStatus = 'success';
                        // Start Merchant Transaction For Platform Charges
                        $merchantOrderid = time() . mt_rand(1, 10000);
                        $merchantToken = getMobikwikToken($receivercellNew);

                        $merchantResp = merchantTransfer($totalDeductible, $receivercell, $comment, $merchantname, $mid, $msgCode, $merchantOrderid, $merchantToken, $txntype);

                        logMobikwikTransaction($merchantResp->refId, $receivercell, MERCHANT_NUMBER, $totalDeductible, $cabId, $merchantResp->status, 2, $serviceCharge, $serviceTax, $merchantResp->statusdescription);

                        if ($merchantResp->status == 'SUCCESS') {
                            mobikwikTokenRegenerate($receivercellNew);
                        }
                        // Start Merchant Transaction For Platform Charges
                    } else {
                        $paymentStatus = 'failed';
                    }
                }

                logRidePayment($sendercellNew, $receivercellNew, $amount, $cabId, $paymentStatus, $serviceCharge, $serviceTax);

            } elseif ($transferFromMerchantAccount == '1') {

                // Partial Payment By User
                $paidAmount = $amount - ($discount + $credit);
                $respPeerTransfer = mobikwikTransfers($paidAmount, $fee, $merchantname, $mid, $orderid, $receivercell, $sendercell, $token);

                if ($respPeerTransfer === FALSE) {
                    $paymentStatus = 'failed';
                } else {
                    logMobikwikTransaction($respPeerTransfer->refId, $sendercell, $receivercell, $paidAmount, $cabId, $paymentStatus, 1, 0.0, 0.0, $respPeerTransfer->statusdescription);

                    if ($respPeerTransfer->status == 'SUCCESS') {
                        mobikwikTokenRegenerate($sendercellNew);

                        $paymentStatus = 'success';

                        // Partial Payment By Merchant
                        $merchantOrderid = time() . mt_rand(1, 10000);
                        $paidAmount1 = $discount + $credit;
                        $respPeerTransfer1 = mobikwikTransfersFromMerchant($paidAmount1, $merchantname, $mid, $merchantOrderid, $receivercell);
                        logMobikwikTransaction($merchantOrderid, MERCHANT_NUMBER, $receivercell, $paidAmount1, $cabId, $respPeerTransfer1->status, 1, 0.0, 0.0, $respPeerTransfer->statusdescription);

                        // Start Merchant Transaction For Platform Charges
                        $merchantOrderid1 = time() . mt_rand(1, 10000);
                        $merchantToken = getMobikwikToken($receivercellNew);

                        $merchantResp = merchantTransfer($totalDeductible, $receivercell, $comment, $merchantname, $mid, $msgCode, $merchantOrderid, $merchantToken, $txntype);

                        logMobikwikTransaction($merchantResp->refId, $receivercell, MERCHANT_NUMBER, $totalDeductible, $cabId, $merchantResp->status, 2, $serviceCharge, $serviceTax, $merchantResp->statusdescription);
                        if ($merchantResp->status == 'SUCCESS') {
                            mobikwikTokenRegenerate($receivercellNew);
                        }
                        // Start Merchant Transaction For Platform Charges
                    } else {
                        $paymentStatus = 'failed';
                    }
                }

                logRidePayment($sendercellNew, $receivercellNew, $amount, $cabId, $paymentStatus, $serviceCharge, $serviceTax);

            } else if ($transferFromMerchantAccount == '2') {
                $paidAmount = $amount - $totalDeductible;

                $respPeerTransfer = mobikwikTransfersFromMerchant($paidAmount, $merchantname, MID_LOADMONEY, $orderid, $receivercell);

                if ($respPeerTransfer === FALSE) {
                    $paymentStatus = 'failed';
                } else {
                    logMobikwikTransaction($orderid, MERCHANT_NUMBER, $receivercell, $paidAmount, $cabId, $respPeerTransfer->status, 1, 0.0, 0.0, $respPeerTransfer->statusdescription);

                    if ($respPeerTransfer->status == 'SUCCESS') {
                        $paymentStatus = 'success';

                    } else {
                        $paymentStatus = 'failed';
                    }
                }

                logRidePayment($sendercellNew, $receivercellNew, $amount, $cabId, $paymentStatus, $serviceCharge, $serviceTax);
            }

        }

        // Debit credits and discounts from account

        if($paymentStatus = 'success') {
            if (($credit + $discount) > 0) {

                if ($discount > 0) {
                    $sql = "INSERT INTO availedOffers(mobileNumber, offerId) VALUES ('$sendercellNew', '" . $offerDetail['id'] . "')";
                    $stmt = $con->prepare($sql);
                    $stmt->execute();
                }
                if (($credit > 0) && ($discount < $amount)) {
                    if ($credit >= ($amount - $discount)) {
                        $debitFromCredits = ($amount - $discount);
                    } else {
                        $debitFromCredits = $credit;
                    }
                    $debitAmount = $credit - $debitFromCredits;

                    $sql = "UPDATE registeredusers set 	totalCredits = '" . $debitAmount . "' where MobileNumber = '" . $sendercellNew . "'";
                    $stmt = $con->prepare($sql);
                    $stmt->execute();
                }

            }
        }

        // End Debit credits and discounts from account

        $stmt = $con->query("SELECT ru.FullName, ru.Email, ui.imagename, v.vehicleModel FROM registeredusers ru JOIN userprofileimage ui ON ru.MobileNumber = ui.MobileNumber JOIN userVehicleDetail uvd ON ru.MobileNumber = uvd.mobileNumber JOIN vehicle v ON v.id = uvd.vehicleId WHERE ru.MobileNumber = '" . $sendercellNew . "'");
        $memberDetail = $stmt->fetch();

        if ($paymentStatus == 'success') {

            $jsonResp = array('status' => "success", 'statuscode' => (string)$respPeerTransfer->statuscode, 'statusdescription' => (string)$respPeerTransfer->statusdescription, 'amount' => (string)$respPeerTransfer->amount, 'orderid' => (string)$respPeerTransfer->orderid, 'refId' => (string)$respPeerTransfer->refId, 'checksum' => (string)$respPeerTransfer->checksum, 'message' => 'Payment processed. Enjoy your ride!');

            //Send Notification
            $stmt = $con->query("SELECT MobileNumber, FullName, DeviceToken, Platform FROM registeredusers WHERE MobileNumber = '$receivercellNew'");
            $receiverExists = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

            if ($receiverExists) {
                $row = $stmt->fetch();

                $receiverName = $row['FullName'];
                $receiverMobileNumber = $row['MobileNumber'];
                $receiverPlatform = $row['Platform'];
                $receiverDeviceToken = $row['DeviceToken'];

                $NotificationType = "Payment_Received";
                $Message = "Payment of Rs." . $amount . " received from " . $memberDetail['FullName'] . " Please login to your mobikwik account and check in transfers section to accept it.";

                $paramsReceiver = array('NotificationType' => $NotificationType, 'SentMemberName' => 'system', 'SentMemberNumber' => '', 'ReceiveMemberName' => $receiverName, 'ReceiveMemberNumber' => $receiverMobileNumber, 'Message' => $Message, 'CabId' => $_POST['cabId'], 'DateTime' => 'now()');

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
            // End Sending Notification

            // Send Payment Email to member

            if ($memberDetail['Email'] != '') {
                require_once 'mail.php';

                $sql = "SELECT date_format(co.ExpStartDateTime, '%M %d, %Y') as TravelDate, co.TravelTime, co.perKmCharge, co.OwnerName, ar. MemberName, ar.MemberLocationAddress, ar.MemberEndLocationAddress, ar.distance, pl.amount FROM cabopen co JOIN acceptedrequest ar ON co.CabId = ar.CabId JOIN paymentLogs pl ON co.CabId = pl.cabId
    WHERE co.CabId = '" . $_POST['cabId'] . "' AND ar.MemberNumber='" . $sendercellNew . "'";

                $stmt = $con->query($sql);
                $cabDetail = $stmt->fetch();

                $stmt = $con->query("SELECT ru.FullName, ru.Email, ui.imagename, v.vehicleModel, uvd.registrationNumber FROM registeredusers ru JOIN userprofileimage ui ON ru.MobileNumber = ui.MobileNumber JOIN userVehicleDetail uvd ON ru.MobileNumber = uvd.mobileNumber JOIN vehicle v ON v.id = uvd.vehicleId WHERE ru.MobileNumber = '" . $receivercellNew . "'");
                $ownerDetail = $stmt->fetch();

                //$paidAmountByUSer = $amount$debitFromCredits

                $rideDetails = array("TravelDate" => $cabDetail['TravelDate'], "TravelTime" => $cabDetail['TravelTime'], "perKmCharge" => $cabDetail['perKmCharge'], "MemberLocationAddress" => $cabDetail['MemberLocationAddress'], "MemberEndLocationAddress" => $cabDetail['MemberEndLocationAddress'], "distance" => $cabDetail['distance'], "amount" => $cabDetail['amount'], "discount"=>$discount, "credits"=>$debitFromCredits, "vehicleModel" => $ownerDetail['vehicleModel'], "registrationNumber"=>$ownerDetail['registrationNumber'], "imagename" => $ownerDetail['imagename'], "memberEmail" => $memberDetail['Email'], "OwnerName" => $cabDetail['OwnerName']);

                sendPaymentMailMember($rideDetails);

            }

            // End Sending Payment Email to member

        } else {

            //Send Notification
            $stmt = $con->query("SELECT MobileNumber, FullName, DeviceToken, Platform FROM registeredusers WHERE MobileNumber = '$receivercellNew'");
            $receiverExists = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

            if ($receiverExists) {
                $row = $stmt->fetch();

                $receiverName = $row['FullName'];
                $receiverMobileNumber = $row['MobileNumber'];
                $receiverPlatform = $row['Platform'];
                $receiverDeviceToken = $row['DeviceToken'];

                $NotificationType = "Payment_Received";
                $Message = "Payment error, please take Rs. " . $amount . " in cash from " . $memberDetail['FullName'] . ".";

                $paramsReceiver = array('NotificationType' => $NotificationType, 'SentMemberName' => 'system', 'SentMemberNumber' => '', 'ReceiveMemberName' => $receiverName, 'ReceiveMemberNumber' => $receiverMobileNumber, 'Message' => $Message, 'CabId' => $_POST['cabId'], 'DateTime' => 'now()');

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
            // End Sending Notification

            $jsonResp = array('status' => 'fail', 'statuscode' => (string)$respPeerTransfer->statuscode, 'statusdescription' => (string)$respPeerTransfer->statusdescription, 'message' => 'Payment failed, please settle Rs.' . $amount . ' in cash');
        }

        if ($jsonResp['status'] == 'success') {
            $sql = "UPDATE acceptedrequest set hasBoarded = 1 where CabId = '" . $_POST['cabId'] . "'";
        } else {
            $sql = "UPDATE acceptedrequest set hasBoarded = 2 where CabId = '" . $_POST['cabId'] . "'";
        }

        $stmt = $con->prepare($sql);
        $res = $stmt->execute();

        http_response_code(200);
        header('Content-Type: application/json');
        echo json_encode($jsonResp);
        exit;
    } else {
        http_response_code(200);
        header('Content-Type: application/json');
        echo '{"status":"failed", "message":"You have already settled this ride."}';
        exit;
    }
} else {
    http_response_code(200);
    header('Content-Type: application/json');
    echo '{"status":"failed", "message":"Invalid Params."}';
    exit;
}