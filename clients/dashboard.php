<?php
include_once('connection.php');
include_once('header.php');
include_once('topmenu.php');

$sql = "SELECT PoolId FROM userpoolsmaster JOIN clientGroups ON clientGroups.groupId=userpoolsmaster.PoolId AND clientGroups.clientId=".$_SESSION['userId'];

$stmt = $con->prepare($sql);
$stmt->execute();
$groupCount = (int) $stmt->rowCount();
$groups = $stmt->fetchAll(PDO::FETCH_ASSOC);

$groupids='';

foreach ($groups as $val) {
    $groupids .=$val['PoolId'].',';
}
$groupids = substr($groupids,0,-1);

$sql = "SELECT PoolSubId FROM userpoolsslave WHERE PoolId IN ($groupids)";
$stmt = $con->prepare($sql);
$stmt->execute();
$groupMemberCount = (int) $stmt->rowCount();

?>

<div class="content pure-u-1-1 pure-u-md-3-4">
    <div class="pure-u-4-4" id="mainContent">
        <div>
            <h4 class="headingText">Account Summary: Nagarro</h4>
            <div class="pure-g pure-g1 dashboard-summary-heading">
                <div class="pure-u-6-24"><p class="dashboard-summary-title">Total Groups</p></div>
                <div class="pure-u-18-24"><p><?=$groupCount;?></p></div>
            </div>
            <div class="pure-g pure-g1 dashboard-summary-heading">
                <div class="pure-u-6-24"><p class="dashboard-summary-title">Total Members</p></div>
                <div class="pure-u-18-24"><p><?=$groupMemberCount;?></p></div>
            </div>
            <div style="clear:both;"></div>
        </div>

    </div>
</div>

<?php
include_once('footer.php');
?>
