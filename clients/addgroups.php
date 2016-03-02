<?php
include_once('connection.php');
include_once('header.php');
include_once('topmenu.php');

if (isset($_POST['submit']) && (count($_FILES) > 0 || $_POST['clubNames'] != '')) {
    $MobileNumber = $_SESSION['mobileNumber'];

    $i = 0;
    $duplicates = '';
    $arrDuplicate = [];
    $clubs = explode(PHP_EOL, $_POST['clubNames']);

    foreach ($clubs as $val) {
        $val = trim($val);
        if ($val != '') {
            $stmt = $con->query("Select * From userpoolsmaster WHERE PoolName='$val' AND OwnerNumber = '$MobileNumber'");
            $found = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

            if ($found > 0) {
                $arrDuplicate[] =  $val;
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

    if (!empty($arrDuplicate))
    {
        $duplicates = implode(', ', $arrDuplicate);

        $duplicates = 'You already have group(s) with name '.$duplicates;
    }

    $msg = $i . ' group(s) created. '.$duplicates;
}
?>

<div class="content pure-u-1-1 pure-u-md-3-4">
    <div class="pure-u-4-4" id="mainContent">

        <div>
            <h4 class="tHeading">Add Groups</h4>
            <?php if($msg){ ?>
            <p style="margin-left: 10px;"><?=$msg;?></p>
            <?php } ?>
            <div style="padding: 15px;">
                <form method="post" action="">
                    <div>
                        <div class="divRight bluetext">
                            Add Group name per line<br /><br />
                            <textarea name="clubNames" cols="30" rows="5"
                                                                 placeholder="Eg. Close Friends"></textarea>
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
