<div id="demo-horizontal-menu">
    <ul id="std-menu-items">
<!--        <li class="pure-menu-selected"><a href="dashboard.php"><i class="fa fa-home"></i>&nbsp;Home</a></li>-->
        <?php if ($_SESSION['type']==2) { ?>
             <li class="pure-menu-selected"><a href="createRide.php">Create Ride</a></li>
             <?php if ($_SESSION['username']=='attach') { ?>
             <li class="pure-menu-selected"><a href="cabMembers.php">Members</a></li>
             <?php } ?>
             <li class="pure-menu-selected"><a href="changepswd.php">Change Password</a></li>
             <li class="pure-menu-selected" style="float: right;"><a href="logout.php">Logout</a></li>
        <?php } else { ?>
        <li class="pure-menu-selected"><a href="approveRequests.php">Pending Approvals</a></li>
        <li class="pure-menu-selected"><a href="changepswd.php">Change Password</a></li>
        <li class="pure-menu-selected"><a href="help.pdf" target="_blank">Help</a></li>
        <?php } ?>
    </ul>
</div>
<?php if ($_SESSION['type'] !=2) { ?>
<div style="text-align: right;float: right;margin:10px;">
    <form method="post" action="search.php">
        <input type="text" name="keyword" placeholder="search members.." value="<?=(isset($_POST['keyword']))?$_POST['keyword']:'';?>">
    </form>
</div>
<?php } ?>
</div>