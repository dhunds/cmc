<?php
include ('connection.php');

if (isset($_POST['sLatLon']) && isset($_POST['eLatLon']) && $_POST['sLatLon'] !='' && $_POST['eLatLon'] !='') {
    list($sLat, $sLon) = explode(',', $_POST['sLatLon']);
    list($eLat, $eLon) = explode(',', $_POST['eLatLon']);

    $sql = "SELECT
      PoolId,
      PoolName,
      (
        6371 * acos (
          cos ( radians($sLat) )
          * cos( radians( startLat ) )
          * cos( radians( startLon ) - radians($sLon) )
          + sin ( radians($sLat) )
          * sin( radians( startLat ) )
        )
      ) AS origin,
      (
        6371 * acos (
          cos ( radians($eLat) )
          * cos( radians( endLat ) )
          * cos( radians( endLon ) - radians($eLon) )
          + sin ( radians($eLat) )
          * sin( radians( endLat ) )
        )
      ) AS destination

    FROM userpoolsmaster
    WHERE poolType=2
    HAVING origin < 5 AND destination < 5
    ORDER BY origin, destination";

    $stmt = $con->query($sql);
    $found = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

    if ($found > 0) {
        $groups = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $resp['status'] = 'success';
        $resp['data'] = $groups;

        http_response_code(200);
        header('Content-Type: application/json');
        echo json_encode($resp);
        exit;

    } else {
        http_response_code(200);
        header('Content-Type: application/json');
        echo '{status:"fail", message:"No Groups Available"}';
        exit;
    }
} else {
    http_response_code(500);
    header('Content-Type: application/json');
    echo '{"status":"failed", "message":"Invalid Params."}';
}
