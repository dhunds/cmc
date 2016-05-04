<?php
include('../common.php');

if (isset($_POST['submit']) && $_POST['clubName'] != '') {

    $MobileNumber = '00911234567890';
    $FullName = 'Admin';
    $groupName = $_POST['clubName'];
    $sLat = $_POST['slat'];
    $sLon = $_POST['slon'];
    $eLat = $_POST['elat'];
    $eLon = $_POST['elon'];

    $stmt = $con->query("select FullName, MobileNumber FROM registeredusers WHERE trim(MobileNumber)='" . $MobileNumber . "'");
    $found = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

    if ($found > 0) {

        $stmt = $con->query("Select PoolName From userpoolsmaster WHERE PoolId=".$_REQUEST['id']);
        $group = $stmt->fetch(PDO::FETCH_ASSOC);

        if (trim($group['PoolName']) == trim($groupName)) {
            $found = 0;
        } else {
            $stmt = $con->query("Select * From userpoolsmaster WHERE PoolName='$groupName' AND OwnerNumber = '$MobileNumber'");
            $found = $con->query("SELECT FOUND_ROWS()")->fetchColumn();
        }

        if ($found > 0) {
            echo "Group Name '" . $groupName . "' Already Exist<br />";
        } else {
            $sql = "UPDATE userpoolsmaster SET PoolName= '$groupName', startLat = '$sLat', startLon = '$sLon', endLat = '$eLat', endLon = '$eLon' WHERE PoolId =".$_REQUEST['id'];
            $stmt = $con->prepare($sql);

            if ($stmt->execute()) {
                echo "Group Updated.";
            }
        }
    }
}

$stmt = $con->query("Select * From userpoolsmaster WHERE PoolId=".$_REQUEST['id']);
$group = $stmt->fetch(PDO::FETCH_ASSOC);

?>

<style>
    .divLeft {
        float: left;
        width: 10%;
    }

    .divRight {
        float: left;
        width: 90%;
    }

    #map {
        width: 600px;
        height: 400px;
    }

</style>
<div>
    <div>
        <div style="width:100%;height:100%;float:left;">
            <h2 class="headingText">Add Public Group</h2>

            <form id="groups" method="post" action="">
                <div style="margin-left: 5px;">
                    <div class="divLeft bluetext">&nbsp;&nbsp;Group Name:</div>
                    <div class="divRight bluetext"><input type="text" name="clubName" value="<?=($group['PoolName'])?$group['PoolName']:'';?>"></div>
                    <div style="clear:both;"></div>
                    <br/>

                    <div class="divLeft bluetext">&nbsp;&nbsp;Map:</div>
                    <div class="divRight bluetext" id="map"></div>
                    <div style="clear:both;"></div>
                    <br/>

                    <div class="divLeft bluetext">&nbsp;&nbsp;Start Lat Long:</div>
                    <div class="divRight bluetext">
                        <input type="text" name="slat" id="slat" value="<?=($group['startLat'])?$group['startLat']:'';?>">&nbsp;&nbsp;
                        <input type="text" name="slon" id="slon" value="<?=($group['startLon'])?$group['startLon']:'';?>">
                    </div>
                    <div style="clear:both;"></div>
                    <br/>

                    <div class="divLeft bluetext">&nbsp;&nbsp;End Lat Long:</div>
                    <div class="divRight bluetext">
                        <input type="text" name="elat" id="elat" value="<?=($group['endLat'])?$group['endLat']:'';?>">&nbsp;&nbsp;
                        <input type="text" name="elon" id="elon" value="<?=($group['endLon'])?$group['endLon']:'';?>">
                        <div style="clear:both;"></div>
                        <br/>
                        <div class="bluetext">
                            <input type="submit" name="submit" value="Update" class="cBtn">
                            <input type="button" name="Reset" value="Reset" class="cBtn"  onclick="frmReset();">
                        </div>
                        <div class="divRight bluetext"></div>

                    </div>
            </form>

            <br/>
        </div>

        <div style="clear:both;"></div>
    </div>
</div>
<script>
    function initMap() {
        var mapDiv = document.getElementById('map');
        var map = new google.maps.Map(mapDiv, {
            center: {lat: <?=($group['startLat'])?$group['startLat']:'28.5267268';?>, lng: <?=($group['startLon'])?$group['startLon']:'77.1358162';?>},
            zoom: 11
        });
    }
    window.onload = function () {
        var mapOptions = {
            center: new google.maps.LatLng(<?=($group['startLat'])?$group['startLat']:'28.5267268';?>, <?=($group['startLon'])?$group['startLon']:'77.1358162';?>),
            zoom: 11,
            mapTypeId: google.maps.MapTypeId.ROADMAP
        };

        var map = new google.maps.Map(document.getElementById("map"), mapOptions);

        var marker1 = new google.maps.Marker({
            draggable: true,
            position: new google.maps.LatLng(<?=($group['startLat'])?$group['startLat']:'28.5267268';?>, <?=($group['startLon'])?$group['startLon']:'77.1358162';?>),
            map: map
        });

        var marker2 = new google.maps.Marker({
            draggable: true,
            position: new google.maps.LatLng(<?=($group['endLat'])?$group['endLat']:'28.4936018';?>, <?=($group['endLon'])?$group['endLon']:'77.0861363';?>),
            map: map
        });

        var circle1 = new google.maps.Circle({
            map: map,
            strokeColor: '#4285F4',
            strokeOpacity: 0.6,
            strokeWeight: 1,
            radius: 2500,    // 10 miles in metres
            fillColor: '#7caeff',
            fillOpacity: 0.2,
        });

        var circle2 = new google.maps.Circle({
            map: map,
            strokeColor: '#4285F4',
            strokeOpacity: 0.6,
            strokeWeight: 1,
            radius: 2500,    // 10 miles in metres
            fillColor: '#7caeff',
            fillOpacity: 0.2,
        });

        circle1.bindTo('center', marker1, 'position');
        circle2.bindTo('center', marker2, 'position');

        google.maps.event.addListener(marker1, 'dragend', function (event) {
            document.getElementById("slat").value = event.latLng.lat();
            document.getElementById("slon").value = event.latLng.lng();
        });

        google.maps.event.addListener(marker2, 'dragend', function (event) {
            document.getElementById("elat").value = event.latLng.lat();
            document.getElementById("elon").value = event.latLng.lng();
        });
    }

    function frmReset() {
        document.getElementById("groups").reset();
    }
</script>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBqd05mV8c2VTIAKhYP1mFKF7TRueU2-Z0&callback=initMap"
        async defer></script>