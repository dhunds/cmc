<?php
include('connection.php');
include_once('../cmcservice/classes/class.notification.php');
$objNotification = new Notification();
$imageName = '';

if (isset($_POST['submit']) && isset($_POST['message']) && $_POST['message'] != '') {

    if (!empty($_FILES)) {

        $file_tmp =$_FILES['imageFile']['tmp_name'];
        $file_size =$_FILES['imageFile']['size'];
        $file_ext=strtolower(end(explode('.',$_FILES['imageFile']['name'])));
        $imageName = time().'.'.$file_ext;
        
        $expensions= array("jpeg","jpg","png");
      
        if (in_array($file_ext,$expensions)=== false) {
            echo 'Extension not allowed, please choose a JPEG or PNG file.';
            exit;
        }

        if ($file_size > 2097152){
            echo 'File size must be less than 2 MB';
            exit;
        }

        if (move_uploaded_file($file_tmp, '../images/notimages/'.$imageName)) {
            echo 'An error occured while uploading file.';
            exit;
        }
    }
    
	$pushFrom = $_POST['pushfrom'];

    if ($imageName !='') {
        $body = array('gcmText' => $_POST['message'], 'pushfrom' => $pushFrom, 'image' => IMAGE_BASEURL.'/notimages/'.$imageName);
    } else {
        $body = array('gcmText' => $_POST['message'], 'pushfrom' => $pushFrom);
    }
    

    $sql = "SELECT * FROM registeredusers WHERE PushNotification='on' AND Platform='A'";

    if (isset($_POST['mobileNumber']) && $_POST['mobileNumber'] != '') {
        $_POST['mobileNumber'] = '0091' . substr(trim($_POST['mobileNumber']), -10);
        $sql .= " AND MobileNumber='" . $_POST['mobileNumber'] . "'";
    }

    $stmt = $con->query($sql);
    $no_of_users = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

    if ($no_of_users > 0) {
        $i = 1;
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if ($i == 1000) {
                $gcm_array[] = $row['DeviceToken'];

                $objNotification->setVariables($gcm_array, $body);
                $res = $objNotification->sendGCMNotification();
                
                $gcm_array = array();
                $i = 0;
            } else {
                $gcm_array[] = $row['DeviceToken'];
            }
            $i++;
        }
        if (count($gcm_array) > 0) {
            $objNotification->setVariables($gcm_array, $body);
            $res = $objNotification->sendGCMNotification();
        }
        //echo '<pre>';
        //print_r($resp);
        //echo 'Notification Sent';
    }

    // Send IOS Notification
    $sql = "SELECT * FROM registeredusers WHERE PushNotification='on' AND Platform='I'";

    if (isset($_POST['mobileNumber']) && $_POST['mobileNumber'] != '') {
        $sql .= " AND MobileNumber='" . $_POST['mobileNumber'] . "'";
    }

    $stmt = $con->query($sql);
    $no_of_users = $con->query("SELECT FOUND_ROWS()")->fetchColumn();
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $apns_array = array();
        $apns_array[] = $row['DeviceToken'];
        $objNotification->setVariables($apns_array, $body);
        $objNotification->sendIOSNotification();

    }
    echo 'Notification Sent';
}
?>

<h2 class="headingText">Send Notification</h2>
<div>
    <form action="sendNotification.php" method="POST" onsubmit="return validate()" enctype="multipart/form-data">
        <table width="50%" style="margin-left: 5px;">
            <tr>
                <td width="150px" class="bluetext">Mobile Number</td>
                <td><input type="text" name="mobileNumber" id="mobileNumber"/> (Optional)<br/><br/></td>
            </tr>
            <tr style="display:none;" id="imageUpload">
                <td width="150px" class="bluetext">Upload Image</td>
                <td><input type="file" name="imageFile" id="imageFile"/> (Optional)<br/><br/></td>
            </tr>
	        <tr>
		        <td width="150px" class="bluetext">Push From</td>
		        <td><select name="pushfrom" onchange="showHideFileUpload(this.value)">
				        <option value="genericnotification">General Notification</option>
				        <option value="genericnotificationclub">General Notification Club</option>
				        <option value="genericnotificationrides">General Notification Rides</option>
				        <option value="genericnotificationwallet">General Notification Wallet</option>
				        <option value="genericnotificationsharelocation">General Notification Location</option>
				        <option value="genericnotificationprofile">General Notification Profile</option>
				        <option value="genericnotificationsettings">General Notification Settings</option>
				        <option value="genericnotificationoffers">General Notification Offers</option>
                        <option value="imagenotification">Image Notification</option>
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

    function showHideFileUpload(pushfrom){
        if (pushfrom == 'imagenotification') {
            $( "#imageUpload" ).show();
        } else {
            $( "#imageUpload" ).hide();
        }
    }
</script>