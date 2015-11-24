<?php
require_once('classes/class.tfs.php');

$_POST = unserialize(serialize($_POST));

if (isset($_POST['type']) && $_POST['type'] =='booking') {
    if (isset($_POST['username']) && $_POST['username'] != '' && isset($_POST['password']) && $_POST['password'] !=
        '') {
        $TFS = new TFS();
        $resp_login = $TFS->login($_POST['username'], $_POST['password']);
        $resp_login = json_decode($resp_login);

        $resp_fare = [];
        $fareDetails = [];
        if (strtolower(trim($_POST['city'])) =='new delhi'){
            $_POST['city'] = 'Delhi';
        }

        if ($resp_login->status == 'success'){
            $params = array(
                'source' => 'app',
                'pickup_time' => $_POST['pickup_time'],
                'pickup_date' => $_POST['pickup_date'],
                'pickup_latitude' => $_POST['pickup_latitude'],
                'pickup_longitude' => $_POST['pickup_longitude'],
                'city' => $_POST['city']
            );
            $resp_fare = $TFS->getFareChart($params);
            $resp_fare = json_decode($resp_fare);

            if ($resp_fare->status == 'success'){
                foreach ($resp_fare->response_data->perKmfares as $val){
                    if ($val->car_type == $_POST['car_type']){
                        $fareDetails = $val;
                    }
                }
                if(!empty($fareDetails)){
                    $params = array(
                        'city' => $_POST['city'],
                        'direction' => '',
                        'customer_name' => $resp_login->response_data->customer_name,
                        'customer_number' => $resp_login->response_data->customer_number,
                        'customer_email' => $resp_login->response_data->customer_email,
                        'pickup_area' => $_POST['pickup_area'],
                        'pickup_address' => '',
                        'landmark' => $_POST['landmark'],
                        'pickup_latitude' => $_POST['pickup_latitude'],
                        'pickup_longitude' => $_POST['pickup_longitude'],
                        'eta_from_app' => $_POST['eta_from_app'],
                        'car_type' => $fareDetails->car_type,
                        'extra_km_fare' => $fareDetails->extra_km_fare,
                        'base_km' => $fareDetails->base_km,
                        'base_fare' => $fareDetails->base_fare,
                        'surcharge' => $fareDetails->surcharge,
                        'user_id' => $resp_login->response_data->user_id,
                        'user_ref_id' => $resp_login->response_data->user_id,
                        'device_id' => '357091055850'
                    );

                    $resp_booking = $resp_booking = $TFS->makeBooking($params);
                    $resp_booking = json_decode($resp_booking);

                    if ($resp_booking->status == 'success') {
                        $booking_id = $resp_booking->response_data->booking_id;

                        $attempt = 1;
                        $driverAvailable='';
                        while($attempt < 6)
                        {
                            $resp_status = $TFS->checkBookingStatus(array('booking_id' => $booking_id));
                            $resp_status = json_decode($resp_status);
                            $driverAvailable = $resp_status->response_data->driver_name;
                            if ($resp_status->status == 'success' && $driverAvailable !='') {
                                break;
                            }
                            $attempt++;
                            sleep(5);
                        }

                        echo json_encode($resp_status);
                        exit;
                    }
                }
            }
        } else {
            echo '{"status":"fail", "msg": "Invalid Params", "error_desc":"Please enter valid 10 digit mobile number / password" }';exit;
        }

    } else {
        echo '{"msg": "Invalid Params"}';exit;
    }
} elseif (isset($_POST['type']) && $_POST['type'] =='cancellation') {
    $TFS = new TFS();

    $params = array(
        'user_id' => '',
        'booking_id' => $_POST['booking_id'],
        'cancellation_reason' => $_POST['cancellation_reason']
    );
    $resp_cancellation = $TFS->cancelBooking($params);

    if ($resp_cancellation->status == 'success' && isset($_POST['booking_id']) && $_POST['booking_id'] !='') {
    //Mark Solo booking archieved
        $stmt = $con->query("SELECT co.Seats, co.CabId  FROM cabopen co JOIN cmccabrecords cr ON cr.CabId=co.CabId AND cr.BookingRefNo='".$_POST['booking_id']."'");
        $rows = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

        if ($rows > 0) {
            $cab = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($cab['Seats']=='0') {
                $sql = "UPDATE cabopen SET CabStatus='I' WHERE CabId = '".$cab['CabId']."'";
                $stmt = $con->prepare($sql);
                $res = $stmt->execute();
            }
        }

        $stmt = $con->prepare("INSERT INTO cancelledBookings SELECT * FROM cmccabrecords WHERE BookingRefNo='".$_POST['booking_id']."'");
        $stmt->execute();
        $stmt = $con->prepare("DELETE FROM cmccabrecords WHERE BookingRefNo='".$_POST['booking_id']."'");
        $stmt->execute();
    }

    echo $resp_cancellation;
    exit;
} else {
    echo '{"msg": "Unauthorised Access"}';exit;
}