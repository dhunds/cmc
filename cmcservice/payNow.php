<?php
spl_autoload_register(function ($class_name) {
    include 'classes/class.'.strtolower($class_name) . '.php';
});

include('connection.php');
include('includes/functions.php');
include('includes/offers.php');
include('mail.php');

$objNotification = new Notification();

$paymentFailed = 0;
$curlFailed = 0;
$error = 0;

$error = checkPostForBlank (array('sendercell', 'receivercell', 'amount', 'cabId', 'paymentMethod'));

if (!$error) {

    $cabId = $_POST['cabId'];
    $amount = $_POST['amount'];
    $riderCell = $_POST['sendercell'];
    $ownerCell = $_POST['receivercell'];
    $paymentMethod = $_POST['paymentMethod'];
    $riderCellWithPrefix = '0091'.$riderCell;
    $driverCellWithPrefix = '0091'.$ownerCell;
    $isAssociate = 0;
    $serviceCharge = 5;
    $serviceTax = (15 / 100) * 5;
    $totalDeductible = $serviceCharge + $serviceTax;

    $discount = 0;
    $credit = 0;
    $payableByRider = 0;
    $payableByMerchant = $amount - $totalDeductible;
    $paymentStatus = 0;
    $debitFromCredits=0;
    $debitAmount = 0;


    $hasAlreadyPaid = hasAlreadyPaidForTheRide($riderCell, $cabId);

    if (!$hasAlreadyPaid) {

        $rider = getUserByMobileNumber($riderCellWithPrefix);
        $offerDetail = checkForOffers($riderCellWithPrefix);
        $isAssociate = isAssociate($driverCellWithPrefix);
        $credit = $rider ['totalCredits'];

        if ($offerDetail) {
            $discount = $offerDetail['amount'];
        }

        // Amount payable by rider
        if (($discount + $credit) < 1) {
            $payableByRider = $amount;
        } else {
            if ($amount <= $discount || ($amount <= ($discount + $credit))) {
                $payableByRider = 0;
            } else {
                $payableByRider = $amount - ($discount + $credit);
            }
        }

        /* Load payment class */
        $driverWalletDetails = getUserWalletForAcceptingPayment($driverCellWithPrefix);
        $driverWallet = new $driverWalletDetails['name']();
        $riderWallet = new $paymentMethod();

        if ($paymentMethod !='Cash'){

            $riderPaymentStatus = 0;

            if ($payableByRider > 0) {
                $orderId = time() . mt_rand(1, 10000);
                $respRiderPayment = $riderWallet->transferFromUserToMerchant($riderCellWithPrefix, $payableByRider, $orderId, $cabId, $serviceCharge, $serviceTax);
                $riderPaymentStatus = $respRiderPayment['status'];

                if ($respRiderPayment['status'] !='success') {
                    $paymentMethod ='Cash';
                }
            }

            if (($payableByRider == 0 || $riderPaymentStatus =='success') && !$isAssociate) {
                $orderId = time() . mt_rand(1, 10000);
                $respMerchantPayment = $driverWallet->transferFromMerchantToDriver($driverCellWithPrefix, $payableByMerchant, $orderId, $cabId);
            }
        }

        /* When payment method is cash  */

        $orderId = time() . mt_rand(1, 10000);

        if ($paymentMethod =='Cash' && !$isAssociate){

            if ($payableByRider < $amount) {
                $payableByMerchant  = ($amount - $payableByRider) - $totalDeductible;

                if ($payableByMerchant > 0) {
                    $orderId = time() . mt_rand(1, 10000);
                    $respMerchantPayment = $driverWallet->transferFromMerchantToDriver($driverCellWithPrefix, $payableByMerchant, $orderId, $cabId);
                }

            } else {
                $orderId = time() . mt_rand(1, 10000);
                $driverWallet->transferFromUserToMerchant($driverCellWithPrefix, $totalDeductible, $orderId, $cabId, $serviceCharge, $serviceTax);
            }
        }

        // Start logging payments

        if ($paymentMethod =='Cash') {

            $transactionId ='';
            updateBoardedStatus($riderCellWithPrefix, $cabId, 2);
        } else {

            $transactionId = (isset($respMerchantPayment['transactionId']))?$respMerchantPayment['transactionId']:'';
            updateBoardedStatus($riderCellWithPrefix, $cabId, 1);
        }

        logRidePayments ($riderCellWithPrefix, $driverCellWithPrefix, $transactionId, $orderId, $amount, $serviceCharge, $serviceTax, $payableByRider, $payableByMerchant, 2, $cabId);


        /* Debit from riders credits and also update offer status if any offer used */

        if ($discount > 0) {

            updateOfferUsed($riderCellWithPrefix, $offerDetail['id'], $cabId);
        }

        if (($credit > 0) && ($discount < $amount)) {

            if ($credit >= ($amount - $discount)) {

                $debitFromCredits = ($amount - $discount);
            } else {

                $debitFromCredits = $credit;
            }

            $debitAmount = $credit - $debitFromCredits;
            updateCreditUsed($riderCellWithPrefix, $debitFromCredits, $debitAmount, $cabId);
        }

        //Setting response
        $riderProfile = getUserByMobileNumber ($riderCellWithPrefix);

        if ($paymentMethod =='Cash') {

            $Message = "Please take Rs. " . $payableByRider . " in cash from " . $riderProfile['FullName'] . ".";

            $jsonResp = array('code'=>200, 'status' => 'success',  'message' => 'Please pay Rs.' . $payableByRider . ' in cash');
        } else {

            if ($isAssociate){
                $Message = "Payment received from " . $riderProfile['FullName'] . ".";
            } else{
                $Message = "Payment of Rs." . ( $amount - $totalDeductible ). " received from " . $riderProfile['FullName'] . " Please login to your mobikwik account and check in transfers section to accept it.";
            }

            $jsonResp = array('code'=>200, 'status' => "success", 'message' => 'Payment processed. Enjoy your ride!');
        }

        $NotificationType = "Payment_Received";
        sendNotification($driverCellWithPrefix, $NotificationType, $Message, $cabId, $objNotification);


        // Sending payment mail to member
        if ($riderProfile['Email'] != '') {

            $sql = "SELECT date_format(co.ExpStartDateTime, '%M %d, %Y') as TravelDate, co.TravelTime, co.perKmCharge, co.OwnerName, ar. MemberName, ar.MemberLocationAddress, ar.MemberEndLocationAddress, ar.distance FROM cabopen co JOIN acceptedrequest ar ON co.CabId = ar.CabId WHERE co.CabId = '" . $cabId . "' AND ar.MemberNumber='" . $riderCellWithPrefix . "'";
            $stmt = $con->query($sql);
            $cabDetail = $stmt->fetch();

            $stmt = $con->query("SELECT ru.FullName, ru.Email, ui.imagename, v.vehicleModel, uvd.registrationNumber FROM registeredusers ru JOIN userprofileimage ui ON ru.MobileNumber = ui.MobileNumber JOIN userVehicleDetail uvd ON ru.MobileNumber = uvd.mobileNumber JOIN vehicle v ON v.id = uvd.vehicleId WHERE ru.MobileNumber = '" . $driverCellWithPrefix . "'");
            $ownerDetail = $stmt->fetch();

            $rideDetails = array("TravelDate" => $cabDetail['TravelDate'], "TravelTime" => $cabDetail['TravelTime'], "perKmCharge" => $cabDetail['perKmCharge'], "MemberLocationAddress" => $cabDetail['MemberLocationAddress'], "MemberEndLocationAddress" => $cabDetail['MemberEndLocationAddress'], "distance" => $cabDetail['distance'], "amount" => $payableByRider, "discount"=>$discount, "credits"=>$debitFromCredits, "vehicleModel" => $ownerDetail['vehicleModel'], "registrationNumber"=>$ownerDetail['registrationNumber'], "imagename" => $ownerDetail['imagename'], "memberEmail" => $riderProfile['Email'], "OwnerName" => $cabDetail['OwnerName']);

            sendPaymentMailMember($rideDetails);
        }


        setResponse($jsonResp);

    } else {
        setResponse(array("code"=>200, "status"=>"failed", "message"=>"You have already settled this ride."));
    }

} else {
    setResponse(array("code"=>200, "status"=>"failed", "message"=>"Invalid Params."));
}
