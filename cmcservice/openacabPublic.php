<?php
include('connection.php');
include_once('classes/class.notification.php');
include('../common.php');
$objNotification = new Notification();

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
    $sLatLon = $_POST['sLatLon'];
    $eLatLon = $_POST['eLatLon'];
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

    $perKmCharge = perKMChargeIntercity();

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

        $sql = "INSERT INTO cabopen(CabId, MobileNumber, OwnerName, FromLocation, ToLocation, FromShortName, ToShortName, sLatLon, eLatLon, sLat, sLon, eLat, eLon, TravelDate, TravelTime, Seats, RemainingSeats, Distance, OpenTime, ExpTripDuration,ExpStartDateTime,ExpEndDateTime,rideType,perKmCharge) VALUES ('$CabId','$MobileNumber','$OwnerName','$FromLocation','$ToLocation','$FromShortName','$ToShortName','$sLatLon','$eLatLon', '$sLat', '$sLon', '$eLat', '$eLon','$TravelDate','$TravelTime','$Seats','$RemainingSeats','$Distance',now(),'$ExpTripDuration', '$ExpStartDateTime','$ExpEndDateTime','$rideType','$perKmCharge')";

        $stmt = $con->prepare($sql);
        $res = $stmt->execute();

        $sql = "INSERT INTO groupCabs(groupId, cabId) VALUES ($groupId, '$CabId')";

        $stmt = $con->prepare($sql);
        $res = $stmt->execute();

        // Send Members Notification About ride
            $sql = "SELECT ru.MobileNumber, ru.Platform, ru.DeviceToken FROM registeredusers ru JOIN userpoolsslave us ON ru.MobileNumber =  us.MemberNumber WHERE us.PoolId = $groupId AND ru.MobileNumber = '$MobileNumber' AND ru.PushNotification ='on' AND ru.DeviceToken !='' AND ru.lastNotificationSentOn < CURRENT_DATE()";

            $stmt = $con->query($sql);
            $found = $con->query("SELECT FOUND_ROWS()")->fetchColumn();
            
            if ($found) {

                $message = 'You have open rides in your group.';

                $body = array('gcmText' => $message, 'pushfrom' => 'CabId_', 'CabId' => $CabId);
                $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($users as $user) {
                    if ($user['Platform'] == "A") {
                        $gcm_array = array();
                        $gcm_array[] = $user['DeviceToken'];
                        $objNotification->setVariables($gcm_array, $body);
                        $res = $objNotification->sendGCMNotification();
                    } else {
                        $apns_array = array();
                        $apns_array[] = $user['DeviceToken'];
                        $objNotification->setVariables($apns_array, $body);
                        $objNotification->sendIOSNotification();
                    }

                    $sql = "UPDATE registeredusers set lastNotificationSentOn = now() WHERE MobileNumber = '".$user['MobileNumber']."'";
                    $stmt = $con->prepare($sql);
                    $stmt->execute();
                }
            }
            
        // End sending notifications to members

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
    http_response_code(500);
    header('Content-Type: application/json');
    echo '{"status":"failed", "message":"Invalid Params."}';
}