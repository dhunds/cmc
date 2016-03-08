<?php

$sql = "SELECT n.RefId, n.NotificationType, n.Message, n.DateTime, n.SentMemberName, rfc.FriendName, rfc.FriendNumber, ru.FullName, pm.OwnerNumber FROM notifications n JOIN userpoolsmaster pm ON pm.PoolId=n.Poolid JOIN registeredusers ru ON ru.MobileNumber=pm.OwnerNumber JOIN referfriendtoclub rfc ON n.RefId=rfc.RefId  WHERE ru.isAdminType=1 AND n.NotificationType='PoolId_Refered' AND n.RefStatus IS NULL ORDER BY DateTime ASC";

$stmt = $con->prepare($sql);
$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="pure-u-4-4" id="mainContent">
    <h2 class="headingText">Approval Requests</h2>

    <div>
        <div class="pure-g" style="font-size:13px; font-weight:bold;">
            <div class="pure-u-4-24"><p class="tHeading">Full Name</p></div>
            <div class="pure-u-4-24"><p class="tHeading">Mobile Number</p></div>
            <div class="pure-u-4-24"><p class="tHeading">Reffered By</p></div>
            <div class="pure-u-8-24"><p class="tHeading">Message</p></div>
            <div class="pure-u-3-24"><p class="tHeading">Date</p></div>
            <div class="pure-u-1-24"><p class="tHeading">Action</p></div>
        </div>
        <?php if (count($result) > 0) {
            foreach ($result as $row) {
        ?>
        <div class="pure-g pure-g1" style="font-size:13px;">
            <div class="pure-u-4-24"><p style="margin-left: 5px;"> <?=$row['FriendName'];?> </p></div>
            <div class="pure-u-4-24"><p> <?=$row['FriendNumber'];?> </p></div>
            <div class="pure-u-4-24"><p> <?=$row['SentMemberName'];?> </p></div>
            <div class="pure-u-8-24"><p> <?=$row['Message'];?> </p></div>
            <div class="pure-u-3-24"><p> <?=date("M-j-y h:i A", strtotime($row['DateTime']));?> </p></div>
            <div class="pure-u-1-24"><p><a href="javascript:;" id="approve" onclick="approveUser('<?=$row['RefId'];?>', '<?=$row['FullName'];?>', '<?=$row['OwnerNumber'];?>');">Approve</a></p></div>
        </div>
        <?php
            }
        } else { ?>
            <span style='color:Green;font-size:13px; font-weight:bold; margin-left: 5px;'>No results to display!</span>
        <?php } ?>
    </div>
</div>
<script>
    function approveUser(refId, name, number){
        $.post( "../cmcservice/referFriendStepTwo.php", {RefId: refId, OwnerName: name, OwnerNumber: number, Accepted:"Yes"}, function( data ) {
            if(data=='SUCCESS'){
                alert('User approved to add to club.');
                location.reload();
            }
        });
    }
</script>