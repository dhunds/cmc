<?php

include ('connection.php');

// API access key from Google API's Console
define( 'API_ACCESS_KEY', 'AIzaSyBqd05mV8c2VTIAKhYP1mFKF7TRueU2-Z0' );

function sendnotificationtoandroidusers($regids,$msg,$cid,$mname,$oname,$onumber)
{
	$body = array(
            'gcmText' => $msg,
			'pushfrom' => 'groupchat',
			'CabId' => $cid,
			'MemberName' => $mname,
			'oname' => $oname,
			'onumber' => $onumber
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

$CabId = $_POST['CabId'];
$MobileNumber = $_POST['MemberNumber'];
//$MemberNumberARR = $_POST['MemberNumberARR'];
$Message = $_POST['Message'];
$MemberName = $_POST['MemberName'];
$ownername = $_POST['ownername'];
$ownernumber = $_POST['ownernumber'];

//$MemberNumberARRnew = substr($MemberNumberARR, 1, -1);

$sql="call getChatStatusCabWise('$CabId', @totalRows, '$MobileNumber')";	
$data = $con->query($sql)->fetchAll(PDO::FETCH_ASSOC);
$total_count = $con->query("select @totalRows;")->fetch(PDO::FETCH_ASSOC);	
$no_of_users = $total_count['@totalRows'];

if ($no_of_users > 0)
{
	foreach ($data as $row) {		
        if($row['ChatStatus'] === 'online')
		{
			
		}
		else if($row['ChatStatus'] === 'offline')
		{
			$gcm_array[]=$row['DeviceToken'];
		} 
    }
	if(count($gcm_array) > 0)
	{
		sendnotificationtoandroidusers($gcm_array,$Message,$CabId,$MemberName,$ownername,$ownernumber);
	}
	//echo "this " . var_dump($gcm_array);
}
else
{
	echo "no one in database";
}
	
?>