<?php
include('connection.php');

if (isset($_POST['bookingRefNo']) && $_POST['bookingRefNo'] != '') {
    $requestId = $_POST['bookingRefNo'];
    $cancelUrl = 'https://api.uber.com/v1/requests/' . $requestId;

    $stmt = $con->query("SELECT cb.access_token FROM cabbookingrequest cb JOIN cmccabrecords cr ON cr
.bookingRequestID=cb.requestID WHERE cr.BookingRefNo='" . $requestId . "'");
    $tokenExists = $con->query("SELECT FOUND_ROWS()")->fetchColumn();
    if ($tokenExists > 0) {
        $row = $stmt->fetch();
        $accessToken = $row['access_token'];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $cancelUrl);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: Bearer ' . $accessToken));
        $result = curl_exec($ch);
        curl_close($ch);

        if ($result === false) {
            echo "Error Number:" . curl_errno($ch) . "<br>";
            echo "Error String:" . curl_error($ch);
            http_response_code(500);
            header('Content-Type: application/json');
            echo '{status:"failed", message:"Error Number:' . curl_errno($ch) . '- ' . curl_error($ch) . '."}';
            exit;
        } else {
            if ($requestId != '') {
                //Mark Solo booking archieved
                $stmt = $con->query("SELECT co.Seats, co.CabId  FROM cabopen co JOIN cmccabrecords cr ON cr.CabId=co.CabId AND cr.BookingRefNo='" . $requestId . "'");
                $rows = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

                if ($rows > 0) {
                    $cab = $stmt->fetch(PDO::FETCH_ASSOC);
                    if ($cab['Seats'] == '0') {
                        $sql = "UPDATE cabopen SET CabStatus='I' WHERE CabId = '" . $cab['CabId'] . "'";
                        $stmt = $con->prepare($sql);
                        $res = $stmt->execute();
                    }
                }

                $stmt = $con->prepare("INSERT INTO cancelledBookings SELECT * FROM cmccabrecords WHERE BookingRefNo='" . $requestId . "'");
                $stmt->execute();
                $stmt = $con->prepare("DELETE FROM cmccabrecords WHERE BookingRefNo='" . $requestId . "'");
                $stmt->execute();
            }

            http_response_code(200);
            header('Content-Type: application/json');
            echo '{status:"success", message:"Updated sucessfully"}';
            exit;
        }
    } else {
        header('Content-Type: application/json');
        echo '{status:"failed", message:"No valid token found to make this request."}';
        exit;
    }
} else {
    header('Content-Type: application/json');
    echo '{status:"failed", message:"Invalid Request."}';
    exit;
}
