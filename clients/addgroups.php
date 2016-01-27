<?php
include_once('connection.php');
include_once('header.php');
include_once('topmenu.php');

if (isset($_POST['submit']) && (count($_FILES) > 0 || $_POST['clubNames'] != '') && $_POST['mobNumber'] != '') {
    $_POST['mobNumber'] = '0091' . substr(trim($_POST['mobNumber']), -10);
    $MobileNumber = $_POST['mobNumber'];
    $FullName = $_POST['name'];
    $Email = $_POST['email'];
    $Platform = 'A';
    $deviceId = 'admin';

    $stmt = $con->query("select FullName, MobileNumber FROM registeredusers WHERE trim(MobileNumber)='" . $MobileNumber . "'");
    $found = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

    if ($found < 1) {
        $sql = "INSERT INTO registeredusers(FullName, MobileNumber, Email, Platform, DeviceToken, CreatedOn, isAdminType) VALUES ('$FullName', '$MobileNumber','$Email','$Platform','$deviceId',now(),1)";
        $stmt = $con->prepare($sql);
        $res = $stmt->execute();

        $sql = "INSERT INTO userprofileimage(MobileNumber, imagename) VALUES ('$MobileNumber','')";
        $stmt = $con->prepare($sql);
        $res2 = $stmt->execute();
    }
    $i = 0;
    if (($found > 0) || ($res == true)) {

        $clubs = explode(PHP_EOL, $_POST['clubNames']);

        foreach ($clubs as $val) {
            $val = trim($val);
            if ($val != '') {
                $stmt = $con->query("Select * From userpoolsmaster WHERE PoolName='$val' AND OwnerNumber = '$MobileNumber'");
                $found = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

                if ($found > 0) {
                    echo "Club Name '".$val . "' Already Exist<br />";
                } else {
                    $val = preg_replace( '/[^[:print:]]/', '',$val);

                    $sql = "INSERT INTO userpoolsmaster(OwnerNumber, PoolName, Active) VALUES ('$MobileNumber', '$val','1')";
                    $stmt = $con->prepare($sql);
                    $res2 = $stmt->execute();
                    if ($res2 == true) {
                        $groupId = $con->lastInsertId();
                        $clientId = $_SESSION['userId'];

                        $sql = "INSERT INTO clientGroups(clientId, groupId) VALUES ('$clientId', '$groupId')";
                        $stmt = $con->prepare($sql);
                        $res2 = $stmt->execute();

                        $i++;
                    }
                }
            }
        }
    }
    $msg = $i . ' groups added successfully';
}
?>
<div class="content pure-u-1-1 pure-u-md-3-4">
    <div class="pure-u-4-4" id="mainContent">

        <div>
            <h4 class="headingText">Add Groups</h4>
            <?php if($msg){ ?>
            <p style="margin-left: 10px;"><?=$msg;?></p>
            <?php } ?>
            <div style="padding: 15px;">
                <form method="post" action="">
                    <div>
                        <div class="divRight bluetext"><input type="text" name="name" placeholder="Full Name"></div>
                        <div style="clear:both;"></div>
                        <br>

                        <div class="divRight bluetext"><input type="text" name="mobNumber" placeholder="Mobile Number">
                        </div>
                        <div style="clear:both;"></div>
                        <br>

                        <div class="divRight bluetext"><input type="text" name="email" placeholder="Email"></div>
                        <div style="clear:both;"></div>
                        <br>

                        <div class="divRight bluetext"><textarea name="clubNames" cols="30" rows="5"
                                                                 placeholder="Group name, For adding multiple group enter each group name in new line"></textarea>
                        </div>
                        <div style="clear:both;"></div>
                        <br>

                        <div class="divLeft bluetext"><input type="submit" name="submit"
                                                                         value="Create Group" class="cBtn"></div>
                        <div class="divRight bluetext"></div>
                    </div>
                </form>

                <br/>
            </div>
            <div style="clear:both;"></div>
        </div>

    </div>
</div>

<?php
include_once('footer.php');
?>
