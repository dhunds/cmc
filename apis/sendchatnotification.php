<?php
include ('connection.php');
include_once('classes/class.notification.php');
$objNotification = new Notification();

$CabId = $_POST['CabId'];
$MobileNumber = $_POST['MemberNumber'];
$Message = $_POST['Message'];
$MemberName = $_POST['MemberName'];
$ownerName = $_POST['ownername'];
$ownerNumber = $_POST['ownernumber'];

$sql = "SELECT ar.ChatStatus, ru.DeviceToken, ru.Platform, ru.FullName FROM acceptedrequest ar JOIN registeredusers ru ON ar.MemberNumber = ru.MobileNumber WHERE ar.MemberNumber !='".$MobileNumber."' AND ar.CabId='".$CabId."' AND ru.PushNotification !='off' AND ar.Status !='Dropped'
UNION
SELECT co.OwnerChatStatus as ChatStatus, ru.DeviceToken, ru.Platform, ru.FullName FROM cabopen co JOIN registeredusers ru ON co.MobileNumber = ru.MobileNumber WHERE co.MobileNumber !	='".$MobileNumber."' AND co.CabId='".$CabId."' AND ru.PushNotification !='off'
";

$stmt = $con->query($sql);
$memberCount = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

if ($memberCount > 0)
{
	$members = $stmt->fetchAll(PDO::FETCH_ASSOC);

	foreach ($members as $member) {

		$gcm_array = [];
		$apns_array = [];

		if($member['ChatStatus'] === 'offline')
		{
			$body = array('gcmText' => $Message, 'pushfrom' => 'groupchat', 'CabId' => $CabId, 'MemberName' => $MemberName, 'oname' => $ownerName, 'onumber' => $ownerNumber );

			if ($member['Platform'] == "A") {
				$gcm_array[] = $member['DeviceToken'];
				$objNotification->setVariables($gcm_array, $body);
				$res = $objNotification->sendGCMNotification();
			} else {
				$apns_array[] = $member['DeviceToken'];
				$objNotification->setVariables($apns_array, $body);
				$res = $objNotification->sendIOSNotification();
			}

		} 
    }
}
else
{
	echo "no one in database";
}
