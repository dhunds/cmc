<?php
include_once('connection.php');
include_once('header.php');
include_once('topmenu.php');

$rowCount=0;

$sql = "SELECT n.RefId, n.NotificationType, n.Message, n.DateTime, n.SentMemberName, rfc.FriendName, rfc.FriendNumber, ru.FullName, pm.OwnerNumber, pm.	PoolName FROM notifications n JOIN userpoolsmaster pm ON pm.PoolId=n.Poolid JOIN registeredusers ru ON ru.MobileNumber=pm.OwnerNumber JOIN referfriendtoclub rfc ON n.RefId=rfc.RefId  WHERE ru.isAdminType=1 AND ru.MobileNumber='".$_SESSION['mobileNumber']."' AND n.NotificationType='PoolId_Refered' AND n.RefStatus IS NULL ORDER BY DateTime ASC";

$stmt = $con->prepare($sql);
$stmt->execute();
$rowCount = (int)$stmt->rowCount();

?>

<div class="content pure-u-1-1 pure-u-md-3-4">
    <div class="pure-u-4-4" id="mainContent">

        <div>
            <h2 class="headingText" style="margin-bottom: 30px;">Referred Members - Approval required to add to group</h2>
            <!-- listing groups-->
            <div>
                <div class="pure-u-1">
                    <div class="pure-g dashboard-summary-heading">
                        <div class="pure-u-4-24"><p class="tHeading">Full Name</p></div>
                        <div class="pure-u-4-24"><p class="tHeading">Mobile Number</p></div>
                        <div class="pure-u-4-24"><p class="tHeading">Referred By</p></div>
                        <div class="pure-u-8-24"><p class="tHeading">Group Name</p></div>
                        <div class="pure-u-3-24"><p class="tHeading">Date</p></div>
                        <div class="pure-u-1-24"><p class="tHeading">Action</p></div>
                    </div>
                    <?php
                    if ($rowCount > 0)
                    {
                        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

                        foreach ($result as $row)
                        {
                            ?>
                            <div class="pure-g pure-g1 dashboard-summary-heading">
                                <div class="pure-u-4-24"><p> <?=$row['FriendName'];?> </p></div>
                                <div class="pure-u-4-24"><p> <?=$row['FriendNumber'];?> </p></div>
                                <div class="pure-u-4-24"><p> <?=$row['SentMemberName'];?> </p></div>
                                <div class="pure-u-8-24"><p> <?=$row['PoolName'];?> </p></div>
                                <div class="pure-u-3-24"><p> <?=date("M-j-y h:i A", strtotime($row['DateTime']));?> </p></div>
                                <div class="pure-u-1-24"><p><a href="javascript:;" id="approve" onclick="approveUser('<?=$row['RefId'];?>', '<?=$row['FullName'];?>', '<?=$row['OwnerNumber'];?>');">Approve</a></p></div>
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
    function approveUser(refId, name, number){
        $.post( "../cmcservice/referFriendStepTwo.php", {RefId: refId, OwnerName: name, OwnerNumber: number, Accepted:"Yes"}, function( data ) {
            if(data=='SUCCESS'){
                alert('User approved and added to club.');
                location.reload();
            }
        });
    }
</script>
