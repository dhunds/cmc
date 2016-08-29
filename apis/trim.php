<?php
include ('connection.php');

$stmt = $con->query("SHOW TABLES");
$tables = $stmt->fetchAll(PDO::FETCH_ASSOC);
$i=0;
$j=0;
foreach($tables as $val){
    $stmt1 = $con->query("DESCRIBE ".$val['Tables_in_cmcdev']);
    $columns = $stmt1->fetchAll(PDO::FETCH_ASSOC);

    foreach($columns as $column){
        $sql = "UPDATE ".$val['Tables_in_cmcdev']." set ".$column['Field']." = TRIM(".$column['Field'].")";
        $stmt2 = $con->prepare($sql);
        $res = $stmt2->execute();
        if($res==true){
            $i++;
        }
        $j++;
    }
}
echo "i==".$i.'<br />j=='.$j;
