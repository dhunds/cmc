<?php

if (isset($_REQUEST['id']) && $_REQUEST['id'] !='') {
    $method='update';
} else {
    $method='insert';
}

if (isset($_POST['submit']) && $_POST['amount'] !='' && $_POST['status'] !='') {

    if ($method =='update'){
        $sql = "UPDATE offers SET title='".$_POST['title']."', description='".$_POST['description']."', terms='".$_POST['terms']."', code='".$_POST['code']."', amount=".$_POST['amount'].", type='".$_POST['type']."', maxUse=".$_POST['maxUse'].", maxUsePerUser=".$_POST['maxUsePerUser'].", validFrom='".$_POST['validFrom']."', validThru='".$_POST['validThru']."', status=".$_POST['status'].", updatedOn=now() WHERE id=".$_POST['id'];
    } else {
       $sql = "INSERT INTO offers SET title='".$_POST['title']."', description='".$_POST['description']."', terms='".$_POST['terms']."', code='".$_POST['code']."', amount=".$_POST['amount'].", type='".$_POST['type']."', maxUse=".$_POST['maxUse'].", maxUsePerUser=".$_POST['maxUsePerUser'].", validFrom='".$_POST['validFrom']."', validThru='".$_POST['validThru']."', status=".$_POST['status'].", createdOn=now(), updatedOn=now()";
    }

    $stmt = $con->prepare($sql);
    if ($stmt->execute()){
        if($method=='update')
            echo 'Updated Successfully!';
        else
            echo 'Added Successfully!';
    } else{
        echo 'An error occured. Please try again.';
    }
}

if ($method =='update'){
    $sql = "Select * from offers WHERE id=".$_REQUEST['id'];
    $stmt = $con->query($sql);
    $offer = $stmt->fetch(PDO::FETCH_ASSOC);
}

?>

<link rel="stylesheet" type="text/css" media="all" href="Calendar/calendar-blue.css" title="win2k-cold-1">
<script type="text/javascript" src="Calendar/calendar.js"></script>
<script type="text/javascript" src="Calendar/lang/calendar-en.js"></script>
<script type="text/javascript" src="Calendar/calendar-setup.js"></script>
<style>
    .divLeft {
        float: left;
        width: 20%;
    }

    .divRight {
        float: left;
        width: 80%;
    }
</style>
<div class="articleBorder">
    <div>
        <div style="width:100%;height:100%;float:left;">
            <h4 class="headingText"><?=($method=='update')?'Edit':'Add New'?> Offer</h4>

            <form method="post" action="">
                <div>
                    <div class="divLeft bluetext">&nbsp;&nbsp;* Title:</div>
                    <div class="divRight bluetext"><textarea name="title" cols="40" rows="3"><?=(isset($offer['title']))?$offer['title']:''?></textarea>
                    </div>
                    <div style="clear:both;"></div>
                    <br/>
                    <div class="divLeft bluetext">&nbsp;&nbsp;* Amount:</div>
                    <div class="divRight bluetext"><input type="text" name="amount" id="amount" value="<?=(isset($offer['amount']))?$offer['amount']:''?>">
                    </div>
                    <div style="clear:both;"></div>
                    <br/>
                    <div class="divLeft bluetext">&nbsp;&nbsp;* Code:</div>
                    <div class="divRight bluetext"><input type="text" name="code" id="code" value="<?=(isset($offer['code']))?$offer['code']:''?>">
                    </div>
                    <div style="clear:both;"></div>
                    <br/>
                    <div class="divLeft bluetext">&nbsp;&nbsp;* Type:</div>
                    <div class="divRight bluetext"><input type="text" name="type" id="type" value="<?=(isset($offer['type']))?$offer['type']:''?>">
                    </div>
                    <div style="clear:both;"></div>
                    <br/>
                    <div class="divLeft bluetext">&nbsp;&nbsp;* Max Use:</div>
                    <div class="divRight bluetext"><input type="text" name="maxUse" id="maxUse" value="<?=(isset($offer['maxUse']))?$offer['maxUse']:''?>">
                    </div>
                    <div style="clear:both;"></div>
                    <br/>
                    <div class="divLeft bluetext">&nbsp;&nbsp;* Max Use Per User:</div>
                    <div class="divRight bluetext"><input type="text" name="maxUsePerUser" id="maxUsePerUser" value="<?=(isset($offer['maxUsePerUser']))?$offer['maxUsePerUser']:''?>">
                    </div>
                    <div style="clear:both;"></div>
                    <br/>
                    <div class="divLeft bluetext">&nbsp;&nbsp;* Valid From:</div>
                    <div class="divRight bluetext"><input type="text" name="validFrom" id="validFrom" value="<?=(isset($offer['validFrom']))?$offer['validFrom']:''?>">
                        <img src="images/calendar.png" id="dtFrom">&nbsp;
                        <script type="text/javascript">
                            Calendar.setup({
                                inputField: 'validFrom',
                                ifFormat: "%Y-%m-%d 00:00:00",
                                button: "dtFrom",
                                singleClick: true
                            });
                        </script>
                    </div>
                    <div style="clear:both;"></div>
                    <br/>
                    <div class="divLeft bluetext">&nbsp;&nbsp;* Valid Thru:</div>
                    <div class="divRight bluetext"><input type="text" name="validThru" id="validThru" value="<?=(isset($offer['validThru']))?$offer['validThru']:''?>">
                        <img src="images/calendar.png" id="dtThru">&nbsp;
                        <script type="text/javascript">
                            Calendar.setup({
                                inputField: 'validThru',
                                ifFormat: "%Y-%m-%d 00:00:00",
                                button: "dtThru",
                                singleClick: true
                            });
                        </script>
                    </div>
                    <div style="clear:both;"></div>
                    <br/>
                    <div class="divLeft bluetext">&nbsp;&nbsp;* Description:</div>
                    <div class="divRight bluetext"><textarea name="description" cols="40" rows="4"><?=(isset($offer['description']))?$offer['description']:''?></textarea>
                    </div>
                    <div style="clear:both;"></div>
                    <br/>
                    <div class="divLeft bluetext">&nbsp;&nbsp;* Terms & Condition:</div>
                    <div class="divRight bluetext"><textarea name="terms" cols="40" rows="4"><?=(isset($offer['terms']))?$offer['terms']:''?></textarea>
                    </div>
                    <div style="clear:both;"></div>
                    <br/>
                    <div class="divLeft bluetext">&nbsp;&nbsp;*Status:</div>
                    <div class="divRight bluetext"><input type="radio" name="status" value="1" <?php if($offer['status']=='1'){echo 'checked';}?>> Active  <input type="radio" name="status" value="0" <?php if($offer['status']=='0'){echo 'checked';}?>> Inactive</div>
                    <div style="clear:both;"></div>
                    <br/>

                    <div class="divLeft bluetext">&nbsp;&nbsp;<input type="submit" name="submit"
                                                                     value="<?=($method=='update')?'Update':'Add'?>" class="cBtn"></div>
                    <div class="divRight bluetext"></div>

                </div>
                <input type="hidden" name="id" value="<?=(isset($_REQUEST['id']))?$_REQUEST['id']:''?>" />
            </form>

            <br/>
        </div>

        <div style="clear:both;"></div>
    </div>
</div>