<?php
include('connection.php');
include('includes/functions.php');
include_once('classes/class.notification.php');
$objNotification = new Notification();

if (isset($_POST['cabId']) && $_POST['cabId'] !='') {

    $sql = "UPDATE cabopen set CabStatus = 'C' where CabId = '" . $_POST['cabId'] . "'";
    $stmt = $con->prepare($sql);
    $res = $stmt->execute();

    $stmt = $con->query("SELECT MobileNumber, CabId, FromShortName, ToShortName, Distance, date_format(ExpStartDateTime, '%M %d, %Y') as TravelDate FROM cabopen WHERE CabId = '" . $_POST['cabId'] . "'");
    $cabDetail = $stmt->fetch();

    $stmt = $con->query("SELECT ru.FullName, ru.MobileNumber, ru.DeviceToken, ar. MemberName, ar.MemberLocationAddress, ar.MemberEndLocationAddress, ar.distance, pl.amount, pl.serviceCharge, pl.serviceTax FROM registeredusers ru JOIN acceptedrequest ar ON a.MobileNumber = b.MemberNumber
        JOIN paymentLogs pl ON ar.MemberNumber = pl.mobileNumberFrom WHERE b.CabId='".$_POST['cabId']."' AND b.hasBoarded=1");

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

            $totalPaymentReceived = $totalPaymentReceived + $row['amount'];
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

    http_response_code(200);
    header('Content-Type: application/json');
    echo '{status:"success", message:"Updated sucessfully"}';
    exit;

} else {
    http_response_code(200);
    header('Content-Type: application/json');
    echo '{"status":"failed", "message":"Invalid Params."}';
    exit;
}
