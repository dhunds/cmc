<?php
include('connection.php');
include('functions.php');

if (isset($_POST['submit']) && isset($_POST['message']) && $_POST['message'] != '') {

	$pushFrom = $_POST['pushfrom'];

    $sql = "SELECT * FROM registeredusers WHERE PushNotification='on'";

    if (isset($_POST['mobileNumber']) && $_POST['mobileNumber'] != '') {
        $sql .= " AND MobileNumber='" . $_POST['mobileNumber'] . "'";
    }

    $stmt = $con->query($sql);
    $no_of_users = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

    if ($no_of_users > 0) {
        $i = 1;
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if ($i == 1000) {
                $gcm_array[] = $row['DeviceToken'];
                $resp = sendAndroidNotification($gcm_array, $_POST['message'], $pushFrom, '');
                $gcm_array = array();
                $i = 0;
            } else {
                $gcm_array[] = $row['DeviceToken'];
            }
            $i++;
        }
        if (count($gcm_array) > 0) {
            $resp = sendAndroidNotification($gcm_array, $_POST['message'], $pushFrom, '');
        }
        echo '<pre>';
        print_r($resp);
        echo 'Notification Sent';
    }
}
?>

<h3 class="headingText">Send Notification</h3>
<div class="articleBorder">
    <form action="sendNotification.php" method="POST" onsubmit="return validate()">
        <table width="50%">
            <tr>
                <td width="150px" class="bluetext">Mobile Number</td>
                <td><input type="text" name="mobileNumber" id="mobileNumber"/> (Optional)<br/><br/></td>
            </tr>
	        <tr>
		        <td width="150px" class="bluetext">Push From</td>
		        <td><select name="pushfrom">
				        <option value="genericnotification">General Notification</option>
				        <option value="genericnotificationclub">General Notification Club</option>
				        <option value="genericnotificationrides">General Notification Rides</option>
				        <option value="genericnotificationwallet">General Notification Wallet</option>
				        <option value="genericnotificationsharelocation">General Notification Location</option>
				        <option value="genericnotificationprofile">General Notification Profile</option>
				        <option value="genericnotificationsettings">General Notification Settings</option>
				        <option value="genericnotificationoffers">General Notification Offers</option>
			        </select>
			        <br/><br/></td>
	        </tr>
            <tr>
                <td width="150px" class="bluetext">Notification</td>
                <td><textarea name="message" cols="50" rows="5"></textarea></td>
            </tr>
            <tr>
                <td colspan="2"><br/><input type="submit" class="cBtn" name="submit" value="Send Notification"></td>
            </tr>
        </table>
    </form>
</div>
<script>
    function validate() {
        if ($("#mobileNumber").val() == '') {
            var conf = confirm('Are you sure you want to sent this notification to everyone?');
            if (conf == true) {
                return true;
            } else {
                return false;
            }
        }
        return true;
    }
</script>