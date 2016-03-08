<?php
$sql = "Select id, title, amount, DATE_FORMAT(validThru, '%D %b %y %h:%i %p') as validThru, status from offers";
$stmt = $con->prepare($sql);
$stmt->execute();
$rowCount = (int) $stmt->rowCount();
?>

<h2 class="headingText" style="margin-bottom: 5px;  ">Offers</h2>
<div>
<div class='pure-u-1'><p style='text-align:right; margin-right: 5px;'><a href='mAddEditOffer.php'>Add New Offers</a>
<div class="pure-g" style="font-size:13px; font-weight:bold;">
    <div class="pure-u-13-24"><p class="tHeading">Title</p></div>
    <div class="pure-u-3-24"><p class="tHeading">Amount</p></div>
    <div class="pure-u-4-24"><p class="tHeading">Valid Thru</p></div>
    <div class="pure-u-4-24"><p class="tHeading">Action</p></div>
</div>

<?php
    if ($rowCount > 0)
    {
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($result as $row)
        {
?>
            <div class="pure-g pure-g1" style="font-size:13px;">
                <div class="pure-u-13-24"><p style="margin-left: 5px;"><?=$row['title']?></p></div>
                <div class="pure-u-3-24"><p style="margin-left: 10px;">Rs.<?=$row['amount']?></p></div>
                <div class="pure-u-4-24"><p><?=$row['validThru']?></p></div>
                <div class="pure-u-4-24"><p><span><?php if($row['status']==1){echo 'Active (<a href="javascript:;" onclick="toggleOfferStatus('.$row['id'].')">Deactivate</a>)';}else{echo 'Inactive (<a href="javascript:;" onclick="toggleOfferStatus('.$row['id'].')">Activate</a>)';}?></span> | <a href="mAddEditOffer.php?id=<?=$row['id']?>">Edit</a></p></div>
            </div>
<?php
        }
    } else {

        echo '<span style="color:Green;font-size:13px; font-weight:bold;">No results to display!</span>';
    }
    echo '</div>';
?>
</div>
<script>
    function toggleOfferStatus(offerId){
        $.post( "toggleOfferStatus.php", {"offerId": offerId}, function( data ) {
            if (data =='success'){
                location.reload();
            }
        });
    }
</script>
