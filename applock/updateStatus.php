<?php
	include('connection.php');

    if (isset($_POST['submit'])){
        $status = $_POST['status'];
        $nextCheckDateTime = $_POST['time'];
        $sql = "UPDATE deviceDetails SET status=$status, nextCheckDateTime='$nextCheckDateTime' WHERE id=".$_POST['id'];
        //echo $sql;die;
        $stmt = $con->prepare($sql);
        $stmt->execute();
        header('location:index.php');
    }

    $stmt = $con->query("SELECT status, nextCheckDateTime  FROM deviceDetails WHERE id=".$_GET['id']);
    $devices = $stmt->fetch(PDO::FETCH_ASSOC);

?>


<html lang="en">
<head>
    <title>Edit</title>
    <link rel="stylesheet" type="text/css" media="all" href="Calendar/calendar-blue.css" title="win2k-cold-1">
    <script type="text/javascript" src="Calendar/calendar.js"></script>
    <script type="text/javascript" src="Calendar/lang/calendar-en.js"></script>
    <script type="text/javascript" src="Calendar/calendar-setup.js"></script>
<body>
	<h2>Change Status</h2>
<form action="" method="post">
<table>
    <tr>
        <td style="width: 20%; padding:8px;">Status</td>
        <td >
            <select name="status">
                <option value="1" <?php if ($devices['status']==1){ echo 'selected';}?> >Active</option>
                <option value="2" <?php if ($devices['status']==2){ echo 'selected';}?>>Warning</option>
                <option value="3" <?php if ($devices['status']==3){ echo 'selected';}?>>Closed</option>
                <option value="4" <?php if ($devices['status']==4){ echo 'selected';}?>>Blocked</option>
            </select>
        </td>
    </tr>
    <tr>
        <td style="width: 20%; padding:8px;">Next Check Time</td>
        <td>
            <input id="time" name="time" class="controls" type="text" placeholder="Time" value="<?=$devices['nextCheckDateTime'];?>"style="width:150px;" readonly> <img src="images/calendar.png" id="dtFrom">&nbsp;
                        <script type="text/javascript">
                            Calendar.setup({
                                inputField: 'time',
                                ifFormat: "%Y-%m-%d %H:%M:%S",
                                button: "dtFrom",
                                singleClick: true,
                                showsTime:true,
                                timeFormat:12,
                                electric:true
                            });
                        </script>
        </td>
    </tr>
    <tr>
        <td colspan="2"><input type="submit" value="UPDATE" name="submit" style="background-color:#18A5DE;border:none;padding:10px 15px;color:#ffffff;font-size:12px; margin:10px 0 0 10px;"></td>
    </tr>
    
</table>
<input type="hidden" name="id" value="<?=$_GET['id']?>">
</form>
</body>
</html>