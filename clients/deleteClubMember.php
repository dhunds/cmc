<?php
include('connection.php');

if (isset($_POST['id'])) {
	$MemberNumber = trim($_POST['id']);
	
	echo $sql = "DELETE FROM cabOwners WHERE id = '$MemberNumber'";
    $stmt = $con->prepare($sql);
    $res = $stmt->execute();

    if ($res === true) {
        echo "success";
    } else {
        echo "fail";
    }
} else {
	echo 'fail';
}
