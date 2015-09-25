<?php
include_once('connection.php');

$sql="UPDATE offers SET status = IF(status=1, 0, 1) WHERE id=".$_POST['offerId'];
$stmt = $con->prepare($sql);

if ($stmt->execute()) {
    echo 'success';
} else {
    echo 'fail';
}
exit();
