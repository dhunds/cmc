<?php
include_once('connection.php');
include_once('header.php');
include_once('topmenu.php');
?>

<div class="content pure-u-1-1 pure-u-md-3-4">
    <div class="pure-u-4-4" id="mainContent">

        <div>
            <h4 class="headingText">Members: Test Group</h4>
            <!-- listing groups-->
            <div class="articleBorder">
                <div class="pure-u-1"><p style="text-align:right;margin-right: 10px;"><a href="addmembers.php">Add New Members</a>

                    <div class="pure-g dashboard-summary-heading">
                        <div class="pure-u-12-24"><p class="tHeading">Member Name</p></div>
                        <div class="pure-u-8-24"><p class="tHeading">Mobile Number</p></div>
                        <div class="pure-u-4-24"><p class="tHeading">Action</p></div>
                    </div>

                    <div class="pure-g pure-g1 dashboard-summary-heading">
                        <div class="pure-u-12-24"><p class="dashboard-summary-title">Mukul Kumar</p></div>
                        <div class="pure-u-8-24"><p class="dashboard-summary-members">9910117448</p></div>
                        <div class="pure-u-4-24"><p><a href="#">Delete</a></p></div>
                    </div>
                    <div class="pure-g pure-g1 dashboard-summary-heading">
                        <div class="pure-u-12-24"><p class="dashboard-summary-title">Mukul Kumar</p></div>
                        <div class="pure-u-8-24"><p class="dashboard-summary-members">9910117448</p></div>
                        <div class="pure-u-4-24"><p><a href="#">Delete</a></p></div>
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
