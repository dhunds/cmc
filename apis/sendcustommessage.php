<?php

include ('connection.php');

// API access key from Google API's Console
define( 'API_ACCESS_KEY', 'AIzaSyBqd05mV8c2VTIAKhYP1mFKF7TRueU2-Z0' );

$MemberNumber = $_POST['MemberNumber'];
$Message = $_POST['Message'];

$stmt = $con->query("SELECT * FROM `registeredusers` WHERE `PushNotification` != 'off' and `MobileNumber` = '$MemberNumber'");
	//$no_of_users = $stmt->rowCount();
$no_of_users = $con->query("SELECT FOUND_ROWS()")->fetchColumn();
 if ($no_of_users > 0) 
{
while($row = $stmt->fetch()){
        $gcm_array[]=$row['DeviceToken'];
}

$body = array(
            'gcmText' => $Message,
			'pushfrom' => 'dropuser',
			'CabId' => $CabId
            );

    // Set POST variables
  $url = 'https://android.googleapis.com/gcm/send';

  $fields = array(
 'registration_ids' => $gcm_array,
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
echo "pool successfully drop";

}
else
{
echo "Error";
}

 ?>