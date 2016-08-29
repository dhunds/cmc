<?php
include ('connection.php');

if(isset($_POST['cabId']) && $_POST['cabId'] !=''){
    if(isset($_POST['location']) && $_POST['location'] !=''){
        list($lat, $lng) = explode(',', $_POST['location']);

        $sql = "UPDATE cabopen SET  ownerLat = '".$lat."', ownerLng='".$lng."', locationUpdatedAt=now() WHERE CabId='".$_POST['cabId']."'";
        try {
            $con->exec($sql);
            header('Content-Type: application/json');
            echo '{"msg": "Owner location updated."}';
            exit;
        } catch (PDOException $e) {
            header('Content-Type: application/json');
            echo '{"msg": "'.$e->getMessage().'"}';
            exit;
        }
    }else{
        $stmt = $con->prepare("SELECT ownerLat, ownerLng, DATE_FORMAT(locationUpdatedAt,'%d %b %Y %h:%i:%s %p') AS locationUpdatedAt  FROM cabopen WHERE CabId='".$_POST['cabId']."'");
        $stmt->execute();
        $row = $stmt->fetch();
        if(!empty($row)){
            $resp = array('msg'=>'success', 'ownerLat'=>$row['ownerLat'], 'ownerLng'=>$row['ownerLng'], 'locationUpdatedAt'=>$row['locationUpdatedAt']);

            header('Content-Type: application/json');
            echo json_encode($resp);
            exit;
        }else{
            header('Content-Type: application/json');
            echo '{"msg": "Owner location not set."}';
            exit;
        }
    }
}