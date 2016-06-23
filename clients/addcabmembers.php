<?php
include_once('connection.php');
include_once('header.php');
include_once('topmenu.php');

$unauthorised =0;

if (isset($_POST['submit']) && $_POST['memberDetails'] != '' ) {

    $duplicates = '';

    $i = 0;
    $arrDuplicate = [];

    $members = [];

    $membersDetail = explode(PHP_EOL, $_POST['memberDetails']);

    foreach ($membersDetail as $member) {
        $members[] = explode(',', $member);
    }

    foreach ($members as $val) {

        $validNumber=0;
        $regex = "/^(\+91-|\+91|0091|0)?\d{10}$/";

        if (preg_match($regex, trim($val[0]))) {
            $validNumber = 1;
        }

        if (trim($val[0]) != '' && $validNumber) {
            $val[0] = '0091' . substr(trim($val[0]), -10);
            $stmt = $con->query("Select * From cabOwners WHERE cleintId=" . $_SESSION['userId'] . " AND mobileNumber = '" . $val[0] . "'");
            $found = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

            if ($found > 0) {
                $arrDuplicate[] =  substr(trim($val[0]), -10);
            } else {
                echo $sql = "INSERT INTO cabOwners(Name, mobileNumber, cleintId) VALUES ('" . $val[1] . "', '" . $val[0] . "', ".$_SESSION['userId'].")";
                $stmt = $con->prepare($sql);
                $res2 = $stmt->execute();
                $i++;
            }
        }
    }

    if (!empty($arrDuplicate))
    {
        $duplicates = implode(', ', $arrDuplicate);

        if (count($arrDuplicate < 2)){
            $ppendmsg = ' is already member.';
        } else {
            $ppendmsg = ' are already member.';
        }
        $duplicates = $duplicates.$ppendmsg;
    }

    $msg =  $i . ' member(s) added. '.$duplicates;
}
?>

<div class="content pure-u-1-1 pure-u-md-3-4">
    <div class="pure-u-4-4" id="mainContent">

        <div>
            <h4 class="tHeading">Add Members</h4>
            <?php if($msg){ ?>
                <p style="margin-left: 10px;"><?=$msg;?></p>
            <?php } ?>
            <div style="padding: 15px;">
                <form method="post" action="">
                    <div>
                        <div class="divRight bluetext">
                            Add member mobile number and Name per line<br /><br />
                            <textarea name="memberDetails" cols="60" rows="5" placeholder="Eg. 9818934735,Mukul Kumar"></textarea>
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
            <div style="clear:both;"></div>
        </div>

    </div>
</div>

<?php
include_once('footer.php');
?>