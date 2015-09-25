<?php
include ('connection.php');
define( 'API_ACCESS_KEY', 'AIzaSyBqd05mV8c2VTIAKhYP1mFKF7TRueU2-Z0' );

if(isset($_POST['cabId']) && $_POST['cabId'] !=''){

    $CabID = $_POST['cabId'];
    $stmt = $con->query("SELECT OwnerName, FromShortName, ToShortName from cabopen where CabId='".$CabID."'");
    $CabsExists = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

    if ($CabsExists > 0)
    {
        $row = $stmt->fetch();
        $ownerName = $row['OwnerName'];
        $FromShortAddress = $row['FromShortName'];
        $ToShortAddress = $row['ToShortName'];
        $tripNotification = $ownerName." has started trip from ".$FromShortAddress. ". To track the location open ClubMyCab";
        $stmt1 = $con->query("select a.* from registeredusers a, acceptedrequest b where a.PushNotification != 'off' and Trim(a.MobileNumber) = Trim(b.MemberNumber) and b.cabid = '$CabID'");
        $no_of_users = $con->query("SELECT FOUND_ROWS()")->fetchColumn();
        if ($no_of_users > 0)
        {
            while($row = $stmt1->fetch())
            {
                $gcm_array = array();
                $apns_array = array();
                $FriendPlatform = $row['Platform'];
                $MemberName = $row['FullName'];
                $MemberNumber = (string) $row['MobileNumber'];
                if($FriendPlatform == "A")
                {
                    $gcm_array[]= $row['DeviceToken'];
                    sendnotification($gcm_array,$tripNotification,"TripStart",$CabID);
                }
                else{
                    $apns_array[]= $row['DeviceToken'];
                    sendnotificationtoiosusers($apns_array,$tripNotification);
                }

                $NotificationType = "Trip started";
                $cronsql = "INSERT INTO cronnotifications(NotificationType,SentMemberName, SentMemberNumber, ReceiveMemberName, ReceiveMemberNumber, Message, CabId, DateTime) VALUES ('$NotificationType','System','','$MemberName','$MemberNumber','$tripNotification','$CabID',now())";
                $cronstmt = $con->prepare($cronsql);
                $cronres = $cronstmt->execute();

            }
        }
        $sql12 = "UPDATE cabopen set uptripnotification = '1', status=1 where CabId = '".$CabID."'";
        $stmt12 = $con->prepare($sql12);
        $res12 = $stmt12->execute();
    }
}

function sendnotificationtoiosusers($ids,$msg)
{
    set_time_limit(0);
    header('content-type: text/html; charset: utf-8');
    $passphrase = '91089108';
    $message = tr_to_utf($msg);
    $deviceIds = $ids;
    $payload = '{"aps":{"alert":"' . $message . '","sound":"default"}}';
    $result = 'Start' . '<br />';
    $ctx = stream_context_create();
    stream_context_set_option($ctx, 'ssl', 'local_cert', 'yepme.pem');
    stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);

    foreach ($deviceIds as $item) {
        sleep(1);
        $fp = stream_socket_client('ssl://gateway.push.apple.com:2195', $err, $errstr, 60, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT, $ctx);

        if (!$fp) {
            exit("Failed to connect: $err $errstr" . '<br />');
        } else {
            //  echo 'Apple <nobr><a id="PXLINK_2_0_1" class="pxInta" href="#">service</a></nobr> is <nobr><a id="PXLINK_1_0_0" class="pxInta" href="#">online</a></nobr>. ' . '<br />';
        }

        $msg = chr(0) . pack('n', 32) . pack('H*', $item) . pack('n', strlen($payload)) . $payload;
        $result = fwrite($fp, $msg, strlen($msg));

        if (!$result) {
            // echo 'Undelivered message count: ' . $item . '<br />';
        } else {
            //  echo 'Delivered message count: ' . $item . '<br />';
        }

        if ($fp) {
            fclose($fp);
        }
    }
    set_time_limit(30);
}

// function for fixing Turkish characters
function tr_to_utf($text) {
    $text = trim($text);
    $search = array('Ü', 'Þ', 'Ð', 'Ç', 'Ý', 'Ö', 'ü', 'þ', 'ð', 'ç', 'ý', 'ö');
    $replace = array('Ãœ', 'Åž', '&#286;ž', 'Ã‡', 'Ä°', 'Ã–', 'Ã¼', 'ÅŸ', 'ÄŸ', 'Ã§', 'Ä±', 'Ã¶');
    $new_text = str_replace($search, $replace, $text);
    return $new_text;
}


function sendnotification($ids,$Msg,$pushfromtext,$cid)
{
    $body = array(
        'gcmText' => $Msg,
        'pushfrom' => $pushfromtext,
        'CabId' => $cid
    );

    // Set POST variables
    $url = 'https://android.googleapis.com/gcm/send';

    $fields = array(
        'registration_ids' => $ids,
        'data' => $body
    );

    $headers = array(
        'Authorization: key=' . API_ACCESS_KEY,
        'Content-Type: application/json'
    );

    $ch = curl_init();

    // Set the url, number of POST vars, POST data
    curl_setopt($ch, CURLOPT_URL, $url);

    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Disabling SSL Certificate support temporarly
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

    // Execute post
    $result = curl_exec($ch);
    if ($result === FALSE) {
        die('Curl failed: ' . curl_error($ch));
    }

    // Close connection
    curl_close($ch);
    echo $result;
}