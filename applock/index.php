<?php
	include('connection.php');
	$stmt = $con->query("SELECT *  FROM deviceDetails");
    $devices = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>


<html lang="en">
<head>
    <title>Devices</title>
    <script src="js/jquery.min.js" type="text/javascript"></script>
<body>
	<h2>Device List</h2>
<table width="100%">
    <tr bgcolor="#18A5DE">
        <td style="width: 12%; color: #fff;padding:8px;"><b>Mobile Number</b></td>
        <td style="width: 15%; color: #fff;padding:8px;"><b>IMEI 1</b></td>
        <td style="width: 15%; color: #fff;padding:8px;"><b>IMEI 2</b></td>
        <td style="width: 26%; color: #fff;padding:8px;"><b>Device ID</b></td>
        <td style="width: 10%; color: #fff;padding:8px;"><b>Status</b></td>
        <td style="width: 9%; color: #fff;padding:8px;"><b>Next Check</b></td>
        <td style="width: 9%; color: #fff;padding:8px;"><b>Action</b></td>
    </tr>
    <?php
            if (!empty($devices)){
                foreach($devices as $value){
                    
                    if ($value['status']) {
                        
                        if ($value['status']==1) {
                            $status = 'Active';
                        } else if($value['status']==2) {
                            $status = 'Warning';
                        } else if($value['status']==3) {
                            $status = 'Closed';
                        } else if($value['status']==4) {
                            $status = 'Blocked';
                        }
                    }


                    echo '<tr>
                            <td style="padding:8px;">'.$value['mobileNumber'].'</td>
                            <td style="padding:8px;">'.$value['IMEI1'].'</td>
                            <td style="padding:8px;">'.$value['IMEI2'].'</td>
                            <td style="padding:8px;">'.wordwrap($value['deviceId'], 40, "<br />", true).'</td>
                            <td style="padding:8px;">'.$status.'</td>
                            <td style="padding:8px;">'.date("jS M y h:i a", strtotime($value['nextCheckDateTime'])).'</td>
                            <td style="padding:8px;"><a href="updateStatus.php?id='.$value['id'].'">Edit</a><br /><a href="javascript:;" onclick="blockUser('.$value['id'].');">Send Push</a></td>';
                }
            } else {
                echo '<tr>
                            <td colspan="2" style="padding:8px;">No devices found.</td>
                      </tr>';
            }

    ?>
</table>

</body>
</html>

<script>
    function blockUser(deviceId){
        $.post( "sendNotification.php", {id: deviceId}, function( data ) {
            alert(data);
            location.reload();
        });
    }
</script>