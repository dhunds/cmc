<?php
include('connection.php');
const CLIENT_ID = '7e22de1177fb4ac4b173a8653c72e1f3';
const BOOKING_URL = 'https://devapi.olacabs.com/v1/bookings/create';
const CANCELLATION_URL = 'https://devapi.olacabs.com/v1/bookings/cancel';


//Ola Cancellation
$_POST = $_GET;
if (isset($_POST['type']) && $_POST['type'] == 'cancellation') {

    $access_token = $_POST['access_token'];

    $params = array(
        'crn' => $_REQUEST['booking_id']
    );

    $cancel_url = CANCELLATION_URL . '?' . http_build_query($params);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $cancel_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HTTPGET, true);

    curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-APP-TOKEN: ' . CLIENT_ID, 'Authorization: Bearer ' . $access_token));

    $resp_cancellation = curl_exec($ch);
    curl_close($ch);


    if ($resp_cancellation->status == 'SUCCESS' && isset($_POST['booking_id']) && $_POST['booking_id'] != '') {
        //Mark Solo booking archieved
        $stmt = $con->query("SELECT co.Seats, co.CabId  FROM cabopen co JOIN cmccabrecords cr ON cr.CabId=co.CabId AND cr.BookingRefNo='" . $_POST['booking_id'] . "'");
        $rows = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

        if ($rows > 0) {
            $cab = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($cab['Seats'] == '0') {
                $sql = "UPDATE cabopen SET CabStatus='I' WHERE CabId = '" . $cab['CabId'] . "'";
                $stmt = $con->prepare($sql);
                $res = $stmt->execute();
            }
        }

        $stmt = $con->prepare("INSERT INTO cancelledBookings SELECT * FROM cmccabrecords WHERE BookingRefNo='" . $_POST['booking_id'] . "'");
        $stmt->execute();
        $stmt = $con->prepare("DELETE FROM cmccabrecords WHERE BookingRefNo='" . $_POST['booking_id'] . "'");
        $stmt->execute();
    }

    echo $resp_cancellation;
    exit;
}
// End Ola Cancellation

if (isset($_REQUEST['access_token']) && $_REQUEST['access_token'] != '') {

    $access_token = $_REQUEST['access_token'];

    $requestID = $_GET['requestid'];
    $expires_in = $_GET['expires_in'];

    $sql = "UPDATE cabbookingrequest SET access_token = '$access_token', expires_in = '$expires_in', token_type = '$token_type', requestStatus = 'REQUESTDONE' where requestID = '$requestID'";
    $stmt = $con->prepare($sql);
    $stmt->execute();

    $params = array(
        'pickup_lat' => $_REQUEST['slat'],
        'pickup_lng' => $_REQUEST['slon'],
        'pickup_mode' => 'NOW',
        'category' => $_REQUEST['category']
    );

    $book_url = BOOKING_URL . '?' . http_build_query($params);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $book_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HTTPGET, true);

    curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-APP-TOKEN: ' . CLIENT_ID, 'Authorization: Bearer ' . $access_token));

    $result = curl_exec($ch);

    if ($result === false) {
        echo "Error Number:" . curl_errno($ch) . "<br>";
        echo "Error String:" . curl_error($ch);
    }
    curl_close($ch);
    echo $result;
    exit;
}