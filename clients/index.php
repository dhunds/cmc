<?php
include_once('header.php');
?>
<div class="header-login pure-u-1-1 pure-u-md-3-4">
Login
</div>
</div>
<div class="content pure-u-1-1 pure-u-md-3-4">
    <div class="pure-u-4-4" id="mainContent">
        <!-- Login form start -->
        <div>
            <div style="padding: 15px;text-align: center;">
                <form method="post" action="dashboard.php" enctype="multipart/form-data">
                    <div>
                        <?php if (isset($_REQUEST['err']) && $_REQUEST['err']==1){ ?>
                            <div class="divRight bluetext">Invalid Username / Password</div>
                            <div style="clear:both;"></div>
                            <br/>
                        <?php } ?>
                        <div class="divRight bluetext"><input type="text" name="username" id="username" placeholder="Username"></div>
                        <div style="clear:both;"></div>
                        <br/>

                        <div class="divRight bluetext"><input type="password" name="password" id="password" placeholder="Password">
                        </div>
                        <div style="clear:both;"></div>
                        <br/>

                        <div class="divLeft bluetext"><input type="submit" name="submit" value="Sign in" class="cBtn">
                        </div>
                        <div class="divRight bluetext"></div>
                    </div>
                </form>
            </div>

            <div style="clear:both;"></div>
        </div>
        <!-- Login end -->
    </div>
</div>
<?php
include_once('footer.php');
?>
