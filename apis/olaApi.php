<?php
//ini_set('display_errors', 1);
include('connection.php');
const OLA_CLIENT_ID = 'ZmNhNTk3NDctNTZjOS00MTkxLTg3NDUtZWQwYzExNzVmMjMw';
const X_APP_TOKEN = '7e22de1177fb4ac4b173a8653c72e1f3';

const BOOKING_URL = 'https://devapi.olacabs.com/v1/bookings/create';
const CANCELLATION_URL = 'https://devapi.olacabs.com/v1/bookings/cancel';
const OLA_AUTHORIZATION_ENDPOINT = 'https://devapi.olacabs.com/oauth2/authorize';
const REDIRECT_URI = 'http://104.155.193.222/cmc/cmcservice/olaApi.php';


//Ola Cancellation

if (isset($_REQUEST['type']) && $_REQUEST['type'] == 'cancellation') {

    $stmt = $con->query("SELECT cbr.access_token FROM cabbookingrequest cbr, cmccabrecords cr WHERE cbr.requestID=cr.bookingRequestID AND cr.BookingRefNo='".$_REQUEST['booking_id']."'");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $access_token = $row['access_token'];

    $params = array(
        'crn' => $_REQUEST['booking_id']
    );

    $cancel_url = CANCELLATION_URL . '?' . http_build_query($params);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $cancel_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HTTPGET, true);

    curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-APP-TOKEN: ' . X_APP_TOKEN, 'Authorization: Bearer ' . $access_token));

    $resp_cancellation = json_decode(curl_exec($ch));
    curl_close($ch);


    if ($resp_cancellation->status == 'SUCCESS' && isset($_REQUEST['booking_id']) && $_REQUEST['booking_id'] != '') {
        //Mark Solo booking archieved

        $stmt = $con->query("SELECT co.Seats, co.CabId  FROM cabopen co JOIN cmccabrecords cr ON cr.CabId=co.CabId AND cr.BookingRefNo='" . $_REQUEST['booking_id'] . "'");
        $rows = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

        if ($rows > 0) {
            $cab = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($cab['Seats'] == '0') {
                $sql = "UPDATE cabopen SET CabStatus='I' WHERE CabId = '" . $cab['CabId'] . "'";
                $stmt = $con->prepare($sql);
                $res = $stmt->execute();
            }
        }

        $stmt = $con->prepare("INSERT INTO cancelledBookings SELECT * FROM cmccabrecords WHERE BookingRefNo='" . $_REQUEST['booking_id'] . "'");
        $stmt->execute();
        $stmt = $con->prepare("DELETE FROM cmccabrecords WHERE BookingRefNo='" . $_REQUEST['booking_id'] . "'");
        $stmt->execute();
    }

    echo json_encode($resp_cancellation);
    exit;
}
// End Ola Cancellation

if (isset($_REQUEST['access_token']) && $_REQUEST['access_token'] != '') {

    $access_token = $_REQUEST['access_token'];

    $requestID = $_REQUEST['requestid'];
    $expires_in = $_REQUEST['expires_in'];

    $sql = "UPDATE cabbookingrequest SET access_token = '$access_token', expires_in = '$expires_in', requestStatus = 'REQUESTDONE' where requestID = '$requestID'";
    $stmt = $con->prepare($sql);
    $stmt->execute();

    $stmt = $con->query("SELECT start_latitude, start_longitude, product_id FROM cabbookingrequest where requestID='$requestID'");
    $rows = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($rows['product_id']=='Within Delhi' || $rows['product_id']=='Delhi to NCR'){
        $rows['product_id']='mini';
    }

    $params = array(
        'pickup_lat' => $rows['start_latitude'],
        'pickup_lng' => $rows['start_longitude'],
        'pickup_mode' => 'NOW',
        'category' => $rows['product_id']
    );

    $book_url = BOOKING_URL . '?' . http_build_query($params);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $book_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HTTPGET, true);

    curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-APP-TOKEN: ' . X_APP_TOKEN, 'Authorization: Bearer ' . $access_token));

    $result = curl_exec($ch);

    if ($result === false) {
        echo "Error Number:" . curl_errno($ch) . "<br>";
        echo "Error String:" . curl_error($ch);
    }
    curl_close($ch);
    echo $result;
    exit;
} else {

    $params = array(
        'response_type' => 'token',
        'client_id' => OLA_CLIENT_ID,
        'redirect_uri' => REDIRECT_URI,
        'scope' => 'profile booking',
        'state' => 'state123'
    );

    $auth_url = OLA_AUTHORIZATION_ENDPOINT . '?' . http_build_query($params);

    header('Location: ' . $auth_url);
    exit;
}
