<?php
include('connection.php');
include('../common.php');

if (isset($_POST['sLatLon']) && isset($_POST['eLatLon']) && $_POST['sLatLon'] != '' && $_POST['eLatLon'] != '' && isset($_POST['CabId']) && $_POST['CabId'] != '' && isset($_POST['MobileNumber']) && $_POST['MobileNumber'] != '') {

    list($sLat, $sLon) = explode(',', $_POST['sLatLon']);
    list($eLat, $eLon) = explode(',', $_POST['eLatLon']);

    $proximity = rideProximity();

    $CabId = $_POST['CabId'];
    $MobileNumber = $_POST['MobileNumber'];
    $OwnerName = $_POST['OwnerName'];
    $FromLocation = $_POST['FromLocation'];
    $ToLocation = $_POST['ToLocation'];
    $TravelDate = $_POST['TravelDate'];
    $TravelTime = $_POST['TravelTime'];
    $Seats = $_POST['Seats'];
    $RemainingSeats = $_POST['RemainingSeats'];
    $Distance = $_POST['Distance'];

    $ExpTripDuration = $_POST['ExpTripDuration'];
    $FromShortName = $_POST['FromShortName'];
    $ToShortName = $_POST['ToShortName'];

    $rideType = '';

    if (isset($_POST['rideType']) && $_POST['rideType'] != '') {
        $rideType = $_POST['rideType'];
    }

    if (isset($_POST['perKmCharge']) && $_POST['perKmCharge'] != '') {
        $perKmCharge = $_POST['perKmCharge'];
    }

    $perKmCharge = perKMChargeIntracity();

    $dateInput = explode('/', $TravelDate);
    $cDate = $dateInput[1] . '/' . $dateInput[0] . '/' . $dateInput[2];

    $expTrip = strtotime($cDate . ' ' . $TravelTime);
    $newdate = $expTrip + $ExpTripDuration;
    $ExpEndDateTime = date('Y-m-d H:i:s', $newdate);

    $startDate = $expTrip;
    $ExpStartDateTime = date('Y-m-d H:i:s', $startDate);

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
            HAVING origin < ".$proximity." AND destination < ".$proximity."
            ORDER BY origin, destination LIMIT 0,1";

    $stmt = $con->query($sql);
    $found = $con->query("SELECT FOUND_ROWS()")->fetchColumn();
    $createGroup = 0;

    if ($found < 1) {
        $createGroup = createPublicGroups($con, $sLat, $sLon, $eLat, $eLon, $FromShortName, $ToShortName);
        $groupId = $createGroup;

        if ($groupId) {
            // Send Mail to support
            require_once 'mail.php';
            $groupName = $FromShortName . ' to ' . $ToShortName;
            sendGroupCreationMail ($groupName);
        }

    } else {
        $nearbyGroup = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $groupId = $nearbyGroup[0]['PoolId'];
    }

    if ($found > 0 || $createGroup) {

        $sql = "INSERT INTO cabopen(CabId, MobileNumber, OwnerName, FromLocation, ToLocation, FromShortName, ToShortName, TravelDate, TravelTime, Seats, RemainingSeats, Distance, OpenTime, ExpTripDuration,ExpStartDateTime,ExpEndDateTime,rideType,perKmCharge) VALUES ('$CabId','$MobileNumber','$OwnerName','$FromLocation','$ToLocation','$FromShortName','$ToShortName','$TravelDate','$TravelTime','$Seats','$RemainingSeats','$Distance',now(),'$ExpTripDuration', '$ExpStartDateTime','$ExpEndDateTime','$rideType','$perKmCharge')";

        $stmt = $con->prepare($sql);
        $res = $stmt->execute();

        $sql = "INSERT INTO groupCabs(groupId, cabId) VALUES ($groupId, '$CabId')";

        $stmt = $con->prepare($sql);
        $res = $stmt->execute();

        if ($res) {
            http_response_code(200);
            header('Content-Type: application/json');
            echo '{"status":"success", "message":"Ride created."}';
            exit;
        } else {
            http_response_code(200);
            header('Content-Type: application/json');
            echo '{"status":"fail", "message":"An Error Occured, Please try again later!"}';
            exit;
        }
    } else {
        http_response_code(500);
        header('Content-Type: application/json');
        echo '{"status":"failed", "message":"An Error occured, Please try later."}';
    }

} else {
   /* http_response_code(500);
    header('Content-Type: application/json');
    echo '{"status":"failed", "message":"Invalid Params."}';*/
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

                    <div class="divLeft bluetext">&nbsp;&nbsp;Mobile Number:</div>
                    <div class="divRight bluetext"><input type="text" name="mobileNumber"></div>
                    <div style="clear:both;"></div>
                    <br/>

                    <div class="divLeft bluetext">&nbsp;&nbsp;Owner Name:</div>
                    <div class="divRight bluetext"><input type="text" name="ownerName"></div>
                    <div style="clear:both;"></div>
                    <br/>

                    <div class="divLeft bluetext">&nbsp;&nbsp;From Location:</div>
                    <div class="divRight bluetext"><input id="from-location" class="controls" type="text"></div>
                    <div style="clear:both;"></div>
                    <br/>

                    <div class="divLeft bluetext">&nbsp;&nbsp;To Location:</div>
                    <div class="divRight bluetext"><input id="to-location" class="controls" type="text"></div>
                    <div style="clear:both;"></div>
                    <br/>

                    <div class="divLeft bluetext">&nbsp;&nbsp;Seats:</div>
                    <div class="divRight bluetext"><input type="text" name="seats"></div>
                    <div style="clear:both;"></div>
                    <br/>

                    <div class="divLeft bluetext">&nbsp;&nbsp;Distance:</div>
                    <div class="divRight bluetext"><input type="text" name="distance" id="distance"></div>
                    <div style="clear:both;"></div>
                    <br/>

                    <div class="divLeft bluetext">&nbsp;&nbsp;Expected Time:</div>
                    <div class="divRight bluetext"><input type="text" name="expTime" id="expTime"></div>
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

    var mapInstance, sLat, sLon, eLat, eLon, distance;

    function initMap() {
        var mapDiv = document.getElementById('map');
        var map = new google.maps.Map(mapDiv, {
            center: {lat: 28.4940472, lng: 77.0820822},
            zoom: 12
        });

        var inputFrom = /** @type {!HTMLInputElement} */(
            document.getElementById('from-location'));

        var inputTo = /** @type {!HTMLInputElement} */(
            document.getElementById('to-location'));

        var autocompleteFrom = new google.maps.places.Autocomplete(inputFrom);
        autocompleteFrom.bindTo('bounds', map);

        var autocompleteTo = new google.maps.places.Autocomplete(inputTo);
        autocompleteTo.bindTo('bounds', map);

        autocompleteFrom.addListener('place_changed', function() {
            var place = autocompleteFrom.getPlace();

            if (!place.geometry) {
                window.alert("Autocomplete's returned place contains no geometry");
                return;
            }
            sLat = place.geometry.location.lat();
            sLon = place.geometry.location.lng();
            drawLocationFrom(sLat, sLon);
        });

        autocompleteTo.addListener('place_changed', function() {
            var place = autocompleteTo.getPlace();

            if (!place.geometry) {
                window.alert("Autocomplete's returned place contains no geometry");
                return;
            }
            eLat = place.geometry.location.lat();
            eLon = place.geometry.location.lng();

            drawLocationTo(eLat, eLon);
        });
    }

    var drawLocationFrom = function (sLat, sLon) {
        document.getElementById("slat").value = sLat;
        document.getElementById("slon").value = sLon;

        if (eLat != undefined) {
            document.getElementById("distance").value = getDistanceFromLatLonInKm(sLat, sLon, eLat, eLon);
        } else {
            var mapOptions = {
                center: new google.maps.LatLng(sLat, sLon),
                zoom: 12,
                mapTypeId: google.maps.MapTypeId.ROADMAP
            };

            mapInstance = new google.maps.Map(document.getElementById("map"), mapOptions);

            directionsDisplay.setMap(mapInstance);
            directionsDisplay.setPanel(document.getElementById('directionsPanel'));
        }

        var marker = new google.maps.Marker({
            draggable: true,
            position: new google.maps.LatLng(sLat, sLon),
            map: mapInstance
        });

        var circle = new google.maps.Circle({
            map: mapInstance,
            strokeColor: '#4285F4',
            strokeOpacity: 0.6,
            strokeWeight: 1,
            radius: 2500,    // 10 miles in metres
            fillColor: '#7caeff',
            fillOpacity: 0.2
        });
        circle.bindTo('center', marker, 'position');

        google.maps.event.addListener(marker, 'dragend', function (event) {
            document.getElementById("slat").value = event.latLng.lat();
            document.getElementById("slon").value = event.latLng.lng();

            sLat = event.latLng.lat();
            sLon = event.latLng.lng();

            if (eLat != undefined) {
                document.getElementById("distance").value = getDistanceFromLatLonInKm(sLat, sLon, eLat, eLon);
            }

            getAddressFromReverseGeocoding(event.latLng.lat(), event.latLng.lng());
        });
    }

    var drawLocationTo = function (eLat, eLon) {

        document.getElementById("elat").value = eLat;
        document.getElementById("elon").value = eLon;

        if (sLat != undefined) {
            document.getElementById("distance").value = getDistanceFromLatLonInKm(sLat, sLon, eLat, eLon);
        } else {
            var mapOptions = {
                center: new google.maps.LatLng(eLat, eLon),
                zoom: 12,
                mapTypeId: google.maps.MapTypeId.ROADMAP
            };

            mapInstance = new google.maps.Map(document.getElementById("map"), mapOptions);
        }

        var marker = new google.maps.Marker({
            draggable: true,
            position: new google.maps.LatLng(eLat, eLon),
            map: mapInstance
        });

        var circle = new google.maps.Circle({
            map: mapInstance,
            strokeColor: '#4285F4',
            strokeOpacity: 0.6,
            strokeWeight: 1,
            radius: 2500,    // 10 miles in metres
            fillColor: '#7caeff',
            fillOpacity: 0.2
        });
        circle.bindTo('center', marker, 'position');

        google.maps.event.addListener(marker, 'dragend', function (event) {
            document.getElementById("elat").value = event.latLng.lat();
            document.getElementById("elon").value = event.latLng.lng();

            eLat = event.latLng.lat();
            eLon = event.latLng.lng();

            if (sLat != undefined) {
                document.getElementById("distance").value = getDistanceFromLatLonInKm(sLat, sLon, eLat, eLon);
                getTimeFromLatLon (sLat, sLon, eLat, eLon);
            }

            getAddressFromReverseGeocoding(event.latLng.lat(), event.latLng.lng());
        });
    }

    function getAddressFromReverseGeocoding(latitude, longitude){

        var geocoder = new google.maps.Geocoder();
        var latLng = new google.maps.LatLng(latitude, longitude);

        geocoder.geocode({
                latLng: latLng
            },
            function(responses)
            {
                if (responses && responses.length > 0)
                {
                    //console.log(responses);
                    //console.log(responses[0].formatted_address);
                }
                else
                {
                    console.log('Not getting Any address for given latitude and longitude.');
                }
            }
        );
    }

    function getDistanceFromLatLonInKm(lat1,lon1,lat2,lon2) {
        var R = 6371; // Radius of the earth in km
        var dLat = deg2rad(lat2-lat1);  // deg2rad below
        var dLon = deg2rad(lon2-lon1);
        var a =
                Math.sin(dLat/2) * Math.sin(dLat/2) +
                Math.cos(deg2rad(lat1)) * Math.cos(deg2rad(lat2)) *
                Math.sin(dLon/2) * Math.sin(dLon/2)
            ;
        var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        var d = R * c; // Distance in km
        return d;
    }

    function deg2rad(deg) {
        return deg * (Math.PI/180)
    }

    function getTimeFromLatLon (sLat, sLon, eLat, eLon) {
        var directions = new GDirections ();

        var wp = new Array ();
        wp[0] = new GLatLng(sLat, sLon);
        wp[1] = new GLatLng(eLat, eLon);

        directions.loadFromWaypoints(wp);

        GEvent.addListener(directions, "load", function() {
            console.log(directions.getDuration ().seconds + " seconds");
            //$('log').innerHTML = directions.getDuration ().seconds + " seconds";
        });
    }

    function computeTotalDistance(result) {
        var total = 0;
        var time= 0;
        var from=0;
        var to=0;
        var myroute = result.routes[0];
        for (var i = 0; i < myroute.legs.length; i++) {
            total += myroute.legs[i].distance.value;
            time +=myroute.legs[i].duration.text;
            from =myroute.legs[i].start_address;
            to =myroute.legs[i].end_address;


        }
        time = time.replace('hours','H');
        time = time.replace('mins','M');
        total = total / 1000.
        document.getElementById('from').innerHTML = from + '-'+to;
        document.getElementById('duration').innerHTML = time ;
        document.getElementById('total').innerHTML =Math.round( total)+"KM" ;
    }

    function frmReset() {
        document.getElementById("groups").reset();
    }
</script>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBqd05mV8c2VTIAKhYP1mFKF7TRueU2-Z0&libraries=places&callback=initMap" async defer></script>
<!--<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBqd05mV8c2VTIAKhYP1mFKF7TRueU2-Z0&callback=initMap"-->
<!--        async defer></script>-->