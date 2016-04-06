<?php

function createPublicGroups($con, $groupName, $sLat, $sLon, $eLat, $eLon) {

    $MobileNumber = '00911234567890';


    $sql = "INSERT INTO userpoolsmaster(OwnerNumber, PoolName, PoolStatus, poolType, startLat, startLon, endLat, endLon, Active) VALUES ('$MobileNumber', '$groupName','OPEN', 2, '$sLat', '$sLon', '$eLat', '$eLon','1')";

    $stmt = $con->prepare($sql);
    $res = $stmt->execute();
    $gId =  $con->lastInsertId();

    if ($rGid) {
        $sql = "INSERT INTO userpoolsmaster(OwnerNumber, PoolName, PoolStatus, poolType, startLat, startLon, endLat, endLon, Active, rGid) VALUES ('$MobileNumber', '$groupName','OPEN', 2, '$eLat', '$eLon', '$sLat', '$sLon','1', $gId)";

        $stmt = $con->prepare($sql);
        $res = $stmt->execute();
        $rGid =  $con->lastInsertId();

        if ($rGid) {
            $sql = "UPDATE userpoolsmaster set rGid = $rGid where PoolId = '$gId'";
            $stmt = $con->prepare($sql);
            $res = $stmt->execute();
        }
    }

    if ($res)
        return true;
    else
        return false;
}