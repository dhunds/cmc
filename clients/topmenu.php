<div id="demo-horizontal-menu">
    <ul id="std-menu-items">
        <li class="pure-menu-selected"><a href="dashboard.php"><i class="fa fa-home"></i>&nbsp;Home</a></li>
        <li class="pure-menu-selected"><a href="groups.php">My Groups</a></li>
        <li class="pure-menu-selected"><a href="approveRequests.php">Requests For Approval</a></li>
        <li class="pure-menu-selected"><a href="changepswd.php">Change Password</a></li>
        <li class="pure-menu-selected"><a href="logout.php">Logout</a></li>
    </ul>
</div>
<div style="text-align: right;float: right;margin:10px;">
    <form method="post" action="search.php">
        <input type="text" name="keyword" placeholder="search.." value="<?=($_POST['keyword'])?$_POST['keyword']:'';?>">
    </form>
</div>
</div>