<?php
include_once('connection.php');
include_once('header.php');
include_once('topmenu.php');
?>

<div class="content pure-u-1-1 pure-u-md-3-4">
    <div class="pure-u-4-4" id="mainContent">

        <div>
            <h4 class="headingText">Groups</h4>
            <!-- listing groups-->
            <div class="articleBorder">
                <div class="pure-u-1"><p style="text-align:right;margin-right: 10px;"><a href="addgroups.php">Add New Groups</a>

                    <div class="pure-g dashboard-summary-heading">
                        <div class="pure-u-16-24"><p class="tHeading">Group Name</p></div>
                        <div class="pure-u-4-24"><p class="tHeading">Total Members</p></div>
                        <div class="pure-u-4-24"><p class="tHeading">Action</p></div>
                    </div>

                    <div class="pure-g pure-g1 dashboard-summary-heading">
                        <div class="pure-u-16-24"><p class="dashboard-summary-title">Test Group</p></div>
                        <div class="pure-u-4-24"><p class="dashboard-summary-members"><a href="members.php?id=1">25</a></p></div>
                        <div class="pure-u-4-24"><p><a href="editgroup.php?id=1">Edit</a>
                                | <a href="#">Delete</a></p></div>
                    </div>
                    <div class="pure-g pure-g1 dashboard-summary-heading">
                        <div class="pure-u-16-24"><p class="dashboard-summary-title">Test Group</p></div>
                        <div class="pure-u-4-24"><p class="dashboard-summary-members"><a href="#">25</a></p></div>
                        <div class="pure-u-4-24"><p><a href="editgroup.php?id=1">Edit</a>
                                | <a href="#">Delete</a></p></div>
                    </div>
                </div>

            </div>
            <!-- end listing groups -->
        </div>

    </div>
</div>

<?php
include_once('footer.php');
?>
