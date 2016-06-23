<?php
include_once('connection.php');
include_once('header.php');
include_once('topmenu.php');
include_once('functions.php');

$sql = "SELECT * FROM cabOwners WHERE cleintId=" . $_SESSION['userId'];
$stmt = $con->prepare($sql);
$stmt->execute();
$memberCount = (int)$stmt->rowCount();

?>
<div class="content pure-u-1-1 pure-u-md-3-4">
    <div class="pure-u-4-4" id="mainContent">

        <div>
            <h2 class="headingText">Members</h2>
            <div>
                <div class="pure-u-1"><p style="text-align:right;margin-right: 10px;">
                        <a href="addcabmembers.php">Add New Members</a>
                    <div class="pure-g dashboard-summary-heading">
                        <div class="pure-u-8-24"><p class="tHeading">Mobile Number</p></div>
                        <div class="pure-u-12-24"><p class="tHeading">Member Name</p></div>
                        <div class="pure-u-4-24"><p class="tHeading">Action</p></div>
                    </div>
                    <?php
                    if ($memberCount > 0) {
                        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

                        foreach ($result as $row) {

                            ?>
                            <div class="pure-g pure-g1 dashboard-summary-heading">
                                <div class="pure-u-8-24"><p
                                        class="dashboard-summary-members"><?='+91-' . substr(trim($row['mobileNumber']), -10); ?></p></div>
                                <div class="pure-u-12-24"><p
                                        class="dashboard-summary-title"><?=$row['Name'];?></p></div>
                                <div class="pure-u-4-24"><p><a href="javascript:;"
                                                               onclick="deleteMember(<?= $row['id'] ?>)">Delete</a>
                                    </p></div>
                            </div>
                            <?php
                        }
                    } else{ ?>

                        <span style='color:#be7f12;font-size:13px; font-weight:bold; margin-left: 15px;'>No results to display!</span>
                    <?php } ?>
                </div>

            </div>
            <!-- end listing groups -->
        </div>

    </div>
</div>
<?php
include_once('footer.php');
?>

<script>
    function deleteMember(id) {
        if (confirm("Are you sure you want to delete this member?")) {
            $.post("deleteClubMember.php", {"id": id}, function (data) {
                if (data == 'success') {
                    location.reload();
                }
            });
        }
    }
</script>