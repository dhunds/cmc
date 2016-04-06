<?php
include ('connection.php');
include_once('classes/class.notification.php');
$objNotification = new Notification();

if (isset($_POST['poolId']) && isset($_POST['mobileNumber']) && $_POST['poolId'] !='' && $_POST['mobileNumber'] !='') {

    $CabId = $_POST['CabId'];

    //$OwnerName = $_POST['OwnerName'];
    //$OwnerNumber = $_POST['OwnerNumber'];
    $MemberName = $_POST['MemberName'];
    $MemberNumber = $_POST['MemberNumber'];
    $MemberLocationAddress = $_POST['MemberLocationAddress'];
    $MemberLocationlatlong = $_POST['MemberLocationlatlong'];
    $MemberEndLocationAddress = $_POST['MemberEndLocationAddress'];
    $MemberEndLocationlatlong = $_POST['MemberEndLocationlatlong'];
    $Status = $_POST['Status'];
    $Message = $_POST['Message'];



    $poolId = $_POST['poolId'];
    $mobileNumber = trim($_POST['mobileNumber']);
    $memberName = $_POST['memberName'];

    $sql = "SELECT PoolId FROM userpoolsslave WHERE MemberNumber ='".$mobileNumber."' AND PoolId=".$poolId;
    $stmt = $con->query($sql);
    $found = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

    if ($found < 1) {
        $sql = "INSERT INTO userpoolsslave(PoolId,MemberName,MemberNumber, IsActive) VALUES  ('$poolId','$memberName','$mobileNumber', '1')";
        $stmt = $con->prepare($sql);
        $res = $stmt->execute();
    }

    if (isset($_POST['cabId']) && $_POST['cabId'] !='') {

        // Join Public Group

        $sqlI = "SELECT imagename FROM userprofileimage WHERE Trim(MobileNumber) = Trim('$MemberNumber')";
        $stmtI = $con->query($sqlI);
        $MemberImageName = $stmtI->fetchColumn();

        $sth = $con->prepare("SELECT COUNT(*) AS RemainingSeats FROM acceptedrequest WHERE CabId = '$CabId' and Status != 'Dropped'");
        $sth->execute();
        $RemainingSeats = (int)$sth->fetchColumn();

        $sth1 = $con->prepare("SELECT Seats FROM cabopen WHERE CabId = '$CabId' and CabStatus = 'A'");
        $sth1->execute();
        $Seats = (int)$sth1->fetchColumn();

        if (($Seats - $RemainingSeats) > 0) {
            $sql2 = "INSERT INTO acceptedrequest(CabId, OwnerName, OwnerNumber, MemberName, MemberNumber, MemberLocationAddress, MemberLocationlatlong, MemberEndLocationAddress, MemberEndLocationlatlong, MemberImageName, Status) VALUES ('$CabId', '$OwnerName','$OwnerNumber','$MemberName', '$MemberNumber','$MemberLocationAddress', '$MemberLocationlatlong','$MemberEndLocationAddress','$MemberEndLocationlatlong', '$MemberImageName','$Status')";
            $stmt2 = $con->prepare($sql2);
            $res2 = $stmt2->execute();

            if ($res2 === true) {
                if (($Seats - $RemainingSeats) > 0) {
                    $updatedRemainingSeats = ($Seats - $RemainingSeats) - 1;
                    $upsql2 = "UPDATE cabopen SET RemainingSeats= '$updatedRemainingSeats' WHERE CabId = '$CabId'";
                    $upstmt2 = $con->prepare($upsql2);
                    $upres2 = $upstmt2->execute();
                }

                $NotificationType = "CabId_Joined";

                $params = array('NotificationType' => $NotificationType, 'SentMemberName' => $MemberName, 'SentMemberNumber' => $MemberNumber, 'ReceiveMemberName'=>$OwnerName, 'ReceiveMemberNumber'=>$OwnerNumber, 'Message'=>$Message, 'CabId'=>$CabId, 'DateTime'=>'now()');
                $notificationId = $objNotification->logNotification($params);
            } else {
                echo 'Error';
            }
        } else {
            echo 'Error';
        }
        //End Join public group
    }


    if ($res) {
        http_response_code(200);
        header('Content-Type: application/json');
        echo '{status:"success", message:"User added to group"}';
        exit;
    } else {
        http_response_code(200);
        header('Content-Type: application/json');
        echo '{status:"fail", message:"An error occured, Please try later. "}';
        exit;
    }
} else {
    http_response_code(500);
    header('Content-Type: application/json');
    echo '{"status":"failed", "message":"Invalid Params."}';
}
