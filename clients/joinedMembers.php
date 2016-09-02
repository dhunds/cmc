<?php
include 'connection.php';

$members = [];

if (isset($_REQUEST['cabId']) && $_REQUEST['cabId'] !='') {

    //$sql = "SELECT MemberName, MemberNumber FROM acceptedrequest WHERE CabId='".$_REQUEST['cabId']."' AND Status !='Dropped'";

    $sql = "SELECT ar.MemberName, ar.MemberNumber, rp.amountPaidByRider, po.name FROM acceptedrequest ar LEFT JOIN ridePayments rp ON ar.CabId=rp.cabId LEFT JOIN paymentOptions po ON po.id=rp.walletId WHERE ar.CabId='".$_REQUEST['cabId']."' AND Status !='Dropped'";
    $stmt = $con->query($sql);
    $membersJoined = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

    if ($membersJoined) {
        $stmt->execute();
        $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

?>

<html lang="en">
<head>
    <title>Joined Members</title>
<body>
<table width="100%">
    <tr bgcolor="#18A5DE">
        <td style="width: 24%; color: #fff;padding:8px;"><b>Driver Mobile</b></td>
        <td style="width: 34%; color: #fff;padding:8px;"><b>Name</b></td>
        <td style="width: 19%; color: #fff;padding:8px;"><b>Amount Paid</b></td>
        <td style="width: 19%; color: #fff;padding:8px;"><b>Payment Method</b></td>
    </tr>
    <?php
            if (!empty($members)){
                foreach($members as $value){
                    echo '<tr>
                            <td style="padding:8px;">'.substr(trim($value['MemberNumber']), -10).'</td>
                            <td style="padding:8px;">'.$value['MemberName'].'</td>
                            <td style="padding:8px;">'.$value['amountPaidByRider'].'</td>
                            <td style="padding:8px;">'.$value['name'].'</td>
                        </tr>';
                }
            } else {
                echo '<tr>
                            <td colspan="2" style="padding:8px;">No one has joined this ride.</td>
                      </tr>';
            }

    ?>
</table>

</body>
</html>

