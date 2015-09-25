<?php

include ('connection.php');

$sql123 = "UPDATE `cabopen` set `cabstatus` = 'C' where NOW() > DATE_ADD(`expenddatetime`, INTERVAL 12 HOUR) AND `cabstatus` = 'A'";
$stmt123 = $con->prepare($sql123);
$res123 = $stmt123->execute();

?>