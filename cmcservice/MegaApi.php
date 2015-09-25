<?php

include('connection.php');

$type = '';
if (isset($_POST['type'])) {
    $type = $_POST['type'];
}
$mobile = '';
if (isset($_POST['mobile'])) {
    $mobile = $_POST['mobile'];
}

$platform = '';
if (isset($_POST['platform'])) {
    $platform = $_POST['platform'];
}

if ($type == "CreateBooking") {
    $guestname = '';
    $stmt = $con->query("SELECT FullName FROM registeredusers WHERE Trim(MobileNumber) = Trim('$mobile')");
    $user_exists = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

    if ($user_exists > 0) {
        $guestname = $stmt->fetchColumn();
    }

    $lat = '';
    if (isset($_POST['slat'])) {
        $lat = $_POST['slat'];
    }

    $lon = '';
    if (isset($_POST['slon'])) {
        $lon = $_POST['slon'];
    }

    $elat = '';
    if (isset($_POST['elat'])) {
        $elat = $_POST['elat'];
    }
    $elon = '';
    if (isset($_POST['elon'])) {
        $elon = $_POST['elon'];
    }
    $stime = '';
    if (isset($_POST['stime'])) {
        $stime = $_POST['stime'];
    }

    $url = "http://175.41.138.72:6060/AccessWs.asmx/CreateReservationWithPromoCodeWCFWithNewAPI";
    $mobile = substr($mobile, 4, 14);
    $fields = array(
        'dropLatLng' => $elat . ',' . $elon,
        'pickupLatLng' => $lat . ',' . $lon,
        'guestName' => $guestname,
        'mobile' => $mobile,
        'pickupDateTime' => $stime,
        'password' => '',
        'dropArea' => '',
        'pickupArea' => '',
        'pickupCity' => '',
        'pickupAddress' => '',
        'PromoCode' => '',
        'NooffcabsRequired' => '',
        'platform' => $platform,
        'privateKey' => '6eu5tcf'
    );

} else if ($type == "CancelBooking") {
    $bookingNo = '';
    if (isset($_POST['bookingNo'])) {
        $bookingNo = $_POST['bookingNo'];
    }
    $url = "http://175.41.138.72:6060/AccessWs.asmx/CancelBookingWCFFromNewAPI";
    $mobile = substr($mobile, 4, 14);
    $fields = array(
        'mobile' => $mobile,
        'password' => '',
        'bookingNo' => $bookingNo,
        'platform' => $platform,
        'privateKey' => '6eu5tcf'
    );
}
$myFile = "MegaBooking" . date('YmdHis') . ".xml";
$url = $url . '?' . http_build_query($fields);
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_HEADER, false);
$result = curl_exec($ch);

if ($result === false) {
    echo "Error Number:" . curl_errno($ch) . "<br />";
    echo "Error String:" . curl_error($ch);
} else {
    if ($bookingNo != '') {
        $stmt = $con->query("SELECT co.Seats, co.CabId  FROM cabopen co JOIN cmccabrecords cr ON cr.CabId=co.CabId AND cr.BookingRefNo='" . $bookingNo . "'");
        $rows = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

        if ($rows > 0) {
            $cab = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($cab['Seats'] == '0') {
                $sql = "UPDATE cabopen SET CabStatus='I' WHERE CabId = '" . $cab['CabId'] . "'";
                $stmt = $con->prepare($sql);
                $res = $stmt->execute();
            }
        }

        $stmt = $con->prepare("INSERT INTO cancelledBookings SELECT * FROM cmccabrecords WHERE BookingRefNo='" . $bookingNo . "'");
        $stmt->execute();
        $stmt = $con->prepare("DELETE FROM cmccabrecords WHERE BookingRefNo='" . $bookingNo . "'");
        $stmt->execute();
    }
}
curl_close($ch);
if ($type == "CreateBooking") {
    //echo $result;
    file_put_contents($myFile, $url, FILE_APPEND | LOCK_EX);
    file_put_contents($myFile, $result, FILE_APPEND | LOCK_EX);
    $bookingResult = json_decode($result);
    if ($bookingResult->status == "FAILURE") {
        echo $result;
    } else {
        $Jobno = '';
        $Jobno = $bookingResult->data->Jobno;
        $intAttempt = 1;
        $isFound = false;
        $url = "http://175.41.138.72:6060/AccessWs.asmx/GetStatusByBookingIdWCFWithStatusCodeFromNewAPI";
        $fields = array(
            'bookingNo' => $Jobno,
            'mobile' => $mobile,
            'password' => '',
            'city' => '',
            'privateKey' => '6eu5tcf'
        );

        $url = $url . '?' . http_build_query($fields);
        while ($intAttempt < 6) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_HTTPGET, true);
            curl_setopt($ch, CURLOPT_HEADER, false);
            $result = curl_exec($ch);

            if ($result === false) {
                echo "Error Number:" . curl_errno($ch) . "<br />";
                echo "Error String:" . curl_error($ch);
            }
            curl_close($ch);
            $statusResult = json_decode($result);
            if ($statusResult->status == "SUCCESS") {
                $isFound = true;
                break;
            } else {
                file_put_contents($myFile, $intAttempt, FILE_APPEND | LOCK_EX);
                file_put_contents($myFile, $result, FILE_APPEND | LOCK_EX);
                $intAttempt = $intAttempt + 1;
                sleep(5);
            }
        }
        if ($isFound) {
            $statusResult->Jobno = $Jobno;
            echo json_encode($statusResult);
        } else {
            echo $result;
        }
    }
} else {
    echo $result;
}

?>