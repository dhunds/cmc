<?php

include ('connection.php');

// API access key from Google API's Console
define( 'API_ACCESS_KEY', 'AIzaSyBqd05mV8c2VTIAKhYP1mFKF7TRueU2-Z0' );

//Fetching Values from URL
$CabId = $_POST['CabId'];
$MemberNumber = $_POST['MemberNumber'];
$Status = $_POST['Status'];

if($Status == "Accepted")
{
	$Message = "Your Request Accepted. Enjoy the ride!";
}
else
{
	$Message = "Your Request Declined. Sorry For Inconvenience";
}

$sql2 = "UPDATE `acceptedrequest` SET `Status`= '$Status' WHERE (`CabId` = '$CabId' AND `MemberNumber` = '$MemberNumber')";
$stmt2 = $con->prepare($sql2);
$res2 = $stmt2->execute();

if ($res2 === true) 
		{
			

$stmt = $con->query("SELECT * FROM `registeredusers` WHERE `MobileNumber` = '$MemberNumber'");
	//$no_of_users = $stmt->rowCount();
	$no_of_users = $con->query("SELECT FOUND_ROWS()")->fetchColumn();
 if ($no_of_users > 0) 
{
while($row = $stmt->fetch()){
        $gcm_array[]=$row['DeviceToken'];
}

$body = array(
            'gcmText' => $Message,
			'pushfrom' => 'acceptreject'
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
echo $result;

}
else
{
echo "no one in database";
}

		
		}
		else {
			echo 'Error';
		}

?>