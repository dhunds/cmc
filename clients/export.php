<?php
include_once('connection.php');

$sql = "SELECT PoolId, PoolName FROM userpoolsmaster JOIN clientGroups ON clientGroups.groupId=userpoolsmaster.PoolId AND clientGroups.clientId=" . $_SESSION['userId'] . " AND userpoolsmaster.PoolId=" . $_REQUEST['id'];
$stmt = $con->prepare($sql);
$stmt->execute();
$rowCount = (int)$stmt->rowCount();

$memberCount = 0;

if ($rowCount > 0) {
    $group = $stmt->fetch(PDO::FETCH_ASSOC);

    $sql = "SELECT MemberName, MemberNumber FROM userpoolsslave WHERE PoolId =" . $_REQUEST['id'];
    $stmt = $con->prepare($sql);
    $stmt->execute();
    $memberCount = (int)$stmt->rowCount();

    if ($memberCount > 0) {
        $filename = 'members('.$group['PoolName'].').csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename='.$filename);

        $output = fopen('php://output', 'w');
        fputcsv($output, array('Member Name', 'Member Number'));

        while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
            fputcsv($output, $row);
        }
    }

} else {
    $msg = 'You do not have permission to see members of this group.';
}
