<?php

include ('connection.php');
$phone = $_POST['phone'];
$pwd = $_POST['pwd'];
$sql = "SELECT * FROM `tblregister` WHERE `phone`='$phone' AND `pwd`='$pwd'";
$result = $con -> query($sql);
//echo $result->rowCount()." ";
$no_of_rows = $con->query("SELECT FOUND_ROWS()")->fetchColumn();
if ($no_of_rows > 0) {
	$data = $result -> fetchAll(PDO::FETCH_ASSOC);
	echo json_encode($data);
} else {
	echo '_JFAIL' ;
}
?>