<?php
include_once('connection.php');

$sql = "SELECT c.* FROM cabopen c JOIN cabOwners co ON c.MobileNumber=co.mobileNumber WHERE co.cleintId=".$_SESSION['userId']." AND c.CabStatus='A'";
$stmt = $con->query($sql);
$found = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

$str = '<div class="pure-g dashboard-summary-heading">
    <div class="pure-u-8-24"><p class="tHeading">Location</p></div>
    <div class="pure-u-3-24"><p class="tHeading">Mobile</p></div>
    <div class="pure-u-3-24"><p class="tHeading">Time</p></div>
    <div class="pure-u-2-24"><p class="tHeading">Amount</p></div>
    <div class="pure-u-2-24"><p class="tHeading">Seats</p></div>
    <div class="pure-u-6-24"><p class="tHeading">Remaining Seats</p></div>
</div>';

if ($found > 0) {

  	$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

  	foreach ($result as $val) {

  		$str .= '<div class="pure-g pure-g1 dashboard-summary-heading">
    <div class="pure-u-8-24">
        <p class="dashboard-summary-title">'.$val['FromShortName'].' to '.$val['ToShortName'].'</p>
    </div>
    <div class="pure-u-3-24">
        <p align="center" class="dashboard-summary-title">'.substr(trim($val['MobileNumber']), -10).'</p>
    </div>
    <div class="pure-u-3-24">
        <p align="center" class="dashboard-summary-title">'.$val['TravelTime'].'</p>
    </div>
    <div class="pure-u-2-24">
        <p align="center" class="dashboard-summary-title">'.round($val['Distance'] * $val['perKmCharge']).'</p>
    </div>
    <div class="pure-u-2-24">
        <p align="center" class="dashboard-summary-title">'.$val['Seats'].'</p>
    </div>
    <div class="pure-u-6-24">
        <p align="center" class="dashboard-summary-title">'.$val['RemainingSeats'].'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="updateRide.php?cabId='.$val['CabId'].'">Edit</a> | <a href="javascript:;" onclick="showMembersJoined(\''.$val['CabId'].'\')">View</a> | <a href="javascript:;" onclick="cancelRide(\''.$val['CabId'].'\')">Cancel</a></p>
    </div>
</div>';

  	}

} else {
	$str = '<div class="pure-g pure-g1 dashboard-summary-heading"><div class="pure-u-24-24"><p align="center">No rides available !!</p></div></div>';
}

echo $str; 
exit;