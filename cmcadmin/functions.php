<?php
define('API_ACCESS_KEY', 'AIzaSyBqd05mV8c2VTIAKhYP1mFKF7TRueU2-Z0' );

function sendAndroidNotification($deviceId, $Msg, $pushFromText, $CabId)
{
    $body = array(
        'gcmText' => $Msg,
        'pushfrom' => $pushFromText
    );

    if ($CabId !='')
        $body['CabId'] = $CabId;

    $url = 'https://android.googleapis.com/gcm/send';
    $fields = array(
        'registration_ids' => $deviceId,
        'data' => $body
    );
    $headers = array(
        'Authorization: key=' . API_ACCESS_KEY,
        'Content-Type: application/json'
    );
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
    $result = curl_exec($ch);
    curl_close($ch);

    if ($result === FALSE) {
        return curl_error($ch);
    }else{
        return $result;
    }
}

function sendSMS($nos, $message)
{
    $ch1 = curl_init();
    $fields_string = '';
    $fieldsNew = array(
        'Message' => $message,
        'Numbers' => $nos
    );
    foreach ($fieldsNew as $key => $value) {
        $fields_string .= $key . '=' . urlencode($value) . '&';
    }
    rtrim($fields_string, '&');
    curl_setopt($ch1, CURLOPT_URL, "http://127.0.0.1/cmc/cmcservice/sendsms.php");
    curl_setopt($ch1, CURLOPT_POST, true);
    curl_setopt($ch1, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch1, CURLOPT_POSTFIELDS, $fields_string);
    curl_exec($ch1);
}

function pagination_search($totalpage, $page, $url = '?')
{
    $adjacents = "2";
    $prev = $page - 1;
    $next = $page + 1;
    $lastpage = $totalpage;
    $lpm1 = $lastpage - 1;

    $pagination = "";
    if ($lastpage > 1) {
        $pagination .= "<div class='pagination'>";

        if ($lastpage < 7 + ($adjacents * 2)) {
            for ($counter = 1; $counter <= $lastpage; $counter++) {
                if ($counter == $page)
                    $pagination .= "<span class='current'>$counter</span>";
                else
                    $pagination .= "<a href='?page=$counter'>$counter</a>";
            }
        } elseif ($lastpage > 5 + ($adjacents * 2)) {
            if ($page < 1 + ($adjacents * 2)) {
                for ($counter = 1; $counter < 4 + ($adjacents * 2); $counter++) {
                    if ($counter == $page)
                        $pagination .= "<span class='current'>$counter</span>";
                    else
                        $pagination .= "<a href='?page=$counter'>$counter</a>";
                }
                $pagination .= "<span class='current'>...</span>";
                $pagination .= "<a href='?page=$lpm1'>$lpm1</a></a>";
                $pagination .= "<a href='?page=$lastpage'>$lastpage</a></li>";
            } elseif ($lastpage - ($adjacents * 2) > $page && $page > ($adjacents * 2)) {
                $pagination .= "<a href='?page=1'>1</a></li>";
                $pagination .= "<a href='?page=2'>2</a></li>";
                $pagination .= "<span class='current'>...</span>";
                for ($counter = $page - $adjacents; $counter <= $page + $adjacents; $counter++) {
                    if ($counter == $page)
                        $pagination .= "<span class='current'>$counter</span>";
                    else
                        $pagination .= "<a href='?page=$counter'>$counter</a>";
                }
                $pagination .= "<span class='current'>...</span>";
                $pagination .= "<a href='?page=$lpm1'>$lpm1</a>";
                $pagination .= "<a href='?page=$lastpage'>$lastpage</a>";
            } else {
                $pagination .= "<a href='?page=1'>1</a></li>";
                $pagination .= "<a href='?page=2'>2</a></li>";
                $pagination .= "<span class='current'>...</span>";
                for ($counter = $lastpage - (2 + ($adjacents * 2)); $counter <= $lastpage; $counter++) {
                    if ($counter == $page)
                        $pagination .= "<span class='current'>$counter</span>";
                    else
                        $pagination .= "<a href='?page=$counter'>$counter</a>";
                }
            }
        }

        if ($page < $counter - 1) {
            $pagination .= "<a href='?page=$next' class='next'>Next &gt;</a></li>";
            $pagination .= "<a href='?page=$lastpage' class='next'>Last</a></li>";
        } else {
            $pagination .= "<span class='current'>Next</span>";
            $pagination .= "<span class='current'>Last</span>";
        }
        $pagination .= "</div>";
    }
    return $pagination;
}