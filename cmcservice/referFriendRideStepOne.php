<?php

include ('connection.php');

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

function sendnotificationtoandroidusers($regids,$msg)
{
	$body = array(
		'gcmText' => $msg,
		'pushfrom' => 'CabId_Refered'
    );
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
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
	$result = curl_exec($ch);
	if ($result === FALSE) {
		die('Curl failed: ' . curl_error($ch));
	}
	curl_close($ch);
}

	//Fetching Values from URL
	$CabId = $_POST['CabId'];
	$MemberName = $_POST['MemberName'];
	$MemberNumber = $_POST['MemberNumber'];
	$ReferedUserName = $_POST['ReferedUserName'];
	$ReferedUserNumber = $_POST['ReferedUserNumber'];
	
	$Message='';
	$OwnerNumber = '';
	$OwnerName = '';
	$OwnerDeviceToken='';
	$Platform='';
	$RefId = 0;
	
	$IsSuccess = false;
	$ReferedUserNumberNew = substr($ReferedUserNumber, 1, -1);
	$ReferedUserNameNew= substr($ReferedUserName, 1, -1);
	
	$sqlI = "SELECT `MobileNumber` FROM `cabopen` WHERE `CabId` = '$CabId' and `CabStatus` = 'A'";
	$stmtI = $con->query($sqlI);
	$OwnerNumber = $stmtI->fetchColumn();
	
	$sqlF = "SELECT `fromshortname` FROM `cabopen` WHERE `CabId` = '$CabId' and `CabStatus` = 'A'";
	$stmtF = $con->query($sqlF);
	$FromShortAddress = $stmtF->fetchColumn();
	
	$sqlT = "SELECT `toshortname` FROM `cabopen` WHERE `CabId` = '$CabId' and `CabStatus` = 'A'";
	$stmtT = $con->query($sqlT);
	$ToShortAddress = $stmtT->fetchColumn();
	
		
	$stmt = $con->query("SELECT * FROM `registeredusers` WHERE `MobileNumber` = '$OwnerNumber' and `PushNotification` != 'off'");
	//$no_of_users = $stmt->rowCount();
	$no_of_users = $con->query("SELECT FOUND_ROWS()")->fetchColumn();
	if ($no_of_users > 0) 
	{
		while($row = $stmt->fetch()){
			$OwnerDeviceToken = $row['DeviceToken'];
			$Platform = $row['Platform'];
			$OwnerName = $row['FullName'];			
		}

		if($OwnerDeviceToken != '')
		{
			$gcm_array = array();
			$apns_array = array();	
			if($Platform == "A")
			{
				$gcm_array[]= $OwnerDeviceToken;
			}
			else{
				$apns_array[]= $OwnerDeviceToken;
			}
			
			$refNumber = explode(",", $ReferedUserNumberNew);
			$refName = explode(",", $ReferedUserNameNew);										
			$length = count($refNumber);
			for ($i = 0; $i < $length; $i++) 
			{  
				//$Message = $MemberName . " refers " . $refName[$i] . " to your ride.";
				$Message = $MemberName . " has reffered " . $refName[$i] . " to join your ride from " . $FromShortAddress . " to " . $ToShortAddress;
				
				
				$NotificationType = "CabId_Refered";
				
				$sqlRef = "INSERT INTO `referfriendtoride` (`CabId`,`MemberNumber`,`FriendNumber`,`FriendName`,`RefDateTime`) VALUES ('$CabId','$MemberNumber','$refNumber[$i]','$refName[$i]',now())";
				$stmtRef = $con->prepare($sqlRef);
				$resRef = $stmtRef->execute();
				$RefId = $con->lastInsertId();
				if ($resRef === true) 
				{						
					$man = "INSERT INTO `notifications`(`NotificationType`, `SentMemberName`, `SentMemberNumber`, `ReceiveMemberName`, `ReceiveMemberNumber`, `Message`, `CabId`, `DateTime`, `RefId`) VALUES ('$NotificationType','$MemberName','$MemberNumber','$OwnerName','$OwnerNumber','$Message','$CabId',now(),'$RefId')";
					$manstmt = $con->prepare($man);
					$manres = $manstmt->execute();
					if ($manres === true) 
					{							
						$IsSuccess = true;
					}
					else
					{
						$IsSuccess = false;
					}
				}
				else
				{
					$IsSuccess = false;
				}
			}	
			if($IsSuccess)
			{	
				//$Message = $MemberName . " has referred his friends to your ride.";
				$Message = $MemberName . " has referred friend(s) to join your ride from " . $FromShortAddress ." to " . $ToShortAddress;
				if(count($gcm_array) > 0)
				{
					sendnotificationtoandroidusers($gcm_array,$Message);
				}
				if(count($apns_array) > 0)
				{
					sendnotificationtoiosusers($apns_array,$Message);
				}
				echo "SUCCESS";				
			}
			else
			{
				echo "FAILURE";
			}
		}	
		else
		{
			echo "FAILURE";
		}		
	}
	else
	{
		echo "FAILURE";
	}
	
?>