<?php
include_once('connection.php');
include_once('header.php');
include_once('topmenu.php');
?>

<div class="content pure-u-1-1 pure-u-md-3-4">
    <div class="pure-u-4-4" id="mainContent">

        <div>
            <h4 class="headingText">Add Members</h4>

            <div style="padding: 15px;">
                <form method="post" action="" enctype="multipart/form-data">
                    <div>
                        <div class="divRight bluetext"><input type="text" name="name" placeholder="Owner Number"></div>
                        <div style="clear:both;"></div>
                        <br>

                        <div class="divRight bluetext"><input type="text" name="mobNumber" placeholder="Club Name">
                        </div>
                        <div style="clear:both;"></div>
                        <br>

                        <div class="divRight bluetext"><textarea name="clubNames" cols="30" rows="5"
                                                                 placeholder="Member numbers, For adding multiple members enter each member number in new line"></textarea>
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
