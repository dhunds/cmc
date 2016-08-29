<?php
 
// set time limit to zero in order to avoid timeout
set_time_limit(0);
 
// charset header for output
header('content-type: text/html; charset: utf-8');
 
// this is the pass phrase you defined when creating the key
$passphrase = '91089108';
 
// you can post a variable to this string or edit the message here
if (!isset($_POST['msg'])) {
$_POST['msg'] = "Notification message here!";
}
 
// tr_to_utf function <nobr><a id="PXLINK_6_0_5" class="pxInta" href="#">needed</a></nobr> to fix the Turkish characters
$message = tr_to_utf($_POST['msg']);
 
// load your device ids to an array
/* $deviceIds = array(
'lh142lk3h1o2141p2y412d3yp1234y1p4y1d3j4u12p43131p4y1d3j4u12p4313',
'y1p4y1d3j4u12p43131p4y1d3j4u12p4313lh142lk3h1o2141p2y412d3yp1234'
); */

$deviceIds = array(
'6351d413decededc8056b889beadfa2f173a1f8347e9ede0700efa2ff0a80db8'
);
 
// this is where you can customize your notification
$payload = '{"aps":{"alert":"' . $message . '","sound":"default"}}';
 
$result = 'Start' . '<br />';
 
////////////////////////////////////////////////////////////////////////////////
// start to create connection
$ctx = stream_context_create();
stream_context_set_option($ctx, 'ssl', 'local_cert', 'yepme.pem');
stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);
  
foreach ($deviceIds as $item) {
    // wait for some time
    sleep(1);
     
    // Open a connection to the APNS server
    $fp = stream_socket_client('ssl://gateway.push.apple.com:2195', $err, $errstr, 60, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT, $ctx);
 
    if (!$fp) {
        exit("Failed to connect: $err $errstr" . '<br />');
    } else {
      //  echo 'Apple <nobr><a id="PXLINK_2_0_1" class="pxInta" href="#">service</a></nobr> is <nobr><a id="PXLINK_1_0_0" class="pxInta" href="#">online</a></nobr>. ' . '<br />';
    }
 
    // Build the binary notification
    $msg = chr(0) . pack('n', 32) . pack('H*', $item) . pack('n', strlen($payload)) . $payload;
     
    // Send it to the server
    $result = fwrite($fp, $msg, strlen($msg));
     
    if (!$result) {
       // echo 'Undelivered message count: ' . $item . '<br />';
    } else {
      //  echo 'Delivered message count: ' . $item . '<br />';
	  echo 'Delivered message';
    }
 
    if ($fp) {
        fclose($fp);
       // echo 'The connection has been closed by the client' . '<br />';
    }
}
 
//echo count($deviceIds) . ' devices have received notifications.<br />';
 
// function for fixing Turkish characters
function tr_to_utf($text) {
    $text = trim($text);
    $search = array('Ü', 'Þ', 'Ð', 'Ç', 'Ý', 'Ö', 'ü', 'þ', 'ð', 'ç', 'ý', 'ö');
    $replace = array('Ãœ', 'Åž', '&#286;ž', 'Ã‡', 'Ä°', 'Ã–', 'Ã¼', 'ÅŸ', 'ÄŸ', 'Ã§', 'Ä±', 'Ã¶');
    $new_text = str_replace($search, $replace, $text);
    return $new_text;
}
 
// set time limit back to a normal value
set_time_limit(30);
?>