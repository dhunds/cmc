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
    var source = 0;
    function initMap() {
        var mapDiv = document.getElementById('map');
        var map = new google.maps.Map(mapDiv, {
            center: {lat: <?=($group['startLat'])?$group['startLat']:'28.4940472';?>, lng: <?=($group['startLat'])?$group['startLon']:'77.0820822';?>},
            zoom: 9
        });
    }
    window.onload = function () {
        var mapOptions = {
            center: new google.maps.LatLng(28.4940472, 77.0820822),
            zoom: 9,
            mapTypeId: google.maps.MapTypeId.ROADMAP
        };

        var map = new google.maps.Map(document.getElementById("map"), mapOptions);
        google.maps.event.addListener(map, 'click', function (e) {

            var lat = e.latLng.lat();
            var lng = e.latLng.lng()

            var latlng = new google.maps.LatLng(lat, lng);
            var geocoder = new google.maps.Geocoder();

            geocoder.geocode({'latLng': latlng}, function (results, status) {
                if (status == google.maps.GeocoderStatus.OK) {
                    if (results[1]) {
                        if (confirm("Confirm this address..\r\n\r\n" + results[1].formatted_address)) {
                            if (source) {
                                document.getElementById("elat").value = lat;
                                document.getElementById("elon").value = lng;
                            } else {
                                document.getElementById("slat").value = lat;
                                document.getElementById("slon").value = lng;
                                source = 1;
                            }
                        }
                    }
                }
            });
        });
    }

    function frmReset() {
        document.getElementById("groups").reset();
        source = 0;
    }
</script>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBqd05mV8c2VTIAKhYP1mFKF7TRueU2-Z0&callback=initMap"
        async defer></script>