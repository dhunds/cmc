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
        $stmt = $con->query("Select * From userpoolsmaster WHERE PoolName='$groupName' AND OwnerNumber = '$MobileNumber'");
        $found = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

        if ($found > 0) {
            echo "Group Name '" . $groupName . "' Already Exist<br />";
        } else {
            $createGroup = createPublicGroups($con, $groupName, $sLat, $sLon, $eLat, $eLon);
            if ($createGroup == true) {
                echo "Group Created.";
            }
        }
    }
}
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
                    <div class="divRight bluetext"><input type="text" name="clubName"></div>
                    <div style="clear:both;"></div>
                    <br/>

                    <div class="divLeft bluetext">&nbsp;&nbsp;Map:</div>
                    <div class="divRight bluetext" id="map"></div>
                    <div style="clear:both;"></div>
                    <br/>

                    <div class="divLeft bluetext">&nbsp;&nbsp;Start Lat Long:</div>
                    <div class="divRight bluetext"><input type="text" name="slat" id="slat">&nbsp;&nbsp;<input
                            type="text" name="slon" id="slon"></div>
                    <div style="clear:both;"></div>
                    <br/>

                    <div class="divLeft bluetext">&nbsp;&nbsp;End Lat Long:</div>
                    <div class="divRight bluetext">
                        <input type="text" name="elat" id="elat">&nbsp;&nbsp;<input type="text" name="elon" id="elon">
                        <div style="clear:both;"></div>
                        <br/>
                        <div class="bluetext">
                            <input type="submit" name="submit" value="Create Group" class="cBtn">
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
            center: {lat: 28.4940472, lng: 77.0820822},
            zoom: 12
        });
    }
    window.onload = function () {
        var mapOptions = {
            center: new google.maps.LatLng(28.5267268, 77.1358162),
            zoom: 12,
            mapTypeId: google.maps.MapTypeId.ROADMAP
        };

        var map = new google.maps.Map(document.getElementById("map"), mapOptions);

        var marker1 = new google.maps.Marker({
            draggable: true,
            position: new google.maps.LatLng(28.5267268, 77.1358162),
            map: map
        });

        var marker2 = new google.maps.Marker({
            draggable: true,
            position: new google.maps.LatLng(28.4936018, 77.0861363),
            map: map
        });

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