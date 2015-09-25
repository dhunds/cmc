<?php

if (isset($_POST['submit']) && $_POST['amount'] !='' && $_POST['status'] !='') {
    $sql = "UPDATE referral SET amount='".$_POST['amount']."', isActive=".$_POST['status'].", maxUseLimit=".$_POST['maxuselimit'].", created=now()";
    $stmt = $con->prepare($sql);
    $stmt->execute();
}

$sql = "SELECT amount, isActive, maxUseLimit, DATE_FORMAT(created, '%D %b %y %h:%i %p') as updatedOn FROM referral";
$stmt = $con->query($sql);
$referral = $stmt->fetch(PDO::FETCH_ASSOC);

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
<div class="articleBorder">
    <div>
        <div style="width:100%;height:100%;float:left;">
            <h4 class="headingText">Manage Referral</h4>

            <form method="post" action="">
                <div>
                    <div class="divLeft bluetext">&nbsp;&nbsp;* Amount:</div>
                    <div class="divRight bluetext"><input type="text" name="amount" id="amount" value="<?=$referral['amount']?>">
                    </div>
                    <div style="clear:both;"></div>
                    <br/>
	                <div class="divLeft bluetext">&nbsp;&nbsp;* Max Use Limit:</div>
	                <div class="divRight bluetext"><input type="text" name="maxuselimit" id="maxuselimit" value="<?=$referral['maxUseLimit']?>">
	                </div>
	                <div style="clear:both;"></div>
	                <br/>
                    <div class="divLeft bluetext">&nbsp;&nbsp;Status:</div>
                    <div class="divRight bluetext"><input type="radio" name="status" value="1" <?php if($referral['isActive']=='1'){echo 'checked';}?>>Active  <input type="radio" name="status" value="0" <?php if($referral['isActive']=='0'){echo 'checked';}?>>Inactive</div>
                    <div style="clear:both;"></div>
                    <br/>

                    <div class="divLeft bluetext">&nbsp;&nbsp;Updated On:</div>
                    <div class="divRight bluetext"><?=$referral['updatedOn'];?></div>
                    <div style="clear:both;"></div>
                    <br/>

                    <div class="divLeft bluetext">&nbsp;&nbsp;<input type="submit" name="submit"
                                                                     value="Update" class="cBtn"></div>
                    <div class="divRight bluetext"></div>

                </div>
            </form>

            <br/>
        </div>

        <div style="clear:both;"></div>
    </div>
</div>