<?php
include_once('connection.php');
include_once('header.php');
include_once('topmenu.php');

$msg = '';
if (isset($_POST['submitfrm'])){
    $sql = "SELECT id FROM clients WHERE password='".$_POST['currentpass']."' AND id=".$_SESSION['userId'];
    $stmt = $con->prepare($sql);
    $stmt->execute();
    $rowCount = (int) $stmt->rowCount();

    if ($rowCount > 0){
        $sql = "UPDATE clients SET password='".$_POST['newpswd']."' WHERE id=".$_SESSION['userId'];
        $stmt = $con->prepare($sql);

        if ($stmt->execute()){
            $msg = 'Password changed successfully!';
        } else{
            $msg = 'An error occured. Please try again.';
        }
    } else{
        $msg = 'Invalid current password.';
    }
}

?>

<div class="content pure-u-1-1 pure-u-md-3-4">
    <div class="pure-u-4-4" id="mainContent">

        <div>
            <h4 class="headingText">Change Password</h4>
            <p style="margin-left: 10px;" id="errormsg"><?=$msg;?></p>
            <div style="padding: 15px;">
                <form method="post" action="" onsubmit="return changepswd();">
                    <div>
                        <div class="divRight bluetext"><input type="password" name="currentpass" id="currentpass" placeholder="Current Password"></div>
                        <div style="clear:both;"></div>
                        <br/>

                        <div class="divRight bluetext"><input type="password" name="newpswd" id="newpswd" placeholder="New Password">
                        </div>
                        <div style="clear:both;"></div>
                        <br/>

                        <div class="divRight bluetext"><input type="password" name="confirmpswd" id="confirmpswd" placeholder="Repeat Password">
                        </div>
                        <div style="clear:both;"></div>
                        <br/>

                        <div class="divLeft bluetext"><input type="submit" name="submitfrm" value="Change Password" class="cBtn">
                        </div>
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

<script>

    function changepswd(){

        if ($( "#currentpass" ).val() ==''){
            $( "#errormsg").text('Please enter current password');
            return false;
        }

        if ($( "#newpswd" ).val() ==''){
            $( "#errormsg").html('Please enter new password');
            return false;
        }

        if ($( "#newpswd" ).val().length < 6){
            $( "#errormsg").html('New password too short.');
            return false;
        }

        if ($( "#newpswd" ).val() != $( "#confirmpswd" ).val()){
            $( "#errormsg").html('New password and confirm password does not match.');
            return false;
        }
        return true;
    }

</script>