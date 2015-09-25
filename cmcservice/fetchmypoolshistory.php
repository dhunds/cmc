<?php

include ('connection.php');
	
	
	$MobileNumber = $_REQUEST['MobileNumber'];
	$lastcabid = $_REQUEST['LastCabId'];
	//$sql="call fetchmypoolshistory('$MobileNumber', @totalRows)";
	$sql="call fetchRidesHistoryPageWise('$MobileNumber','$lastcabid', @totalRows)";
	
	$data = $con->query($sql)->fetchAll(PDO::FETCH_ASSOC);

	$total_count = $con->query("select @totalRows;")->fetch(PDO::FETCH_ASSOC);
	
	if($total_count["@totalRows"] === NULL)
	{		
		$total_count = 0;		
	}					
	
	if ($total_count > 0) 
	{
        $arrFinal = [];
        foreach ($data as $val){
           if($val['CabStatus']=='A'){
               $stmt = $con->query("select MemberNumber FROM cabmembers where CabId = '".$val['CabId']."' AND trim(MemberNumber)='".$MobileNumber."' AND settled=1");
               $foundRows = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

               $stmt = $con->query("select MobileNumber FROM cabopen where CabId = '".$val['CabId']."' AND trim(MobileNumber)='".$MobileNumber."' AND
settled=1");
               $foundRows1 = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

               if($foundRows > 0 || $foundRows1 > 0){
                   $val['CabStatus']='I';
                   $arrFinal[] = $val;
               }
           }else{
               $arrFinal[] = $val;
           }
        }
        echo json_encode($arrFinal);
	}
	else
	{
		echo "No Pool Created Yet!!";
	}
	
	
	
 ?>