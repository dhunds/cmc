<?php

include ('connection.php');

// API access key from Google API's Console
define( 'API_ACCESS_KEY', 'AIzaSyBqd05mV8c2VTIAKhYP1mFKF7TRueU2-Z0' );

function sendnotificationtoandroidusers($regids,$msg)
{
	$body = array(
            'gcmText' => $msg,
			'pushfrom' => 'cabopen',
            );

    // Set POST variables
  $url = 'https://android.googleapis.com/gcm/send';

  $fields = array(
 'registration_ids' => $regids,
 'data' => $body,
 );


 $headers = array(
 'Authorization: key=' . API_ACCESS_KEY,
  'Content-Type: application/json'
 );


//print_r($headers);
// Open connection
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

$MobileNumber = $_POST['MobileNumber'];
$Message = $_POST['Message'];

$stmt = $con->query("SELECT * FROM `registeredusers` WHERE `MobileNumber` = '$MobileNumber' and `PushNotification` != 'off'");
$no_of_users = $stmt->rowCount();

 if ($no_of_users > 0) 
{
while($row = $stmt->fetch()){
	
			$gcm_array[]=$row['DeviceToken'];
        
}
	
	if(count($gcm_array) > 0)
	{
		sendnotificationtoandroidusers($gcm_array,$Message);
	}
}
else
{
	echo "no one in database";
}
	
?>