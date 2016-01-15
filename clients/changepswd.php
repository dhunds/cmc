<?php
include_once('connection.php');
include_once('header.php');
include_once('topmenu.php');
?>

<div class="content pure-u-1-1 pure-u-md-3-4">
    <div class="pure-u-4-4" id="mainContent">

        <div>
            <h4 class="headingText">Change Password</h4>
            <div style="padding: 15px;">
                <form method="post" action="" enctype="multipart/form-data">
                    <div>
                        <div class="divRight bluetext"><input type="text" name="currentpass" id="currentpass" placeholder="Current Password"></div>
                        <div style="clear:both;"></div>
                        <br/>

                        <div class="divRight bluetext"><input type="text" name="newpswd" id="newpswd" placeholder="New Password">
                        </div>
                        <div style="clear:both;"></div>
                        <br/>

                        <div class="divRight bluetext"><input type="text" name="confirmpswd" id="confirmpswd" placeholder="Repeat Password">
                        </div>
                        <div style="clear:both;"></div>
                        <br/>

                        <div class="divLeft bluetext"><input type="submit" name="submit" value="Change Password" class="cBtn">
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
