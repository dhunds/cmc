<?php
include('connection.php');
include_once('classes/class.notification.php');
$objNotification = new Notification();

if (isset($_POST['cabId']) && $_POST['cabId'] !='') {
    $CabId = $_POST['cabId'];
    if (isset($_POST['mobileNumber']) && $_POST['mobileNumber'] !='' && isset($_POST['owner'])) {
    // Mark Individual members as fare settled
        if ($_POST['mobileNumber']==$_POST['owner']){
           $sql = "UPDATE cabopen SET settled=1 WHERE CabId = '".$_POST['cabId']."' AND trim(MobileNumber) = '".$_POST['mobileNumber']."'";
        }else{
          $sql = "UPDATE cabmembers SET settled=1 WHERE CabId = '".$_POST['cabId']."' AND trim(MemberNumber) = '".$_POST['mobileNumber']."'";
        }

        $stmt = $con->prepare($sql);
        $res = $stmt->execute();

    // Send Notification
        $sql = "SELECT FullName FROM registeredusers WHERE trim(MobileNumber) = '".$_POST['mobileNumber']."'";
        $stmt = $con->prepare($sql);
        $stmt->execute();
        $user = $stmt->fetch();
        $Msg = $user['FullName'].' has indicated that fare split has already been settled or will be paid in cash';

       $sql = "SELECT ru.FullName, ru.MobileNumber, ru.DeviceToken FROM registeredusers ru JOIN cabopen co ON ru.MobileNumber=co.paidBy WHERE co.CabId='".$_POST['cabId']."'";
        $stmt = $con->prepare($sql);
        $stmt->execute();
        $row = $stmt->fetch();
        $MemberName=$row['FullName'];
        $MemberNumber=$row['MobileNumber'];
        $gcm_array[]= $row['DeviceToken'];

        $sql = "INSERT INTO notifications(NotificationType,SentMemberName, SentMemberNumber, ReceiveMemberName, ReceiveMemberNumber, Message, CabId, DateTime) VALUES ('genericnotification','System','','$MemberName', '$MemberNumber','$Msg', '$cabId', now())";
        $nStmt = $con->prepare($sql);
        $nStmt->execute();
        $notificationId =  $con->lastInsertId();

        $body = array('gcmText' => $Msg, 'pushfrom' => 'genericnotification', 'notificationId' => $notificationId, 'CabId' => $CabId);
        $objNotification->setVariables($gcm_array, $body);
        $res = $objNotification->sendGCMNotification();

        // If All members done fare settlement mark trip as Archived
        $stmt = $con->query("select MemberNumber FROM cabmembers where CabId = '".$_POST['cabId']."' AND settled !=1");
        $foundRows = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

        $stmt = $con->query("select MobileNumber FROM cabopen where CabId = '".$_POST['cabId']."' AND settled !=1");
        $foundRows1 = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

        if($foundRows < 1 && $foundRows1 < 1){
            $sql = "UPDATE cabopen set CabStatus = 'I' where CabId = '" . $_POST['cabId'] . "'";
            $stmt = $con->prepare($sql);
            $res = $stmt->execute();
        }

        http_response_code(200);
        header('Content-Type: application/json');
        echo '{status:"success", message:"Updated sucessfully"}';
        exit;

    } else {
        //Mark Trip as Archived
        $sql = "UPDATE cabopen set CabStatus = 'I' where CabId = '" . $_POST['cabId'] . "'";
        $stmt = $con->prepare($sql);
        $res = $stmt->execute();

        http_response_code(200);
        header('Content-Type: application/json');
        echo '{status:"success", message:"Updated sucessfully"}';
        exit;
    }
}