<?php

include_once('connection.php');
include_once('header.php');
include_once('topmenu.php');

$sql = "SELECT PoolId, PoolName FROM userpoolsmaster JOIN clientGroups ON clientGroups.groupId=userpoolsmaster.PoolId AND clientGroups.clientId=" . $_SESSION['userId'] . " AND userpoolsmaster.PoolId=" . $_REQUEST['id'];
$stmt = $con->prepare($sql);
$stmt->execute();
$rowCount = (int)$stmt->rowCount();

$memberCount = 0;

if ($rowCount > 0) {
    $group = $stmt->fetch(PDO::FETCH_ASSOC);
    $sql = "SELECT * FROM userpoolsslave WHERE PoolId =" . $_REQUEST['id'];
    $stmt = $con->prepare($sql);
    $stmt->execute();
    $memberCount = (int)$stmt->rowCount();
} else {
    $msg = 'You do not have permission to see members of this group.';
}

?>
<div class="content pure-u-1-1 pure-u-md-3-4">
    <div class="pure-u-4-4" id="mainContent">

        <div>
            <h2 class="headingText">Members: <?=$group['PoolName'];?></h2>
            <?php if ($msg) { ?>
                <p style="margin-left: 10px;"><?= $msg; ?></p>
            <?php } ?>
            <!-- listing groups-->
            <div>
                <div class="pure-u-1"><p style="text-align:right;margin-right: 10px;">
                        <a href="addmembers.php?id=<?= $_REQUEST['id'] ?>">Add New Members</a>
                        <?php
                        if ($memberCount > 0) { ?>
                            <a href="export.php?id=<?= $_REQUEST['id'] ?>">Export</a>
                        <?php } ?>
                    <div class="pure-g dashboard-summary-heading">
                        <div class="pure-u-12-24"><p class="tHeading">Member Name</p></div>
                        <div class="pure-u-8-24"><p class="tHeading">Mobile Number</p></div>
                        <div class="pure-u-4-24"><p class="tHeading">Action</p></div>
                    </div>
                    <?php
                    if ($memberCount > 0) {
                        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

                        foreach ($result as $row) {

                            ?>
                            <div class="pure-g pure-g1 dashboard-summary-heading">
                                <div class="pure-u-12-24"><p
                                        class="dashboard-summary-title"><?= $row['MemberName'] ?></p></div>
                                <div class="pure-u-8-24"><p
                                        class="dashboard-summary-members"><?= $row['MemberNumber'] ?></p></div>
                                <div class="pure-u-4-24"><p><a href="javascript:;"
                                                               onclick="deleteMember(<?= $row['PoolId'] ?>, '<?= $row['MemberNumber'] ?>')">Delete</a>
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
    function deleteMember(poolId, memberNumber) {
        if (confirm("Are you sure you want to delete this member?")) {
            $.post("deleteMember.php", {"poolid": poolId, "usernumber": memberNumber}, function (data) {
                if (data == 'success') {
                    location.reload();
                }
            });
        }
    }
</script>