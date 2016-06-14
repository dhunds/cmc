<?php
include('connection.php');
include('includes/functions.php');
include_once('classes/class.notification.php');
$objNotification = new Notification();

if (isset($_POST['cabId']) && $_POST['cabId'] !='') {

    $cabId = $_POST['cabId'];

    $sql = "UPDATE cabopen set CabStatus = 'C' where CabId = '" . $cabId . "'";
    $stmt = $con->prepare($sql);
    $res = $stmt->execute();

    $stmt = $con->query("SELECT MobileNumber, CabId, FromShortName, ToShortName, Distance, date_format(ExpStartDateTime, '%M %d, %Y') as TravelDate FROM cabopen WHERE CabId = '" .$cabId . "'");
    $cabDetail = $stmt->fetch(PDO::FETCH_ASSOC);

    $sql = "SELECT rp.amount, rp.serviceCharge, rp.serviceTax, ru.FullName, ru.MobileNumber, ru.DeviceToken, ru.Platform, ru.PushNotification, ar.MemberLocationAddress, ar.MemberEndLocationAddress, ar.distance FROM ridePayments rp JOIN registeredusers ru ON rp.paidBy = ru.MobileNumber JOIN acceptedrequest ar ON ar.MemberNumber = rp.paidBy WHERE rp.cabId='".$cabId."' AND ar.CabId='".$cabId."'";

    $stmt = $con->query($sql);

    $membersJoined = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

    if ($membersJoined) {
        $members = [];
        $totalPaymentReceived = 0;

        while ($row = $stmt->fetch()) {

            $NotificationType = "Cab_Rating";
            $Message = "Please rate your ride from " . $cabDetail['FromShortName'] . " to  " . $cabDetail['ToShortName'] . " .";

            $params = array('NotificationType' => $NotificationType, 'SentMemberName' => 'system', 'SentMemberNumber' => '', 'ReceiveMemberName'=>$row['FullName'], 'ReceiveMemberNumber'=>(string)$row['MobileNumber'], 'Message'=>$Message, 'CabId'=>$cabDetail['CabId'], 'DateTime'=>'now()');

            sendOwnerRatingNotification ($objNotification, $params, $row['DeviceToken'], $row['Platform'], $row['PushNotification']);

            $members[] = $row;

            $totalPaymentReceived = $totalPaymentReceived + ($row['amount'] - ($row['serviceCharge'] + $row['serviceTax']));
        }

        // Send Mail
        $owner = getUserByMobileNumber($cabDetail['MobileNumber']);

        if($owner['Email'] !='') {
            require_once 'mail.php';

            $rideData = array ("FromShortName" =>$cabDetail['FromShortName'], "ToShortName" => $cabDetail['ToShortName'], "TravelDate" => $cabDetail['TravelDate'], "Distance" => $cabDetail['Distance'], "amount" => $totalPaymentReceived);

            $RideDetail = array("ride" =>$rideData, "members"=>$members);

            $subject = "Your Ride Summary: iShareRyde";
            sendPaymentMailOwner ($owner['Email'], $RideDetail, $subject);
        }
        //End Send Mail

        $sql = "UPDATE cabopen set ratenotificationsend = '1' where CabId = '".$cabDetail['CabId']."'";
        $stmt = $con->prepare($sql);
        $stmt->execute();
    }

    setResponse(array("code"=>200, "status"=>"success", "message"=>"Updated sucessfully"));

} else {
    setResponse(array("code"=>200, "status"=>"failed", "message"=>"Invalid Params."));
}
