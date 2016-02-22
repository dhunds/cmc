<?php
include_once('connection.php');
include_once('header.php');
include_once('topmenu.php');

$sql = "SELECT PoolId, PoolName, (SELECT COUNT(PoolSubId) FROM userpoolsslave WHERE PoolId = userpoolsmaster.PoolId) as totalMembers FROM userpoolsmaster JOIN clientGroups ON clientGroups.groupId=userpoolsmaster.PoolId WHERE clientGroups.clientId=".$_SESSION['userId'];
$stmt = $con->prepare($sql);
$stmt->execute();
$rowCount = (int) $stmt->rowCount();

?>

<div class="content pure-u-1-1 pure-u-md-3-4">
    <div class="pure-u-4-4" id="mainContent">

        <div>
            <h2 class="headingText">Groups</h2>
            <!-- listing groups-->
            <div>
                <div class="pure-u-1"><p style="text-align:right;margin-right: 10px;"><a href="addgroups.php">Add New Groups</a>

                    <div class="pure-g dashboard-summary-heading">
                        <div class="pure-u-16-24"><p class="tHeading">Group Name</p></div>
                        <div class="pure-u-4-24"><p class="tHeading">Total Members</p></div>
                        <div class="pure-u-4-24"><p class="tHeading">Action</p></div>
                    </div>
                    <?php
                        if ($rowCount > 0)
                        {
                            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

                            foreach ($result as $row)
                            {
                    ?>
                    <div class="pure-g pure-g1 dashboard-summary-heading">
                        <div class="pure-u-16-24"><p class="dashboard-summary-title"><?=$row['PoolName']?></p></div>
                        <div class="pure-u-4-24"><p class="dashboard-summary-members"><a href="members.php?id=<?=$row['PoolId']?>" ><?=$row['totalMembers']?></a></p></div>
                        <div class="pure-u-4-24"><p><a href="editgroup.php?id=<?=$row['PoolId']?>">Edit</a> |
                                <a href="addmembers.php?id=<?=$row['PoolId']?>">Add Members</a> |
                                <a href="javascript:;" onclick="deleteGroup(<?=$row['PoolId']?>)">Delete</a> |
                                <?php if ($row['totalMembers'] > 0) { ?>
                                    <a href="export.php?id=<?=$row['PoolId'];?>">Export</a>
                                <?php } ?>
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
    function deleteGroup(groupId){
        if (confirm("Are you sure you want to delete this group?")){
            $.post( "deleteGroup.php", {"groupId": groupId}, function( data ) {
                if (data =='success'){
                    location.reload();
                }
            });
        }
    }
</script>
