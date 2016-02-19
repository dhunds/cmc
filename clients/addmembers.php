<?php
include_once('connection.php');
include_once('functions.php');
include_once('header.php');
include_once('topmenu.php');

$unauthorised =0;

if (isset($_POST['submit']) && $_POST['memberDetails'] != '' ) {

    $MobileNumber = $_POST['mobNumber'];
    $clubName = $_POST['name'];

    $stmt = $con->query("Select pm.*, ru.FullName From userpoolsmaster pm LEFT JOIN registeredusers ru ON ru.MobileNumber=pm.OwnerNumber WHERE PoolName='$clubName' AND OwnerNumber = '$MobileNumber'");
    $found = $con->query("SELECT FOUND_ROWS()")->fetchColumn();
    $i = 0;
    if ($found > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $members = [];

        $membersDetail = explode(PHP_EOL, $_POST['memberDetails']);

        foreach ($membersDetail as $member) {
            $members[] = explode(',', $member);
        }

        foreach ($members as $val) {
            if (!(isset($val[1]) && $val[1] != '')) {
                $val[1] = '';
            }
            if ($val[0] != '') {
                $val[0] = '0091' . substr(trim($val[0]), -10);
                $stmt = $con->query("Select * From userpoolsslave WHERE PoolId=" . $row['PoolId'] . " AND MemberNumber = '" . $val[0] . "'");
                $found = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

                if ($found > 0) {
                    echo "'" . $val[0] . "' is already member of this club.<br/>";
                } else {
                    $sql = "INSERT INTO userpoolsslave(PoolId, MemberName, MemberNumber, IsActive) VALUES ('" . $row['PoolId'] . "', '" . $val[1] . "','" . $val[0] . "', '1')";
                    $stmt = $con->prepare($sql);
                    $res2 = $stmt->execute();

                    if ($res2 == true) {
                        $stmtUsr = $con->query("Select DeviceToken From registeredusers WHERE MobileNumber='" . $val[0] . "' AND DeviceToken !=''");
                        $found = $con->query("SELECT FOUND_ROWS()");

                        if ($found > 0) {
                            $deviceId[] = $stmtUsr->fetchColumn();
                            $NotificationType = "PoolId_Added";
                            $OwnerName = $row['FullName'];
                            $OwnerNumber = $MobileNumber;
                            $FriendName = $val[1];
                            $FriendNumber = $val[0];
                            $poolid = $row['PoolId'];

                            $manFriend = "INSERT INTO notifications(NotificationType, SentMemberName, SentMemberNumber, ReceiveMemberName, ReceiveMemberNumber, Message, PoolId, DateTime) VALUES ('$NotificationType','$OwnerName','$OwnerNumber','$FriendName','$FriendNumber','$Msg','$poolid',now())";
                            $manstmtFriend = $con->prepare($manFriend);
                            $manresFriend = $manstmtFriend->execute();
                            $notificationId = $con->lastInsertId();

                            $Msg = $OwnerName . ' added you to a club ' . $ClubName;
                            $res = sendAndroidNotification($deviceId, $Msg, 'PoolId_');
                            //echo $res.'<br />';
                            $i++;
                        } else {
                            $sqlSMS = "SELECT SmsMessage FROM smstemplates WHERE SmsshortCode = 'CLUBADD'";
                            $stmtSMS = $con->query($sqlSMS);
                            $messageSMS = $stmtSMS->fetchColumn();
                            $messageSMS = str_replace("OXXXXX", $row['FullName'], $messageSMS);
                            sendSMS("[" . $val[0] . "]", $messageSMS);
                            $i++;
                        }
                    }
                }
            }
        }
    }
    $msg =  $i . ' members added to this club';
}


$sql = "SELECT PoolId, PoolName, OwnerNumber  FROM userpoolsmaster JOIN clientGroups ON clientGroups.groupId=userpoolsmaster.PoolId AND clientGroups.clientId=".$_SESSION['userId']." AND userpoolsmaster.PoolId=".$_REQUEST['id'];
$stmt = $con->prepare($sql);
$stmt->execute();
$rowCount = (int) $stmt->rowCount();

if($rowCount > 0){
    $group = $stmt->fetch(PDO::FETCH_ASSOC);
} else {
    $msg = 'You are not the owner of this group.';
    $unauthorised =1;
}
?>

<div class="content pure-u-1-1 pure-u-md-3-4">
    <div class="pure-u-4-4" id="mainContent">

        <div>
            <h4 class="headingText">Add Members to group <?=ucfirst($group['PoolName'])?></h4>
            <?php if($msg){ ?>
                <p style="margin-left: 10px;"><?=$msg;?></p>
            <?php } ?>
            <?php if (!$unauthorised) { ?>
            <div style="padding: 15px;">
                <form method="post" action="">
                    <div>
                        <div class="divRight bluetext"><textarea name="memberDetails" cols="60" rows="5"
                                                                 placeholder="Enter members in format <Member Number>,<Member Name>. For adding multiple members enter each member number in new line"></textarea>
                            <input type="hidden" name="name" value="<?=$group['PoolName']?>">
                            <input type="hidden" name="mobNumber" value="<?=$group['OwnerNumber']?>">
                        </div>
                        <div style="clear:both;"></div>
                        <br>

                        <div class="divLeft bluetext"><input type="submit" name="submit"
                                                                         value="Add Member" class="cBtn"></div>
                        <div class="divRight bluetext"></div>
                    </div>
                </form>

                <br/>
            </div>
            <?php } ?>
            <div style="clear:both;"></div>
        </div>

    </div>
</div>

<?php
include_once('footer.php');
?>