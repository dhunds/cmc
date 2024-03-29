<?php


include ('connection.php');

// API access key from Google API's Console
define( 'API_ACCESS_KEY', 'AIzaSyBqd05mV8c2VTIAKhYP1mFKF7TRueU2-Z0' );



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
	$myFile= "upcoming" . date('YmdHis') . ".xml";
	file_put_contents($myFile, $Msg, FILE_APPEND | LOCK_EX);
	file_put_contents($myFile, $pushfromtext, FILE_APPEND | LOCK_EX);
	file_put_contents($myFile, $cid, FILE_APPEND | LOCK_EX);
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
	
	$stmt1 = $con->query("select a.*,c.`cabid`,c.`MobileNumber` as `OwnerNumber`,c.`fromshortname`,c.`toshortname` from `registeredusers` a, `acceptedrequest` b, `cabopen` c where a.`PushNotification` != 'off' and a.`MobileNumber` = b.`MemberNumber` and b.`cabid` = c.`cabid`AND b.Status !='Dropped' and c.`cabid` IN (SELECT `cabid` from `cabopen` where TIMESTAMPDIFF(MINUTE, NOW(), `ExpStartDateTime`) < 10  AND `uptripnotification` = 0 and `CabStatus` = 'A')");
	//$no_of_users = $stmt1->rowCount();
	$no_of_users = $con->query("SELECT FOUND_ROWS()")->fetchColumn();
	if ($no_of_users > 0) 
	{		
		while($row = $stmt1->fetch())
		{
			$gcm_array = array();
			$apns_array = array();		
			$CabID = $row['cabid'];		
			$FriendPlatform = $row['Platform'];	
			$MemberName = $row['FullName'];	
			$MemberNumber = (string) $row['MobileNumber'];	
			$FromShortAddress = $row['fromshortname'];
			$ToShortAddress = $row['toshortname'];
			$OwnerNumber = (string) $row['OwnerNumber'];
			$UpcomingTripNotification = "You have an upcoming trip from " . $FromShortAddress . " to " . $ToShortAddress . ". Click here to view details.";			
			
			if($FriendPlatform == "A")
			{
				$gcm_array[]= $row['DeviceToken'];
				sendnotification($gcm_array,$UpcomingTripNotification,"upcomingtrip",$CabID);	
			}
			else{
				$apns_array[]= $row['DeviceToken'];
				sendnotificationtoiosusers($apns_array,$UpcomingTripNotification);
			}
			
			$NotificationType = "Upcoming Trip";
			$cronsql = "INSERT INTO `cronnotifications`(`NotificationType`,`SentMemberName`, `SentMemberNumber`, `ReceiveMemberName`, `ReceiveMemberNumber`, `Message`, `CabId`, `DateTime`) VALUES ('$NotificationType','System','','$MemberName','$MemberNumber','$UpcomingTripNotification','$CabID',now())";
			$cronstmt = $con->prepare($cronsql);
			$cronres = $cronstmt->execute();
			
			$sql12 = "UPDATE `cabopen` set `uptripnotification` = '1' where `cabid` = '$CabID'";
			$stmt12 = $con->prepare($sql12);
			$res12 = $stmt12->execute();			
		}		

		$stmt = $con->query("SELECT * FROM `registeredusers` WHERE Trim(`MobileNumber`) = Trim('$OwnerNumber') and `PushNotification` != 'off'");
		//$no_of_users = $stmt->rowCount();
		$OwnerExists = $con->query("SELECT FOUND_ROWS()")->fetchColumn();
		if ($OwnerExists > 0) 
		{
			$gcm_array = array();
			$apns_array = array();
			while($row = $stmt->fetch()){
				$OwnerDeviceToken = $row['DeviceToken'];
				$Platform = $row['Platform'];
				$OwnerName = $row['FullName'];			
			}
			if($Platform == "A")
			{
				$gcm_array[]= $OwnerDeviceToken;
				sendnotification($gcm_array,$UpcomingTripNotification,"upcomingtrip",$CabID);
			}
			else{
				$apns_array[]= $OwnerDeviceToken;
				sendnotificationtoiosusers($apns_array,$UpcomingTripNotification);
			}	
			
			$NotificationType = "Upcoming Trip";
			$cronsql = "INSERT INTO `cronnotifications`(`NotificationType`,`SentMemberName`, `SentMemberNumber`, `ReceiveMemberName`, `ReceiveMemberNumber`, `Message`, `CabId`, `DateTime`) VALUES ('$NotificationType','System','','$OwnerName','$OwnerNumber','$UpcomingTripNotification','$CabID',now())";
			$cronstmt = $con->prepare($cronsql);
			$cronres = $cronstmt->execute();
		}
					
	}
	else
	{
		echo "no one in database";
	}

?>