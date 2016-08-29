<?php

include ('connection.php');

function sendSMS($nos, $message)
{
$ch1 = curl_init();
$fields_string = '';
$fieldsNew = array(
 'Message' => $message,
 'Numbers' => $nos
 );
foreach($fieldsNew as $key=>$value) { $fields_string .= $key.'='. urlencode($value) .'&'; }
rtrim($fields_string, '&');	
curl_setopt($ch1, CURLOPT_URL, "http://127.0.0.1/cmc/cmcservice/sendsms.php");
curl_setopt($ch1, CURLOPT_POST, true);
curl_setopt($ch1, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch1, CURLOPT_POSTFIELDS, $fields_string);
$resultNew = curl_exec($ch1);
}

function sendnotification($ids,$Msg,$pushfromtext) 
{
	$body = array(
            'gcmText' => $Msg,
			'pushfrom' => $pushfromtext
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

// API access key from Google API's Console
define( 'API_ACCESS_KEY', 'AIzaSyBqd05mV8c2VTIAKhYP1mFKF7TRueU2-Z0' );

//Fetching Values from URL
$CabId = $_POST['CabId'];
$MembersNumber = $_POST['MembersNumber'];
$MembersName = $_POST['MembersName'];
$OwnerName = $_POST['OwnerName'];
$OwnerNumber = $_POST['OwnerNumber'];
$UserName = $_POST['UserName'];
$UserNumber = $_POST['UserNumber'];

			$Message = $UserName . " has referred you to share a cab";
			
			$MessagetoOwnwer = $UserName . " has referred a friend to your ride";

			$MembersNumbernew = substr($MembersNumber, 1, -1);
			$MembersNamenew = substr($MembersName, 1, -1);
			
			$myArraynumber = explode(',', $MembersNumbernew);
			$myArrayname = explode(',', $MembersNamenew);
			$strNo = '';
			$strNoforAlreadyExistUser = '';
			$str = "INSERT INTO `cabmembers`(CabId, MemberName, MemberNumber) VALUES";
			for($i = 0 ;$i< count($myArraynumber); $i++) {				
				$stmt10 = $con->query("SELECT * FROM `cabmembers` WHERE `MemberNumber` = Trim($myArraynumber[$i]) and `CabId`= '$CabId'");
				//$rows10 = $stmt10->fetchAll(PDO::FETCH_ASSOC);
				//$user_already_exists = count($rows10);
				$user_already_exists = $con->query("SELECT FOUND_ROWS()")->fetchColumn();
				if($user_already_exists > 0)
				{
					if($strNoforAlreadyExistUser == '')
					{
						$strNoforAlreadyExistUser = $myArraynumber[$i];
					}
					else{
						$strNoforAlreadyExistUser .= ',' . $myArraynumber[$i];
					}	
					$sql12 = "UPDATE `cabmembers` set `DropStatus` = 'No' where `MemberNumber` = Trim($myArraynumber[$i]) and `CabId`= '$CabId'";
					$stmt12 = $con->prepare($sql12);
					$res12 = $stmt12->execute();					
				}
				else
				{
					$str .= "('" . $CabId . "','" . $myArrayname[$i] . "','" . $myArraynumber[$i] . "'),";
					
					/* $sql212 = "INSERT INTO `allnotification`(`SentMemberName`, `SentMemberNumber`, `ReceiveMemberName`, `ReceiveMemberNumber`, `Message`, `CabId`, `DateTime`, `Status`) VALUES ('$UserName','$UserNumber',Trim('$myArrayname[$i]'),Trim('$myArraynumber[$i]'),'$Message','$CabId',now(),'U')";
					$stmt212 = $con->prepare($sql212);
					$res212 = $stmt212->execute(); */
					
					$NotificationType = "CabId_Refered";
					$sql212 = "INSERT INTO `notifications`(`NotificationType`, `SentMemberName`, `SentMemberNumber`, `ReceiveMemberName`, `ReceiveMemberNumber`, `Message`, `CabId`, `DateTime`) VALUES ('$NotificationType','$UserName','$UserNumber',Trim('$myArrayname[$i]'),Trim('$myArraynumber[$i]'),'$Message','$CabId',now())";
					$stmt212 = $con->prepare($sql212);
					$res212 = $stmt212->execute();
				}								    			
				
				////////////////
				$stmt1 = $con->query("SELECT * FROM `registeredusers` WHERE `MobileNumber` = Trim($myArraynumber[$i])");
				//$rows = $stmt1->fetchAll(PDO::FETCH_ASSOC);
				//$user_exists = count($rows);
				$user_exists = $con->query("SELECT FOUND_ROWS()")->fetchColumn();
				if($OwnerNumber == Trim($myArraynumber[$i]))
				{
					$user_exists =1;
				}
				if($user_exists == 0)
				{
					if($strNo == '')
					{
						$strNo = "[" . $myArraynumber[$i];
					}
					else{
						$strNo .= ',' . $myArraynumber[$i];
					}				
				}				
			}
			if($strNo != '') 
			{ 
				$sql = "SELECT `SmsMessage` FROM `smstemplates` WHERE `SmsshortCode` = 'INVITE'";
				$stmt = $con->query($sql);
				$message = $stmt->fetchColumn();				

				$sql = "SELECT FromShortName, ToShortName FROM cabopen WHERE CabId ='".$CabId ."'";
				$stmt = $con->query($sql);
    			$row = $stmt->fetch(PDO::FETCH_ASSOC);
				
				$message = str_replace("OXXXXX", $UserName ,$message);			
				$message = str_replace("FXXXXX", $row['FromShortName'] ,$message);		
				$message = str_replace("TXXXXX", $row['ToShortName'] ,$message);	
									
				sendSMS($strNo . "]", $message);				
			}
			$str = trim($str, ",");
			
			$stmt2121 = $con->prepare($str);
			$res2222 = $stmt2121->execute();

	$stmt = $con->query("SELECT * FROM `registeredusers` WHERE `MobileNumber` IN ($MembersNumbernew) and `PushNotification` != 'off'");
	//$no_of_users = $stmt->rowCount();
	$no_of_users = $con->query("SELECT FOUND_ROWS()")->fetchColumn();
 	if ($no_of_users > 0) 
	{
		while($row = $stmt->fetch())
		{
			$gcm_array[]=$row['DeviceToken'];
		}
		
		sendnotification($gcm_array,$Message,"cabopen");
		
		$stmt9 = $con->query("SELECT * FROM `registeredusers` WHERE `MobileNumber` = '$OwnerNumber' and `PushNotification` != 'off'");
		//$no_of_users9 = $stmt9->rowCount();
		$no_of_users9 = $con->query("SELECT FOUND_ROWS()")->fetchColumn();
		if ($no_of_users9 > 0) 
		{
			while($row9 = $stmt9->fetch())
			{
				$gcm_array9[]=$row9['DeviceToken'];
			}
			
			sendnotification($gcm_array9,$MessagetoOwnwer,"njdfn");
			
			/* $sql21288 = "INSERT INTO `allnotification`(`SentMemberName`, `SentMemberNumber`, `ReceiveMemberName`, `ReceiveMemberNumber`, `Message`, `CabId`, `DateTime`, `Status`) VALUES ('$UserName','$UserNumber',Trim('$OwnerName'),Trim('$OwnerNumber'),'$MessagetoOwnwer','$CabId',now(),'U')";
			$stmt21288 = $con->prepare($sql21288);
			$res21288 = $stmt21288->execute(); */
			
			$NotificationType = "CabId_Refered";
					$sql21288 = "INSERT INTO `notifications`(`NotificationType`, `SentMemberName`, `SentMemberNumber`, `ReceiveMemberName`, `ReceiveMemberNumber`, `Message`, `CabId`, `DateTime`) VALUES ('$NotificationType','$UserName','$UserNumber',Trim('$OwnerName'),Trim('$OwnerNumber'),'$MessagetoOwnwer','$CabId',now())";
					$stmt21288 = $con->prepare($sql21288);
					$res21288 = $stmt21288->execute();
		}
		else
		{
		echo "no one in database";
		}
		
		
	}
	else
	{
	echo "no one in database";
	}

?>