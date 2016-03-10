<?php
include_once('connection.php');
include_once('header.php');
include_once('topmenu.php');

$rowCount=0;

if (isset($_POST['keyword']) && $_POST['keyword'] !='') {
    $sql = "SELECT pm.PoolId, pm.PoolName, ps.MemberName, ps.MemberNumber FROM userpoolsmaster pm
            JOIN userpoolsslave ps ON pm.PoolId=ps.PoolId
            JOIN clientGroups cg ON pm.PoolId=cg.groupId
            WHERE cg.clientId=".$_SESSION['userId']."
            AND (ps.MemberName like '%".$_POST['keyword']."%' OR  ps.MemberNumber like '%".substr(trim($_POST['keyword']), -10)."%')
            ";
    $stmt = $con->prepare($sql);
    $stmt->execute();
    $rowCount = (int)$stmt->rowCount();
}


?>

<div class="content pure-u-1-1 pure-u-md-3-4">
    <div class="pure-u-4-4" id="mainContent">

        <div>
            <h2 class="headingText" style="margin-bottom: 30px;">Search results for '<?=$_POST['keyword'];?>'</h2>
            <!-- listing groups-->
            <div>
                <div class="pure-u-1">
                    <div class="pure-g dashboard-summary-heading">
                        <div class="pure-u-7-24"><p class="tHeading">Number</p></div>
                        <div class="pure-u-7-24"><p class="tHeading">Name</p></div>
                        <div class="pure-u-7-24"><p class="tHeading">Group Name</p></div>
                        <div class="pure-u-3-24"><p class="tHeading">Action</p></div>
                    </div>
                    <?php
                    if ($rowCount > 0)
                    {
                        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

                        foreach ($result as $row)
                        {
                            ?>
                            <div class="pure-g pure-g1 dashboard-summary-heading">
                                <div class="pure-u-7-24"><p class="dashboard-summary-title"><?='+91-' . substr(trim($row['MemberNumber']), -10);?></p></div>
                                <div class="pure-u-7-24"><p class="dashboard-summary-title"><?=($row['MemberName'])?$row['MemberName']:'Not Registered';?></p></div>
                                <div class="pure-u-7-24"><p class="dashboard-summary-members"><?=$row['PoolName']?></div>
                                <div class="pure-u-3-24"><p><a href="javascript:;" onclick="deleteMember(<?= $row['PoolId'] ?>, '<?= $row['MemberNumber'] ?>')">Delete</a></p></div>
                            </div>
                        <?php }
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
