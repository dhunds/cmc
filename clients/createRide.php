<?php
include_once('connection.php');
include_once('header.php');
include_once('topmenu.php');
include('../common.php');

if (isset($_POST['submit'])) {
    $error = checkPostForBlank (array('mobileNumber', 'ownerName', 'email', 'vehicle', 'registrationNumber', 'FromLocation', 'ToLocation', 'FromShortName', 'ToShortName', 'seats', 'distance', 'expTime', 'slat', 'slon', 'elat', 'elon', 'fromCity',  'toCity'));

    if (!$error) {
//    echo '<pre>';
//    print_r($_POST);
//    die;
        $client_id = $_SESSION['userId'];
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
        $vehicleId = $_POST['vehicle'];
        list($TravelDate, $TravelTime) = explode(" ", $_POST['time']);
        $TravelTime = strtoupper($TravelTime);
        $Seats = $_POST['seats'];
        $RemainingSeats = $_POST['seats'];
        $Distance = $_POST['distance'];
        $ExpTripDuration = ($_POST['expTime'] * 60);  // in seconds
        $FromShortName = $_POST['FromShortName'];
        $ToShortName = $_POST['ToShortName'];
        $rideType = 4;
        $dateInput = explode('/', $TravelDate);
        $cDate = $dateInput[1] . '/' . $dateInput[0] . '/' . $dateInput[2];

        $expTrip = strtotime($cDate . ' ' . $TravelTime);
        $newdate = $expTrip + $ExpTripDuration;
        $ExpEndDateTime = date('Y-m-d H:i:s', $newdate);

        $startDate = $expTrip;

        $ExpStartDateTime = date('Y-m-d H:i:s', $startDate);
        
        $TravelTime = date('g:i A', strtotime($TravelTime));
        $fromCity = $_POST['fromCity'];
        $toCity = $_POST['toCity'];

        if (isIntracityRide($fromCity, $toCity)){
            $isIntercity =0;
            $perKmCharge = perKMChargeIntracity();
        } else {
            $isIntercity =1;
            $perKmCharge = perKMChargeIntercity();
        }

        $stmt = $con->query("SELECT FullName FROM registeredusers WHERE MobileNumber = '".$MobileNumber."'");
        $userExists = $con->query("SELECT FOUND_ROWS()")->fetchColumn();


         if ($userExists < 1) {
            $sql = "INSERT INTO registeredusers (FullName, Password, MobileNumber, DeviceToken, Email, Gender, DOB, Platform, PushNotification, LastLoginDateTime, SingleUsePassword, SingleUseExpiry, SingleUseVerified, ResetPasswordOTP, CreatedOn, isAdminType, referralCode, usedReferralCode, totalCredits, defaultPaymentOption, defaultPaymentAcceptOption, type, status, socialId, socialType, mobikwikToken) VALUES ('".$_POST['ownerName']."', '', '".$MobileNumber."', '', '".$_POST['email']."', '', '', '', 'off', CURRENT_TIMESTAMP, NULL, NULL, '0', NULL, NULL, '0', '', '', '', '1', '1', '2', '1', '', '', '')";

            $stmt = $con->prepare($sql);
            $stmt->execute();

            $sql = "INSERT INTO  userprofileimage (MobileNumber ,imagename)VALUES ('".$MobileNumber."',  '');";
            $stmt = $con->prepare($sql);
            $stmt->execute();

         }

        $stmt = $con->query("SELECT id, cleintId FROM cabOwners WHERE mobileNumber = '".$MobileNumber."'");
        $userAssociated = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

        if ($userAssociated > 0) {
            $cabOwner = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($cabOwner['cleintId'] != $_SESSION['userId']){
                $msg = 'Driver already linked to some other corporate account. Please write to support@ishareryde.com with driver phone number and name for resolution. Ride has not been created !';
                $error=1;
            }
        } else {
            $sql = "INSERT INTO  cabOwners (`mobileNumber` ,`Name`, `cleintId`)VALUES ('".$MobileNumber."',  '".$_POST['ownerName']."', '$client_id');";
            $stmt = $con->prepare($sql);
            $stmt->execute();
        }

        if (!$error){

            $stmt = $con->query("SELECT id FROM userVehicleDetail WHERE mobileNumber = '".$MobileNumber."'");
            $isVehicleAttached = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

            if ($isVehicleAttached < 1) {
                $stmt = $con->prepare("INSERT INTO userVehicleDetail SET vehicleId = '$vehicleId',  isCommercial=1, registrationNumber='".$_POST['registrationNumber']."', mobileNumber = '".$MobileNumber."', created=now()");
                $stmt->execute();
            } else {
                $stmt = $con->prepare("UPDATE userVehicleDetail SET vehicleId = '$vehicleId',  isCommercial=1, registrationNumber='".$_POST['registrationNumber']."' WHERE mobileNumber = '".$MobileNumber."'");
                $stmt->execute();
            }

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

                $sql = "INSERT INTO cabopen(CabId, MobileNumber, OwnerName, FromLocation, ToLocation, FromShortName, ToShortName, fromCity, toCity, sLatLon, eLatLon, sLat, sLon, eLat, eLon, TravelDate, TravelTime, Seats, RemainingSeats, Distance, OpenTime, ExpTripDuration,ExpStartDateTime,ExpEndDateTime,rideType,perKmCharge,isIntercity) VALUES ('$CabId','$MobileNumber','$OwnerName','$FromLocation','$ToLocation','$FromShortName','$ToShortName', '$fromCity', '$toCity', '$sLatLon','$eLatLon', '$sLat', '$sLon', '$eLat', '$eLon','$TravelDate','$TravelTime','$Seats','$RemainingSeats','$Distance',now(),'$ExpTripDuration', '$ExpStartDateTime','$ExpEndDateTime','$rideType','$perKmCharge', $isIntercity)";
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
                            <div class="divRight bluetext"><input type="text" name="ownerName" id="ownerName" placeholder="Owner Name"  style="width:300px;"></span></div>
                            <div style="clear:both;"></div>
                            <br/>

                            <div class="divRight bluetext"><input type="text" name="email" id="email" placeholder="Email"  style="width:300px;"></div>
                            <div style="clear:both;"></div>
                            <br/>

                            <div class="divRight bluetext">
                            <select name="vehicle" id="vehicle" style="width:300px;">
                                <option value="">Select Vehicle</option>
                            <?php
                                $stmt = $con->query("SELECT * FROM vehicle");

                                while($row = $stmt->fetch(PDO::FETCH_ASSOC)){

                            ?>
                              <option value="<?=$row['id'];?>"><?=$row['vehicleModel'];?></option>
                            <?php } ?>
                            </select>
                            </div>
                            <div style="clear:both;"></div>
                            <br/>

                            <div class="divRight bluetext"><input type="text" name="registrationNumber" id="registrationNumber" placeholder="Registration Number"  style="width:300px;"></div>
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
                                <input name="fromCity" id="fromCity" type="hidden">
                                <input name="toCity" id="toCity" type="hidden">
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
        <p align="center" class="dashboard-summary-title">'.$val['RemainingSeats'].'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="updateRide.php?cabId='.$val['CabId'].'">Edit</a> | <a href="javascript:;" onclick="showMembersJoined(\''.$val['CabId'].'\')">View</a></p>
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

                for (i = 0; i < place.address_components.length; i++) {
                    if (place.address_components[i].types[0] == "locality"){
                        document.getElementById('fromCity').value = place.address_components[i].long_name;
                    }
                }

                if (document.getElementById('fromCity').value==''){
                    alert('From address could not be located');
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

                for (i = 0; i < place.address_components.length; i++) {
                    if (place.address_components[i].types[0] == "locality"){
                        document.getElementById('toCity').value = place.address_components[i].long_name;
                    }
                }

                if (document.getElementById('toCity').value==''){
                    alert('To address could not be located');
                }
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
                    document.getElementById("ownerName").value = '';
                    document.getElementById("email").value = '';
                    document.getElementById("vehicle").value = '';
                    document.getElementById("registrationNumber").value = '';
                } else if (data =='fail'){
                    document.getElementById("ownerName").value = '';
                    document.getElementById("email").value = '';
                    document.getElementById("vehicle").value = '';
                    document.getElementById("registrationNumber").value = '';
                    //document.getElementById("driverDetails").innerHTML = " Invalid Mobile Number!";
                } else {
                    var details = data.split("~");
                    document.getElementById("ownerName").value = details[0];
                    document.getElementById("email").value = details[1];
                    document.getElementById("vehicle").value = details[2];
                    document.getElementById("registrationNumber").value = details[3];
                    //document.getElementById("driverDetails").innerHTML = " "+details[0]+" ("+details[1]+", "+details[2]+")";
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

        function showMembersJoined(cabId) {
            var left = (screen.width/2)-(450/2);
            var top = (screen.height/2)-(450/2);
             return window.open('joinedMembers.php?'+cabId, 'iShareRyde', 'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=no, resizable=no, copyhistory=no, width=450, height=450, top='+top+', left='+left);
        }
    </script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBqd05mV8c2VTIAKhYP1mFKF7TRueU2-Z0&libraries=places&callback=initMap" async defer></script>
    <!--<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBqd05mV8c2VTIAKhYP1mFKF7TRueU2-Z0&callback=initMap"-->
    <!--        async defer></script>-->

<?php
    include_once('footer.php');
?>