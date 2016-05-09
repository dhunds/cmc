<?php
include ('connection.php');

$OwnerNumber = $_REQUEST['OwnerNumber'];

$sql = "SELECT pm.PoolId, pm.OwnerNumber, pm.PoolName, ru.FullName, '1' AS IsPoolOwner FROM userpoolsmaster pm LEFT JOIN
registeredusers ru ON trim(ru.MobileNumber)=trim(pm.OwnerNumber) WHERE trim(pm.OwnerNumber)='".trim($OwnerNumber)."' AND pm.poolType=1
UNION
SELECT pm.PoolId, pm.OwnerNumber, pm.PoolName, ru.FullName, '0' AS IsPoolOwner FROM userpoolsmaster pm
JOIN userpoolsslave ps ON ps.PoolId = pm.PoolId JOIN registeredusers ru ON trim(ru.MobileNumber)=trim(pm.ownerNumber)
WHERE trim(ps.MemberNumber)='".trim($OwnerNumber)."'  AND pm.poolType=1";

$stmt = $con->query($sql);
$no_of_clubs= $con->query("SELECT FOUND_ROWS()")->fetchColumn();

$finalArray = [];
if ($no_of_clubs > 0) {
    while($row = $stmt->fetch())
    {
        $stmt2 = $con->query("SELECT ps.MemberNumber, ru.FullName, pi.imagename FROM userpoolsslave ps LEFT JOIN registeredusers ru ON ru.MobileNumber=trim(ps.MemberNumber) LEFT JOIN userprofileimage pi ON pi.MobileNumber=trim(ps.MemberNumber)  WHERE ps.PoolId='".$row['PoolId']."'");
        $totalClubs = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

        $arrTemp = [];
        $arrTemp1 = [];
        $arrTemp['PoolId'] = $row['PoolId'];
        $arrTemp['PoolName'] = $row['PoolName'];
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
                    $arrTemp2['MemberNumber'] = $row1['MemberNumber'];
                    $arrTemp2['ImageName'] = $row1['imagename'];
                    $arrTemp2['OwnerNumber'] = $row['OwnerNumber'];
                    $arrTemp2['OwnerName'] = $row['FullName'];
                    $arrTemp1[] = $arrTemp2;
                }
            }
        }
        $arrTemp['NoofMembers'] = ($totalClubs-$unregistered) +1;
        $arrTemp['Members'] = $arrTemp1;
        $finalArray[]=$arrTemp;
    }

}

if (count($finalArray) > 0){
    echo json_encode($finalArray);exit;
} else {
    echo "No Users of your Club";exit;
}
