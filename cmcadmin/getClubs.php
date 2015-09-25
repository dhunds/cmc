<?php
include_once('connection.php');

if(isset($_GET['q']) && $_GET['q'] !='' && isset($_GET['mobNumber']) && $_GET['mobNumber'] !=''){
    $keyword = $_GET['q'];
    $_GET['mobNumber'] = '0091'.substr($_GET['mobNumber'], -10);
    $sql="SELECT PoolName FROM userpoolsmaster WHERE OwnerNumber='".$_GET['mobNumber']."' AND PoolName LIKE '%$keyword%' ORDER BY PoolName";
    $stmt = $con->query($sql);
    $found = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

    if($found > 0)
    {
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
        {
            echo $row['PoolName']."\n";
        }
    }
}

