<?php
include('connection.php');
include_once('classes/class.notification.php');
include_once('includes/functions.php');
include_once('includes/offers.php');

$objNotification = new Notification();

$MobileNumber = $_POST['MobileNumber'];
$singleusepassword = $_POST['singleusepassword'];

$stmt = $con->query("SELECT FullName FROM tmp_register WHERE MobileNumber = '$MobileNumber' and SingleUsePassword = '$singleusepassword'");
$user_exists = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

if ($user_exists == 0) {
    echo "FAILURE";
} else {
    $stmt2 = $con->query("SELECT * FROM tmp_register WHERE MobileNumber = '$MobileNumber' and SingleUsePassword = '$singleusepassword'
and SingleUseExpiry > NOW()");
    $user_exists = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

    if ($user_exists == 0) {
        echo "OTPEXPIRE";
    } else {
        $user = $stmt2->fetch();

        $sql = "INSERT INTO registeredusers(FullName, Password, MobileNumber, DeviceToken, Email, Gender, DOB, Platform, SingleUsePassword, SingleUseVerified, SingleUseExpiry,CreatedOn, referralCode, usedReferralCode) VALUES ('" . $user['FullName'] . "','" . $user['Password'] . "', '" . $user['MobileNumber'] . "','" . $user['DeviceToken'] . "','" . $user['Email'] . "','" . $user['Gender'] . "', '" . $user['DOB'] . "','" . $user['Platform'] . "','" . $user['SingleUsePassword'] . "', '1', '" . $user['SingleUseExpiry'] . "',now(),'" . $user['referralCode'] . "','" . $user['usedReferralCode'] . "')";
        $stmt = $con->prepare($sql);
        $res = $stmt->execute();

        $sql = "INSERT INTO userprofileimage(MobileNumber, imagename) VALUES ('" . $user['MobileNumber'] . "','')";
        $stmt = $con->prepare($sql);
        $res2 = $stmt->execute();

        if ($res == true && $res2 == true) {
            $stmt = $con->prepare("DELETE FROM  tmp_register WHERE MobileNumber='" . $user['MobileNumber'] . "'");
            $stmt->execute();

            /** Create Default Club ***/
            if ($user['usedReferralCode'] != '') {
                $sql = "SELECT * FROM registeredusers WHERE referralCode='" . $user['usedReferralCode'] . "'";
                $stmt = $con->query($sql);
                $usr = $stmt->fetch(PDO::FETCH_ASSOC);

                $sql = "SELECT PoolId FROM userpoolsmaster WHERE OwnerNumber='" . $usr['MobileNumber'] . "'";
                $stmt = $con->query($sql);
                $found = $con->query("SELECT FOUND_ROWS()")->fetchColumn();
                $found1 = 0;

                if ($found > 0) {
                    $ids = '';
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $ids .= $row['PoolId'] . ',';
                    }
                    $ids = substr($ids, 0, -1);

                    $sql = "SELECT PoolSubId FROM userpoolsslave WHERE PoolId IN ($ids) AND MemberNumber='" . trim($user['MobileNumber']) . "'";
                    $stmt = $con->query($sql);
                    $found1 = $con->query("SELECT FOUND_ROWS()")->fetchColumn();
                }

                if (!$found1) {
                    $insertedId = createClub($usr['MobileNumber'], 'My Cab Share');
                    addToClub($insertedId, $user['FullName'], $user['MobileNumber']);
                }


                /** Add Credits to Beneficiaries **/

                $resp = claimReferalBonus($user['usedReferralCode'], $MobileNumber, $user['FullName'], $user['DeviceToken'], $objNotification);


            }

            echo "SUCCESS";
        }
    }
}