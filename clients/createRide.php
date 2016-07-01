<?php
include_once('connection.php');
include_once('header.php');
include_once('topmenu.php');
include('../common.php');

if (isset($_POST['submit'])) {
    $error = checkPostForBlank (array('mobileNumber', 'ownerName', 'FromLocation', 'ToLocation', 'FromShortName', 'ToShortName', 'seats', 'distance', 'expTime', 'slat', 'slon', 'elat', 'elon'));

    if (!$error) {
   // echo '<pre>';
    //print_r($_POST);
    //die;
        $sLat = $_POST['slat'];
        $sLon = $_POST['slon'];
        $eLat = $_POST['elat'];
        $eLon = $_POST['elon'];
        $sLatLon = $sLat.','.$sLon;
        $eLatLon = $eLat.','.$eLon;

        $proximity = rideProximity();

        $MobileNumber = '0091' . substr(trim($_POST['mobileNumber']), -10);
        $CabId = $MobileNumber.time();
        $OwnerName = $_POST['ownerName'];
        $FromLocation = $_POST['FromLocation'];
        $ToLocation = $_POST['ToLocation'];
        
        list($TravelDate, $TravelTime) = explode(" ", $_POST['time']);
        $TravelTime = strtoupper($TravelTime);
        $Seats = $_POST['seats'];
        $RemainingSeats = $_POST['seats'];
        $Distance = $_POST['distance'];
        $ExpTripDuration = ($_POST['expTime'] * 60);  // in seconds
        $FromShortName = $_POST['FromShortName'];
        $ToShortName = $_POST['ToShortName'];
        $rideType = 4;
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
                HAVING origin < " . $proximity . " AND destination < " . $proximity . "
                ORDER BY origin, destination LIMIT 0,1";

        $stmt = $con->query($sql);
        $found = $con->query("SELECT FOUND_ROWS()")->fetchColumn();
        $createGroup = 0;

        if ($found < 1) {
            $createGroup = createPublicGroups($con, $sLat, $sLon, $eLat, $eLon, $FromShortName, $ToShortName);
            $groupId = $createGroup;

            if ($groupId) {
                // Send Mail to support
                require_once '../cmcservice/mail.php';
                $groupName = $FromShortName . ' to ' . $ToShortName;
                sendGroupCreationMail($groupName);
            }

        } else {
            $nearbyGroup = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $groupId = $nearbyGroup[0]['PoolId'];
        }

        if ($found > 0 || $createGroup) {

            $sql = "INSERT INTO cabopen(CabId, MobileNumber, OwnerName, FromLocation, ToLocation, FromShortName, ToShortName, sLatLon, eLatLon, sLat, sLon, eLat, eLon, TravelDate, TravelTime, Seats, RemainingSeats, Distance, OpenTime, ExpTripDuration,ExpStartDateTime,ExpEndDateTime,rideType,perKmCharge) VALUES ('$CabId','$MobileNumber','$OwnerName','$FromLocation','$ToLocation','$FromShortName','$ToShortName','$sLatLon','$eLatLon', '$sLat', '$sLon', '$eLat', '$eLon','$TravelDate','$TravelTime','$Seats','$RemainingSeats','$Distance',now(),'$ExpTripDuration', '$ExpStartDateTime','$ExpEndDateTime','$rideType','$perKmCharge')";
           //echo $sql;die;
            $stmt = $con->prepare($sql);
            $res = $stmt->execute();

            $sql = "INSERT INTO groupCabs(groupId, cabId) VALUES ($groupId, '$CabId')";

            $stmt = $con->prepare($sql);
            $res = $stmt->execute();

            if ($res) {
                $msg = 'Ride created.';
            } else {
                $msg = 'An Error occured, Please try later.';
            }
        } else {
            $msg = 'An Error occured, Please try later.';
        }
    } else {
        $msg = 'Please fill all the boxes.';
    }
}

function checkPostForBlank($arrParams){
    $error = 0;
    foreach ($arrParams as $value) {
        if (!isset($_POST[$value]) || $_POST[$value] =='') {
            $error = 1;
        }
    }
    return $error;
}

