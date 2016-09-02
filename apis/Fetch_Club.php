<?php
include ('connection.php');

$OwnerNumber = $_REQUEST['OwnerNumber'];

$ownerUserId = $_REQUEST['ownerUserId'];

$sql = "SELECT pm.PoolId, pm.ownerUserId, pm.OwnerNumber, pm.PoolName, ru.FullName, '1' AS IsPoolOwner FROM userpoolsmaster pm LEFT JOIN
registeredusers ru ON trim(ru.userId)=trim(pm.ownerUserId) WHERE trim(pm.ownerUserId)='".trim($ownerUserId)."' AND pm.poolType=1
UNION
SELECT pm.PoolId, pm.ownerUserId, pm.OwnerNumber, pm.PoolName, ru.FullName, '0' AS IsPoolOwner FROM userpoolsmaster pm
JOIN userpoolsslave ps ON ps.PoolId = pm.PoolId JOIN registeredusers ru ON trim(ru.userId)=trim(pm.ownerUserId)
WHERE trim(ps.memberUserId)='".trim($ownerUserId)."'  AND pm.poolType=1";

$stmt = $con->query($sql);
$no_of_clubs= $con->query("SELECT FOUND_ROWS()")->fetchColumn();

$finalArray = [];
if ($no_of_clubs > 0) {
    while($row = $stmt->fetch())
    {

        $stmt2 = $con->query("SELECT ps.memberUserId, ps.MemberNumber, ru.FullName, (SELECT imagename FROM userprofileimage WHERE userId= ps.memberUserId LIMIT 0,1) as imagename FROM userpoolsslave ps LEFT JOIN registeredusers ru ON ru.userId=trim(ps.memberUserId) WHERE ps.PoolId='".$row['PoolId']."'");
        $totalClubs = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

        $arrTemp = [];
        $arrTemp1 = [];
        $arrTemp['PoolId'] = $row['PoolId'];
        $arrTemp['PoolName'] = $row['PoolName'];
        $arrTemp['ownerUserId'] = $row['ownerUserId'];
        $arrTemp['OwnerNumber'] = $row['OwnerNumber'];
        $arrTemp['OwnerName'] = $row['FullName'];
        $arrTemp['IsPoolOwner'] = $row['IsPoolOwner'];

        $unregistered=0;
        if ($totalClubs > 0) {
            while ($row1 = $stmt2->fetch()) {

                if ($row1['FullName'] =='') {
                    $unregistered++;
                } else {
                    $arrTemp2 = [];
                    $arrTemp2['FullName'] = $row1['FullName'];
                    $arrTemp2['memberUserId'] = $row1['memberUserId'];
                    $arrTemp2['MemberNumber'] = $row1['MemberNumber'];
                    $arrTemp2['ImageName'] = $row1['imagename'];
                    $arrTemp2['OwnerNumber'] = $row['OwnerNumber'];
                    $arrTemp2['OwnerName'] = $row['FullName'];
                    $arrTemp1[] = $arrTemp2;
                }
            }
        }
        $noOfMembers = ($totalClubs-$unregistered) +1;
        $arrTemp['NoofMembers'] = (string)$noOfMembers;
        $arrTemp['Members'] = $arrTemp1;
        $finalArray[]=$arrTemp;
    }

}

if (count($finalArray) > 0){
    echo json_encode($finalArray);exit;
} else {
    echo "No Users of your Club";exit;
}
