<?php
include_once('connection.php');
include_once('header.php');
include_once('topmenu.php');

$unauthorised =0;
if (isset($_POST['submit']) && $_POST['groupname'] !='') {
    $sql = "SELECT PoolId, PoolName FROM userpoolsmaster JOIN clientGroups ON clientGroups.groupId=userpoolsmaster.PoolId AND clientGroups.clientId=".$_SESSION['userId']." AND userpoolsmaster.PoolId=".$_POST['groupId'];
    $stmt = $con->prepare($sql);
    $stmt->execute();
    $rowCount = (int) $stmt->rowCount();

    if ($rowCount > 0){
        $sql = "SELECT PoolName FROM userpoolsmaster JOIN clientGroups ON clientGroups.groupId=userpoolsmaster.PoolId AND clientGroups.clientId=".$_SESSION['userId']." AND userpoolsmaster.PoolName='".$_POST['groupname']."'";
        $stmt = $con->prepare($sql);
        $stmt->execute();
        $rowCount = (int) $stmt->rowCount();

        if ($rowCount > 0) {
            $msg = 'You already have a group with this name. Please choose a different name.';
        } else {
            $sql = "UPDATE userpoolsmaster SET PoolName='".$_POST['groupname']."' WHERE PoolId=".$_POST['groupId'];
            $stmt = $con->prepare($sql);

            if ($stmt->execute()){
                $msg = 'Updated Successfully!';
            } else{
                $msg = 'An error occured. Please try again.';
            }
        }


    } else {
        $msg = 'You do not have permission to edit this group.';
        $unauthorised =1;
    }
}

$sql = "SELECT PoolId, PoolName FROM userpoolsmaster JOIN clientGroups ON clientGroups.groupId=userpoolsmaster.PoolId AND clientGroups.clientId=".$_SESSION['userId']." AND userpoolsmaster.PoolId=".$_REQUEST['id'];
$stmt = $con->prepare($sql);
$stmt->execute();
$rowCount = (int) $stmt->rowCount();

if($rowCount > 0){
    $group = $stmt->fetch(PDO::FETCH_ASSOC);
} else {
    $msg = 'You do not have permission to edit this group.';
    $unauthorised =1;
}

?>

<div class="content pure-u-1-1 pure-u-md-3-4">
    <div class="pure-u-4-4" id="mainContent">

        <div>
            <h4 class="tHeading">Edit Group</h4>
            <?php if($msg){ ?>
                <p style="margin-left: 10px;"><?=$msg;?></p>
            <?php } ?>
            <?php if (!$unauthorised) { ?>
            <div style="padding: 15px;">
                <form method="post" action="">
                    <div>
                        <div class="divRight bluetext">Group Name:  <input type="text" name="groupname" placeholder="Group Name" value="<?=$group['PoolName']?>">
                        <input type="hidden" name="groupId" value="<?=$_REQUEST['id']?>">
                        </div>
                        <div style="clear:both;"></div>
                        <br>

                        <div class="divLeft bluetext"><input type="submit" name="submit"
                                                                         value="Update" class="cBtn"></div>
                        <div class="divRight bluetext"></div>
                    </div>
                </form>

                <br/>
            </div>
            <?php } ?>
            <div style="clear:both;"></div>
        </div>

    </div>
</div>

<?php
include_once('footer.php');
?>