?>
    <div class="content pure-u-1-1 pure-u-md-3-4">

        <div class="pure-u-4-4" id="mainContent">

            <div style="width:40%; float:left;">
                <h4 class="tHeading">Create Ride</h4>
                <p style="margin-left: 10px;" id="errormsg"><?=$msg;?></p>
                <div style="padding: 15px;">
                    <form method="post" action="">
                        <div>
                            <div class="divRight bluetext"><input type="text" name="mobileNumber" id="mobileNumber" placeholder="Owner Number" onblur="checkDriverDetails()" style="width:300px;"><span id="driverDetails"></span></div>
                            <div style="clear:both;"></div>
                            <br/>

                            <div class="divRight bluetext"><input id="from-location" name="FromLocation" class="controls" type="text" placeholder="From Location" style="width:300px;">
                            </div>
                            <div style="clear:both;"></div>
                            <br/>

                            <div class="divRight bluetext"><input id="to-location" name="ToLocation" class="controls" type="text" placeholder="To Location" style="width:300px;">
                            </div>
                            <div style="clear:both;"></div>
                            <br/>
                            <div class="divRight bluetext"><input id="time" name="time" class="controls" type="text" placeholder="Time" style="width:300px;" readonly> <img src="images/calendar.png" id="dtFrom">&nbsp;
                        <script type="text/javascript">
                            Calendar.setup({
                                inputField: 'time',
                                ifFormat: "%d/%m/%Y %H:%M %P",
                                button: "dtFrom",
                                singleClick: true,
                                showsTime:true,
                                timeFormat:12,
                                electric:true
                            });
                        </script>

                            </div>
                            <div style="clear:both;"></div>
                            <br/>

                            <div class="divRight bluetext"><input type="text" name="seats" placeholder="Seats" style="width:300px;">
                            </div>
                            <div style="clear:both;"></div>
                            <br/>

                            <div class="divRight bluetext" id="map" style="display: none;"></div>
                            <div style="clear:both;"></div>
                            <br/>

                            <div class="divRight bluetext">
                                <input type="hidden" name="distance" id="distance">
                                <input  type="hidden" name="expTime" id="expTime">
                                <input name="FromShortName" id="FromShortName" type="hidden">
                                <input name="ToShortName" id="ToShortName" type="hidden">
                                <input type="hidden" name="ownerName" id="ownerName">
                                <input type="hidden" name="slat" id="slat">
                                <input type="hidden" name="slon" id="slon">
                                <input type="hidden" name="elat" id="elat">
                                <input type="hidden" name="elon" id="elon">
                                <input type="submit" name="submit" value="Create Ride" class="cBtn">
                                <input type="button" name="Reset" value="Reset" class="cBtn"  onclick="frmReset();">
                            </div>
                            <div class="divRight bluetext"></div>
                        </div>
                    </form>
                    <br/>
                </div>
            </div>
            <div style="width:58%; float:right;">
                
                <div style="padding: 1px;" >
                    <span id="rides">
                    <div class="pure-g dashboard-summary-heading">
                        <div class="pure-u-10-24"><p class="tHeading">Location</p></div>
                        <div class="pure-u-4-24"><p class="tHeading">Mobile Number</p></div>
                        <div class="pure-u-3-24"><p class="tHeading">Time</p></div>
                        <div class="pure-u-2-24"><p class="tHeading">Seats</p></div>
                        <div class="pure-u-5-24"><p class="tHeading">Remaining Seats</p></div>
                    </div>
                        <?php 
                           $sql = "SELECT c.* FROM cabopen c JOIN cabOwners co ON c.MobileNumber=co.mobileNumber WHERE co.cleintId=".$_SESSION['userId']." AND c.CabStatus='A'";
$stmt = $con->query($sql);
$found = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

$str = '';

