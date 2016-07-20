<?php
include_once('connection.php');
include_once('header.php');
include_once('topmenu.php');

$unauthorised =0;

if (isset($_POST['submit']) && $_POST['cabId'] !='') {

    $seats = $_POST['seat'];
    $totalSeats = $_POST['totalSeats'];
    $remainingSeats = $_POST['remainingSeats'];
    $distance = $_POST['distance'];
    ///echo '<pre>';
    //print_r($_POST);
    if ($totalSeats > $seats) {
        $remaining = $remainingSeats - ($totalSeats - $seats);
    } else if($totalSeats < $seats) {
        $remaining = $remainingSeats + ($seats - $totalSeats);
    } else {
        $remaining = $remainingSeats;
    }

    $sql = "UPDATE cabopen SET Seats='".$_POST['seat']."', RemainingSeats= '".$remaining."', Distance= '".$distance."' WHERE CabId='".$_POST['cabId']."'";
    //echo $sql;die;
    $stmt = $con->prepare($sql);

    if ($stmt->execute()){
        header('Location:createRide.php');
    } else{
        $msg = 'An error occured. Please try again.';
    }
}
//print_r($_REQUEST);
$sql = "SELECT c.* FROM cabopen c JOIN cabOwners co ON c.MobileNumber=co.mobileNumber AND co.cleintId=".$_SESSION['userId']." AND c.CabId='".$_REQUEST['cabId']."'";
$stmt = $con->query($sql);
$rowCount = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

if($rowCount > 0){
    $ride = $stmt->fetch(PDO::FETCH_ASSOC);
} else {
    $msg = 'You do not have permission to edit this ride.';
    $unauthorised =1;
}

?>

<div class="content pure-u-1-1 pure-u-md-3-4">
    <div class="pure-u-4-4" id="mainContent">

        <div>
            <h4 class="tHeading">Update Ride</h4>
            <?php if($msg){ ?>
                <p style="margin-left: 10px;"><?=$msg;?></p>
            <?php } ?>
            <?php if (!$unauthorised) { ?>
            <div style="padding: 15px;">
                <form method="post" action="">
                    <div>
                        <div class="divRight bluetext">Location:   <span style="color:black"><?=$ride['FromShortName']?> to <?=$ride['ToShortName']?></span></div>
                        <br>
                        <div class="divRight bluetext">Total Seats: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <input type="text" name="seat" placeholder="Seats" value="<?=$ride['Seats']?>"> </div>
                        <div style="clear:both;"></div>
                        <br>

                         <div class="divRight bluetext">Remaining Seats: <span style="color:black"><?=$ride['RemainingSeats']?> </span> </div>
                        <div style="clear:both;"></div>
                        <br>

                        <br>
                        <div class="divRight bluetext">Distance: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <input type="text" name="distance" placeholder="Distance" value="<?=$ride['Distance']?>"> </div>
                        <div style="clear:both;"></div>
                        <br>

                        <div class="divLeft bluetext">
                            <input type="hidden" name="cabId" value="<?=$_REQUEST['cabId']?>">
                            <input type="hidden" name="totalSeats" value="<?=$ride['Seats']?>">
                            <input type="hidden" name="remainingSeats" value="<?=$ride['RemainingSeats']?>">
                            <input type="submit" name="submit" value="Update" class="cBtn">
                        </div>
                        <div class="divRight bluetext"></div>
                    </div>
                </form>

                <br/>
            </div>
            <?php } ?>
            <div style="clear:both;"></div>
        </div>

    </div>
</div>

<?php
include_once('footer.php');
?>

