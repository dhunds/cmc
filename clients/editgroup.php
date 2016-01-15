<?php
include_once('connection.php');
include_once('header.php');
include_once('topmenu.php');
?>

<div class="content pure-u-1-1 pure-u-md-3-4">
    <div class="pure-u-4-4" id="mainContent">

        <div>
            <h4 class="headingText">Edit Group</h4>

            <div style="padding: 15px;">
                <form method="post" action="" enctype="multipart/form-data">
                    <div>
                        <div class="divRight bluetext"><input type="text" name="name" placeholder="Full Name"></div>
                        <div style="clear:both;"></div>
                        <br>

                        <div class="divRight bluetext"><input type="text" name="mobNumber" placeholder="Mobile Number" readonly>
                        </div>
                        <div style="clear:both;"></div>
                        <br>

                        <div class="divRight bluetext"><input type="text" name="email" placeholder="Email"></div>
                        <div style="clear:both;"></div>
                        <br>

                        <div class="divRight bluetext"><input type="text" name="groupname" placeholder="Group Name"></div>
                        <div style="clear:both;"></div>
                        <br>

                        <div class="divLeft bluetext"><input type="submit" name="submit"
                                                                         value="Update" class="cBtn"></div>
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
