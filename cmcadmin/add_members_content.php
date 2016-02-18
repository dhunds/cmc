<?php
include_once('functions.php');

if (isset($_POST['submit']) && (count($_FILES) > 0 || $_POST['memberDetails'] != '') && $_POST['mobNumber'] != '') {
    $_POST['mobNumber'] = '0091' . substr(trim($_POST['mobNumber']), -10);
    $MobileNumber = $_POST['mobNumber'];
    $clubName = $_POST['name'];

    $stmt = $con->query("Select pm.*, ru.FullName From userpoolsmaster pm LEFT JOIN registeredusers ru ON ru.MobileNumber=pm.OwnerNumber WHERE PoolName='$clubName' AND OwnerNumber = '$MobileNumber'");
    $found = $con->query("SELECT FOUND_ROWS()")->fetchColumn();
    $i = 0;
    if ($found > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $members = [];

        if (is_uploaded_file($_FILES['members']['tmp_name'])) {
            $file = fopen($_FILES['members']['tmp_name'], "r");

            while (!feof($file)) {
                $member = fgetcsv($file);
                $members[] = $member;
            }
            fclose($file);
        } else {
            $membersDetail = explode(PHP_EOL, $_POST['memberDetails']);

            foreach ($membersDetail as $member) {
                $members[] = explode(',', $member);
            }
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
                            echo $res.'<br />';
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
    echo $i . ' members added to this club';
}

?>

<style>
    .divLeft {
        float: left;
        width: 10%;
    }

    .divRight {
        float: left;
        width: 90%;
    }
</style>
<link rel="stylesheet" type="text/css" href="css/jquery.autocomplete.css"/>
<script type="text/javascript" src="js/jquery.js"></script>
<script type="text/javascript" src="js/jquery.autocomplete.js"></script>
<script>
    function showClubs() {
        var mobNumber = $('#mobNumber').val();
        $("#clubname").autocomplete("getClubs.php?mobNumber=" + mobNumber, {
            selectFirst: true
        });
    }
</script>
<div class="articleBorder">
    <div>
        <div style="width:100%;height:100%;float:left;">
            <h4 class="headingText">Add Members to Club</h4>

            <form method="post" action="" enctype="multipart/form-data">
                <div>
                    <div class="divLeft bluetext">&nbsp;&nbsp;* Owner Number:</div>
                    <div class="divRight bluetext"><input type="text" name="mobNumber" id="mobNumber"></div>
                    <div style="clear:both;"></div>
                    <br/>

                    <div class="divLeft bluetext">&nbsp;&nbsp;* Club Name:</div>
                    <div class="divRight bluetext"><input type="text" name="name" id="clubname" onkeyup="showClubs();">
                    </div>
                    <div style="clear:both;"></div>
                    <br/>

                    <div class="divLeft bluetext">&nbsp;&nbsp;Member &nbsp;&nbsp;Numbers:</div>
                    <div class="divRight bluetext"><textarea name="memberDetails" cols="30" rows="5"></textarea></div>
                    <div style="clear:both;"></div>
                    <br/>

                    <div class="divLeft bluetext" style="padding-left: 15%">OR</div>
                    <div style="clear:both;"></div>
                    <br/>

                    <div class="divLeft bluetext">&nbsp;&nbsp;&nbsp;&nbsp;File (csv):</div>
                    <div class="divRight bluetext"><input type="file" name="members"></div>
                    <div style="clear:both;"></div>
                    <br/>

                    <div class="divLeft bluetext">&nbsp;&nbsp;<input type="submit" name="submit"
                                                                     value="Add Members to Club" class="cBtn"></div>
                    <div class="divRight bluetext"></div>

                </div>
            </form>

            <br/>
        </div>

        <div style="clear:both;"></div>
    </div>
</div>