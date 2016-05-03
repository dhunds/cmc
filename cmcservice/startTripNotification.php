<?php
include('connection.php');
include_once('classes/class.notification.php');
$objNotification = new Notification();

if (isset($_POST['cabId']) && $_POST['cabId'] != '') {

    $CabID = $_POST['cabId'];
    $stmt = $con->query("SELECT OwnerName, FromShortName, ToShortName, MobileNumber from cabopen where CabId='" . $CabID . "'");
    $CabsExists = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

    if ($CabsExists > 0) {

        $NotificationType = "Trip started";
        $tripNotification = "Ride has Started. Please be on time at your pickup location.\n";

        $stmt1 = $con->query("select a.*, b.hasBoarded from registeredusers a, acceptedrequest b where a.PushNotification != 'off' and Trim(a.MobileNumber) = Trim(b.MemberNumber) and b.cabid = '$CabID'");
        $no_of_users = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

        if ($no_of_users > 0) {
            while ($row = $stmt1->fetch()) {
                $gcm_array = array();
                $apns_array = array();

                $memberName = $row['FullName'];
                $MobileNumber = $row['MobileNumber'];

                $man = "INSERT INTO notifications(NotificationType,SentMemberName, SentMemberNumber, ReceiveMemberName, ReceiveMemberNumber, Message, CabId, DateTime) VALUES ('$NotificationType','System','','$memberName','$MobileNumber','$tripNotification','$CabID',now())";
                $manstmt = $con->prepare($man);
                $manres = $manstmt->execute();
                $notificationId = $con->lastInsertId();

                $body = array('gcmText' => $tripNotification, 'pushfrom' => 'TripStart', 'notificationId' => $notificationId, 'CabId' => $CabID);

                if ($row['Platform'] == "A") {
                    $gcm_array[] = $row['DeviceToken'];
                    $objNotification->setVariables($gcm_array, $body);
                    $res = $objNotification->sendGCMNotification();
                } else {
                    $apns_array[] = $row['DeviceToken'];
                    $objNotification->setVariables($apns_array, $body);
                    $res = $objNotification->sendIOSNotification();
                }
                
            }
        }

        $sql12 = "UPDATE cabopen set uptripnotification = '1', status=1 where CabId = '" . $CabID . "'";
        $stmt12 = $con->prepare($sql12);
        $res12 = $stmt12->execute();

        http_response_code(200);
        header('Content-Type: application/json');
        echo '{"status":"success", "message":"Trip Started."}';
        exit;
    }
} else {
    http_response_code(500);
    header('Content-Type: application/json');
    echo '{"status":"fail", "message":"Invalid Params."}';
    exit;
}