if ($found > 0) {

    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($result as $val) {

        $str .= '<div class="pure-g pure-g1 dashboard-summary-heading">
    <div class="pure-u-10-24">
        <p class="dashboard-summary-title">'.$val['FromShortName'].' to '.$val['ToShortName'].'</p>
    </div>
    <div class="pure-u-4-24">
        <p align="center" class="dashboard-summary-title">'.substr(trim($val['MobileNumber']), -10).'</p>
    </div>
    <div class="pure-u-3-24">
        <p align="center" class="dashboard-summary-title">'.$val['TravelTime'].'</p>
    </div>
    <div class="pure-u-2-24">
        <p align="center" class="dashboard-summary-title">'.$val['Seats'].'</p>
    </div>
    <div class="pure-u-5-24">
        <p align="center" class="dashboard-summary-title">'.$val['RemainingSeats'].'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="updateRide.php?cabId='.$val['CabId'].'">Edit</a></p>
    </div>
</div>';

    }

} else {
    $str = '<div class="pure-g pure-g1 dashboard-summary-heading"><div class="pure-u-24-24"><p align="center">No rides available !!</p></div></div>';
}
echo $str;
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>

        var mapInstance, sLat, sLon, eLat, eLon, distance, directionsDisplay, directionsService;


        function initMap() {
            var mapDiv = document.getElementById('map');

            var map = new google.maps.Map(mapDiv, {
                center: {lat: 28.4940472, lng: 77.0820822},
                zoom: 12
            });

            directionsService = new google.maps.DirectionsService();

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

                document.getElementById('FromShortName').value = createShortAddress(document.getElementById('from-location').value);
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

                document.getElementById('ToShortName').value = createShortAddress(document.getElementById('to-location').value);
            });
        }

        var drawLocationFrom = function (sLat, sLon) {
            document.getElementById("slat").value = sLat;
            document.getElementById("slon").value = sLon;

            if (eLat != undefined) {
                getDistanceAndTime (sLat+","+sLon, eLat+","+eLon);
            } else {
                var mapOptions = {
                    center: new google.maps.LatLng(sLat, sLon),
                    zoom: 12,
                    mapTypeId: google.maps.MapTypeId.ROADMAP
                };

                mapInstance = new google.maps.Map(document.getElementById("map"), mapOptions);
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
                    getDistanceAndTime (sLat+","+sLon, eLat+","+eLon);
                }
            });
        }

        var drawLocationTo = function (eLat, eLon) {

            document.getElementById("elat").value = eLat;
            document.getElementById("elon").value = eLon;

            if (sLat != undefined) {
                getDistanceAndTime (sLat+","+sLon, eLat+","+eLon);
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
                    getDistanceAndTime (sLat+","+sLon, eLat+","+eLon);
                }
            });
        }

        function getDistanceAndTime (sLatLong, eLatLong) {
            var request = {
                origin: sLatLong, // LatLng|string
                destination: eLatLong, // LatLng|string
                travelMode: google.maps.DirectionsTravelMode.DRIVING
            };

            directionsService.route( request, function( response, status ) {
                if ( status === 'OK' ) {
                    var point = response.routes[ 0 ].legs[ 0 ];
                    document.getElementById("expTime").value = Math.round(point.duration.value / 60);
                    document.getElementById("distance").value = Math.round(point.distance.value / 1000);
                }
            } );
        }

        function frmReset() {
            document.getElementById("groups").reset();
        }

        function createShortAddress(address) {
            var shortAddress;

            if (address !='') {
                var arrAddress = address.split(",");
                addressCount = arrAddress.length;

                if (addressCount==4){
                    shortAddress = arrAddress[0].trim() + ', ' + arrAddress[1].trim();
                } else if(addressCount ==5){
                    shortAddress = arrAddress[1].trim() + ', ' + arrAddress[2].trim();
                } else if(addressCount ==6){
                    shortAddress = arrAddress[2].trim() + ', ' + arrAddress[3].trim();
                } else {
                    shortAddress = arrAddress[0].trim() + ', ' + arrAddress[1].trim();
                }
            }
            return shortAddress;
        }

        function checkDriverDetails(){
            var mobileNumber = document.getElementById("mobileNumber").value;
            $.post( "checkDriverDetails.php", {"mobileNumber": mobileNumber}, function( data ) {
                
                if (data =='') {
                    document.getElementById("driverDetails").innerHTML = " Mobile number required!";
                } else if (data =='fail'){
                    document.getElementById("driverDetails").innerHTML = " Invalid Mobile Number!";
                } else {
                    var details = data.split("~");
                    document.getElementById("ownerName").value = details[0];
                    document.getElementById("driverDetails").innerHTML = " "+details[0]+" ("+details[1]+", "+details[2]+")";
                }

            });
        }

        $(document).ready(
            function() {
                setInterval(function() {
                    $.post( "clientRides.php", function( data ) {
                        document.getElementById("rides").innerHTML = data;
                    });
                }, 60000);
            }
        );

    </script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBqd05mV8c2VTIAKhYP1mFKF7TRueU2-Z0&libraries=places&callback=initMap" async defer></script>
    <!--<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBqd05mV8c2VTIAKhYP1mFKF7TRueU2-Z0&callback=initMap"-->
    <!--        async defer></script>-->

<?php
    include_once('footer.php');
?>