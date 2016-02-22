<div id="demo-horizontal-menu">
    <ul id="std-menu-items">
<!--        <li class="pure-menu-selected"><a href="dashboard.php"><i class="fa fa-home"></i>&nbsp;Home</a></li>-->
        <li class="pure-menu-selected"><a href="groups.php">Groups</a></li>
        <li class="pure-menu-selected"><a href="approveRequests.php">Member Invitations</a></li>
        <li class="pure-menu-selected"><a href="changepswd.php">Change Password</a></li>
        <li class="pure-menu-selected"><a href="help.pdf">Help</a></li>
        <li class="pure-menu-selected" style="float: right;"><a href="logout.php">Logout</a></li>
    </ul>
</div>
<div style="text-align: right;float: right;margin:10px;">
    <form method="post" action="search.php">
        <input type="text" name="keyword" placeholder="search members.." value="<?=(isset($_POST['keyword']))?$_POST['keyword']:'';?>">
    </form>
</div>
</div>