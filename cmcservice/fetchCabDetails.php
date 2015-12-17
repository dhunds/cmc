<?php

include ('connection.php');

function IsNullOrEmptyString($question){
    return (!isset($question) || trim($question)==='');
}

function sort_arr_of_obj($array, $sortby, $direction='asc') 
{
    $sortedArr = array();
    $tmp_Array = array();

    foreach($array as $k => $v) {
        $tmp_Array[] = strtolower($v->$sortby);
    }

    if($direction=='asc'){
        asort($tmp_Array);
    }else{
        arsort($tmp_Array);
    }

    foreach($tmp_Array as $k=>$tmp){
        $sortedArr[] = $array[$k];
    }

    return $sortedArr;

}

function CustomSort($mainCabsData, $con)
{	
	$lsql = 'select * from `cabmode`';
	$lstmt = $con->query($lsql);
	$lno_of_rows = $lstmt->rowCount();	
	$SortedArray = array();
	if($lno_of_rows > 0)
	{		
		while($lrow = $lstmt->fetch())
		{
			$IsFound = false;
			$lsortedArray = array();
			$CabMode = $lrow["ModeID"];
			foreach($mainCabsData as $row) 
			{
				$lstdClass =  new stdClass;
				$lstdClass = $row;
				
				if($lstdClass->CabMode == $CabMode)
				{
					$lsortedArray[] = $lstdClass;
					$IsFound = true;
				}
			}	
			if($IsFound)
			{
				$SortedArray[] = sort_arr_of_obj($lsortedArray,'CabName','asc');
			}			
		}
	}
	return $SortedArray;
}

function CallCabAPI($CabID,$From,$To,$slat,$slon,$elat,$elon) 
{
	if($CabID == 1) // Uber Cabs
	{
		if($elat == '' && $elon == '')
		{
			//get Time estimates
			$url = BASEURL."/uberConnect.php?type=timeestimates";
			$fields = array(
				'lat' =>  $slat,
				'lon' => $slon,		
			);
			$url = $url . '&' . http_build_query($fields);
			$ch = curl_init();
			curl_setopt($ch,CURLOPT_URL, $url);
			curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch,CURLOPT_HTTPGET, true);
			curl_setopt($ch,CURLOPT_HEADER , false);
			$result = curl_exec($ch);
			if($result === false)
			{
			   echo "Error Number:".curl_errno($ch)."<br>";
			   echo "Error String:".curl_error($ch);
			}
			curl_close($ch);
		}
		else
		{
			//get Time estimates
			$url = BASEURL."/uberConnect.php?type=priceestimates";
			$fields = array(
				'lat' =>  $slat,
				'lon' => $slon,	
				'elat' =>  $elat,
				'elon' => $elon,				
			);
			$url = $url . '&' . http_build_query($fields);
			$ch = curl_init();
			curl_setopt($ch,CURLOPT_URL, $url);
			curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch,CURLOPT_HTTPGET, true);
			curl_setopt($ch,CURLOPT_HEADER , false);
			$result = curl_exec($ch);
			if($result === false)
			{
			   echo "Error Number:".curl_errno($ch)."<br>";
			   echo "Error String:".curl_error($ch);
			}
			curl_close($ch);
		}
		
	}
	else if($CabID == 3) //Meru Cabs
	{		
		$url = "http://mobileapp.merucabs.com/NearByCab_Eve/GetNearByCabs.svc/rest/nearby";		
		$fields = array(
			'Lat' =>  $slat,
			'Lng' => $slon,
			'SuggestedRadiusMeters' => 5000,			
			'CabMaxCount' => 10,
		);
		$url = $url . '?' . http_build_query($fields);
		$ch = curl_init();
		curl_setopt($ch,CURLOPT_URL, $url);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch,CURLOPT_HTTPGET, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Accept: application/json'));
		$result = curl_exec($ch);
		if($result === false)
		{
		   echo "Error Number:".curl_errno($ch)."<br>";
		   echo "Error String:".curl_error($ch);
		}
		curl_close($ch);
	}
	else if($CabID == 4) //Taxi For Sure
	{
		$url = "http://www.radiotaxiforsure.in/api/consumer-app/fares-new/";
		$fields = array(
			'pickup_latitude' =>  $slat,
			'pickup_longitude' => $slon,
			'appVersion' => '4.1.1'
		);
		$url = $url . '?' . http_build_query($fields);
		$ch = curl_init();
		curl_setopt($ch,CURLOPT_URL, $url);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch,CURLOPT_HTTPGET, true);
		curl_setopt($ch,CURLOPT_HEADER , false);
		$result = curl_exec($ch);
		if($result === false)
		{
		   echo "Error Number:".curl_errno($ch)."<br>";
		   echo "Error String:".curl_error($ch);
		}
		curl_close($ch);
	}
	else if($CabID == 6) //Mega Cabs
	{		
		$url = "http://175.41.138.72:6060/AccessWs.asmx/GetCabInVicinity";
		$fields = array(
			'lat' =>  $slat,
			'lng' => $slon,
			'city' => $From,
			'key' => '6eu5tcf',
			'platform' => 'android',
			'privateKey' => '6eu5tcf'
		);
		$url = $url . '?' . http_build_query($fields);
		$ch = curl_init();
		curl_setopt($ch,CURLOPT_URL, $url);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch,CURLOPT_HTTPGET, true);
		curl_setopt($ch,CURLOPT_HEADER , false);
		$result = curl_exec($ch);
		if($result === false)
		{
		   echo "Error Number:".curl_errno($ch)."<br>";
		   echo "Error String:".curl_error($ch);
		}
		curl_close($ch);
	}
	return $result;
}
	
$FromCity= $_REQUEST['FromCity'];

$ToCity = '';
if(isset($_REQUEST['ToCity']))
{
	$ToCity = $_REQUEST['ToCity'];
}

$lat = '';
if(isset($_REQUEST['slat']))
{
	$lat = $_REQUEST['slat'];
}

$lon = '';
if(isset($_REQUEST['slon']))
{
	$lon = $_REQUEST['slon'];
}

$elat = '';
if(isset($_REQUEST['elat']))
{
	$elat = $_REQUEST['elat'];
}
$elon = '';
if(isset($_REQUEST['elon']))
{
	$elon = $_REQUEST['elon'];
}

$dist = '';
if(isset($_REQUEST['dist']))
{
	$dist = $_REQUEST['dist'];
}

$stime = '';
if(isset($_REQUEST['stime']))
{
	$stime = $_REQUEST['stime'];
}

$etime = '';
if(isset($_REQUEST['etime']))
{
	$etime = $_REQUEST['etime'];
}

/*$myfile = 'Request' . date('YmdHis') . '.txt';
file_put_contents($myfile, "FromCity : " . $FromCity, FILE_APPEND | LOCK_EX);
file_put_contents($myfile, PHP_EOL . "ToCity : " . $ToCity, FILE_APPEND | LOCK_EX);
file_put_contents($myfile, PHP_EOL . "lat : " . $lat, FILE_APPEND | LOCK_EX);
file_put_contents($myfile, PHP_EOL . "lon : " . $lon, FILE_APPEND | LOCK_EX);
file_put_contents($myfile, PHP_EOL . "elat : " . $elat, FILE_APPEND | LOCK_EX);
file_put_contents($myfile, PHP_EOL . "elon : " . $elon, FILE_APPEND | LOCK_EX);
file_put_contents($myfile, PHP_EOL . "dist : " . $dist, FILE_APPEND | LOCK_EX);
file_put_contents($myfile, PHP_EOL . "stime : " . $stime, FILE_APPEND | LOCK_EX);
file_put_contents($myfile, PHP_EOL . "etime : " . $etime, FILE_APPEND | LOCK_EX);
*/

$sql = '';
if($ToCity == '')
{
	$fromgroup = '';
	$fromstmt = $con->query("SELECT `CityGroup` FROM `groupcities` WHERE `City` = '$FromCity'");
	//$cityRows = $fromstmt->rowCount();
	$cityRows = $con->query("SELECT FOUND_ROWS()")->fetchColumn();
	if($cityRows > 0)
	{
		$fromgroup  = $fromstmt->fetchColumn(); 
	}	
	if($fromgroup != '')
	{
		$FromCity = $fromgroup;
	}

	$sql = "SELECT a.*,b.`CabName`, b.`CabMobileSite`,b.`CabMode`,b.`CabPackageName`, (SELECT `ModeName` FROM `cabmode` where `ModeID` = b.`CabMode`)  As ModeName, ((SELECT SUM(`Rating`) FROM `cabrating` where `CabDetailID` = a.`CabDetailID`)/(SELECT COUNT(*) FROM `cabrating` where `CabDetailID` = a.`CabDetailID`)) As Rating, '' as timeEstimate, '' as priceEstimate, '' as product_id FROM `cabdetails` a , `cabnames` b WHERE a.`City` = '$FromCity' AND a.`Active` = 1 and b.`CabNameID` = a.`CabNameID` and a.`OutStation` = 'N' and a.`Active` = 1";	
}
else
{
	if($FromCity == $ToCity)
	{
		$fromgroup = '';
		$fromstmt = $con->query("SELECT `CityGroup` FROM `groupcities` WHERE `City` = '$FromCity'");
		//$cityRows = $fromstmt->rowCount();
		$cityRows = $con->query("SELECT FOUND_ROWS()")->fetchColumn();
		if($cityRows > 0)
		{
			$fromgroup  = $fromstmt->fetchColumn(); 
		}	
		if($fromgroup != '')
		{
			$FromCity = $fromgroup;
		}
		$sql = "SELECT a.*,b.`CabName`, b.`CabMobileSite`,b.`CabMode`,b.`CabPackageName`, (SELECT `ModeName` FROM `cabmode` where `ModeID` = b.`CabMode`)  As ModeName, ((SELECT SUM(`Rating`) FROM `cabrating` where `CabDetailID` = a.`CabDetailID`)/(SELECT COUNT(*) FROM `cabrating` where `CabDetailID` = a.`CabDetailID`)) As Rating, '' as timeEstimate, '' as priceEstimate, '' as product_id FROM `cabdetails` a , `cabnames` b WHERE a.`City` = '$FromCity' AND a.`Active` = 1 and b.`CabNameID` = a.`CabNameID` and a.`OutStation` = 'N' and a.`Active` = 1";		
	}
	else
	{
		$fromgroup = '';
		$fromstmt = $con->query("SELECT `CityGroup` FROM `groupcities` WHERE `City` = '$FromCity'");
		//$cityRows = $fromstmt->rowCount();
		$cityRows = $con->query("SELECT FOUND_ROWS()")->fetchColumn();
		if($cityRows > 0)
		{
			$fromgroup  = $fromstmt->fetchColumn(); 
		}			
		
		$togroup = '';
		$tostmt = $con->query("SELECT `CityGroup` FROM `groupcities` WHERE `City` = '$ToCity'");
		//$cityRows = $tostmt->rowCount();
		$cityRows = $con->query("SELECT FOUND_ROWS()")->fetchColumn();
		if($cityRows > 0)
		{
			$togroup  = $tostmt->fetchColumn(); 
		}			
		
		if($fromgroup == $togroup)
		{
			$sql = "SELECT a.*,b.`CabName`, b.`CabMobileSite`,b.`CabMode`,b.`CabPackageName`, (SELECT `ModeName` FROM `cabmode` where `ModeID` = b.`CabMode`)  As ModeName, ((SELECT SUM(`Rating`) FROM `cabrating` where `CabDetailID` = a.`CabDetailID`)/(SELECT COUNT(*) FROM `cabrating` where `CabDetailID` = a.`CabDetailID`)) As Rating, '' as timeEstimate, '' as priceEstimate, '' as product_id FROM `cabdetails` a , `cabnames` b WHERE a.`City` = '$fromgroup' AND a.`Active` = 1 and b.`CabNameID` = a.`CabNameID` and a.`OutStation` = 'N' and a.`Active` = 1";						
		}
		else
		{
			$sql = "SELECT a.*,b.`CabName`, b.`CabMobileSite`,b.`CabMode`,b.`CabPackageName`, (SELECT `ModeName` FROM `cabmode` where `ModeID` = b.`CabMode`)  As ModeName, ((SELECT SUM(`Rating`) FROM `cabrating` where `CabDetailID` = a.`CabDetailID`)/(SELECT COUNT(*) FROM `cabrating` where `CabDetailID` = a.`CabDetailID`)) As Rating, '' as timeEstimate, '' as priceEstimate, '' as product_id FROM `cabdetails` a , `cabnames` b WHERE a.`City` = '$FromCity' AND a.`Active` = 1 and b.`CabNameID` = a.`CabNameID` and a.`OutStation` = 'Y' and a.`Active` = 1";			
		}	
	}
}

$stmt = $con->query($sql);
//$no_of_rows = $stmt->rowCount();	
$no_of_rows = $con->query("SELECT FOUND_ROWS()")->fetchColumn();
if ($no_of_rows > 0) 
{	
	$ApiCabs = array();
	$mainCabsData = array();
	while($row = $stmt->fetch())
	{
		if($row['CabMode'] == 1)
		{
			if (in_array($row['CabNameID'] . '|' . $row['Rating'], $ApiCabs) == false)
			{
				$ApiCabs[] = $row['CabNameID'] . '|' . $row['Rating'] . '|' . $row['CabMode'];
			}		
		}
		else
		{					
			$CabsAllData = new stdClass;
			$CabsAllData->CabDetailID = $row['CabDetailID'];							
			$CabsAllData->CabContactNo = $row['CabContactNo'];
			$CabsAllData->Outstation = $row['Outstation'];			
			$CabsAllData->CarType = $row['CarType'];
			if($CabsAllData->Outstation == 'N')
			{
				$CabsAllData->BaseFare = $row['BaseFare'];
				$CabsAllData->BaseFareKM = $row['BaseFareKM'];	
				$CabsAllData->RatePerKMAfterBaseFare = $row['RatePerKMAfterBaseFare'];	
				$CabsAllData->NightTimeStartHours = $row['NightTimeStartHours'];	
				$CabsAllData->NightTimeEndHours =  $row['NightTimeEndHours'];	
				$CabsAllData->NightTimeRateMultiplier =  $row['NightTimeRateMultiplier'];	
				$CabsAllData->City =  $row['City'];						
				if(IsNullOrEmptyString($CabsAllData->BaseFare) == false)
				{
					if($dist != '')
					{
						if($dist < $CabsAllData->BaseFareKM)
						{
							$CabsAllData->Cost = floatval($row['BaseFare']);		
						}
						
						if($dist > $CabsAllData->BaseFareKM)
						{
							$CabsAllData->Cost = floatval($row['BaseFare']) + ($dist - floatval($CabsAllData->BaseFareKM)) * floatval($row['RatePerKMAfterBaseFare']);		
						}
						
						if($CabsAllData->NightTimeStartHours != "")
						{
							/*$parts = preg_split('/\s+/', $stime);										
							$sMHours = substr($parts[1], 0, 2); 
							$sMMinutes = substr($parts[1], -2); */
												
							//$sdatetime = date_create_from_format('d/m/Y H:i', $parts[0] . ' ' . $sMHours . ':' . $sMMinutes);
							if($stime != '')
							{
								$sdatetime = date_create_from_format('d/m/Y H:i:s', $stime);											
								$edatetime = $sdatetime->modify("+" . intval($etime) . " minutes"); 											
								$sdate = date_format($sdatetime,'d/m/Y');											
								
								$sHours = substr($CabsAllData->NightTimeStartHours, 0, 2); 
								$sMinutes = substr($CabsAllData->NightTimeStartHours, -2); 
								$nightstartdatetime = date_create_from_format('d/m/Y H:i', $sdate . ' ' . $sHours . ':' . $sMinutes);
								
														
								$eHours = substr($CabsAllData->NightTimeEndHours, 0, 2); 
								$eMinutes = substr($CabsAllData->NightTimeEndHours, -2); 
								$nightenddatetime = date_create_from_format('d/m/Y H:i', $sdate . ' ' . $eHours . ':' . $eMinutes);
								
								$nextdaydatetime = $nightenddatetime->modify("+1 days"); 										
													
								if($CabsAllData->NightTimeStartHours == "0000")
								{																	
									$nextdaystartdatetime = $nightstartdatetime->modify("+1 days"); 
									
									if($edatetime >= $nextdaystartdatetime && $edatetime <=$nextdaydatetime){
										$CabsAllData->Cost = floatval($CabsAllData->Cost) * floatval($CabsAllData->NightTimeRateMultiplier);							
									}																	
								}
								else
								{								
									if($edatetime >= $nightstartdatetime && $edatetime <=$nextdaydatetime){														
										$CabsAllData->Cost = floatval($CabsAllData->Cost) * floatval($CabsAllData->NightTimeRateMultiplier);							
									}						
								}
							}
						}
						$CabsAllData->Cost = intval($CabsAllData->Cost);
						$CabsAllData->low_estimate = $CabsAllData->Cost;
						$CabsAllData->high_estimate = intval($CabsAllData->Cost * 1.25);
					}					
				}	
				else {
					$CabsAllData->BaseFare = "NA";
					if($dist != '')
					{
						$CabsAllData->Cost = "NA";
						$CabsAllData->low_estimate = "NA";
						$CabsAllData->high_estimate = "NA";
					}
				}				
			}
			else
			{
				$CabsAllData->fortykmorfourhours = $row['40kmor4hours'];	
				$CabsAllData->eightykmoreighthours = $row['80kmor8hours'];	
				$CabsAllData->OvernightCharges = $row['OvernightCharges'];	
			}
								
			$CabsAllData->CabName =  $row['CabName'];
			$CabsAllData->CabMobileSite =  $row['CabMobileSite'];
			$CabsAllData->CabMode =  $row['CabMode'];	
			$CabsAllData->CabPackageName = $row['CabPackageName'];
			$CabsAllData->Rating =  $row['Rating'];
			$mainCabsData[] = $CabsAllData;
		}		
	}	
	if(count($ApiCabs) > 0)	
	{				
		foreach ($ApiCabs as $cabItem) {
			$arrList = explode("|",$cabItem);			
			if($arrList[0] == 1) // Uber Cabs
			{							
				$cabData = CallCabAPI($arrList[0],$FromCity,'',$lat,$lon,'','');					
				$uberProductsTime= json_decode($cabData);		
				if(isset($uberProductsTime->times))
				{
					foreach($uberProductsTime->times as $item) { 														
						$CabsAllData = new stdClass;					
						$CabsAllData->CabName = $item->display_name;
						$CabsAllData->Rating =  $arrList[1];					
						$CabsAllData->timeEstimate =  $item->estimate;						
						$CabsAllData->productId =  $item->product_id;
						$CabsAllData->CabMode =  $arrList[2];						
						$mainCabsData[] = $CabsAllData;					
					}	
				}
				if($elat != '' && $elon != '')
				{
					$cabData = CallCabAPI($arrList[0],$FromCity,$ToCity,$lat,$lon,$elat,$elon);					
					$uberProducts = json_decode($cabData);	
					if(isset($uberProducts->prices))
					{
						foreach($uberProducts->prices as $item) { 	
						
							$isFound = false;
							foreach($mainCabsData as $row) 
							{
								$lstdClass =  new stdClass;
								$lstdClass = $row;								
								if(isset($lstdClass->productId))
								{
									if($item->product_id == $lstdClass->productId)
									{
										$lstdClass->high_estimate =  $item->high_estimate;
										$lstdClass->duration =  $item->duration;
										$lstdClass->low_estimate =  $item->low_estimate;
										$lstdClass->surge_multiplier =  $item->surge_multiplier;									
										$isFound = true;
										break;
									}
								}
							}	
							if($isFound == false)
							{
								$CabsAllData = new stdClass;					
								$CabsAllData->CabName =   $item->display_name;
								$CabsAllData->Rating =  $arrList[1];
								$CabsAllData->CabMode =  $arrList[2];
								$CabsAllData->high_estimate =  $item->high_estimate;
								//$CabsAllData->minimum =  $item->minimum;
								$CabsAllData->duration =  $item->duration;
								//$CabsAllData->estimate =  $item->estimate;
								//$CabsAllData->distance =  $item->distance;
								$CabsAllData->productId =  $item->product_id;
								$CabsAllData->low_estimate =  $item->low_estimate;
								$CabsAllData->surge_multiplier =  $item->surge_multiplier;
								//$CabsAllData->currency_code =  $item->currency_code;
								$mainCabsData[] = $CabsAllData;	
							}							
						}	
					}
				}							
			}
			else if($arrList[0] == 3) //Meru Cabs
			{					
				$cabData = CallCabAPI($arrList[0],'','',$lat,$lon,'','');							
				$MeruProducts = json_decode(json_decode($cabData),true);					
				if(isset($MeruProducts["Cablist"]))
				{								
					foreach($MeruProducts["Cablist"] as $item) {						
						$CabsAllData = new stdClass;
						$CabsAllData->CabName = "Meru";
						$CabsAllData->CarName = $item["Brand"];											
						$CabsAllData->Rating = $arrList[1];
						$CabsAllData->CabMode = $arrList[2];
						$CabsAllData->DeviceId = $item["DeviceId"];
						$CabsAllData->Lat = $item["Lat"];
						$CabsAllData->Lng = $item["Lng"];
						$CabsAllData->OrientationDegrees = $item["OrientationDegrees"];
						$CabsAllData->TrafficETA =  $item["TrafficETA"];
						$CabsAllData->AddtoETA = $MeruProducts["AddtoETA"];
						$CabsAllData->ExceedTime = $MeruProducts["ExceedTime"];						
						$mainCabsData[] = $CabsAllData;							
					}
				}						
			}
			else if($arrList[0] == 4) //Taxi For Sure
			{	
				$cabData = CallCabAPI($arrList[0],'','',$lat,$lon,'','');	
				$TaxiForSure= json_decode($cabData,true);				
				if(isset($TaxiForSure["response_data"]))
				{					
					$dayTimings = $TaxiForSure["response_data"]["day"]["timings"];										
					$stDateTime = explode(" ", $stime);
					$stTimeData = explode(":", $stDateTime[1]);
					$astTime = (int)$stTimeData[0];

					$aTime = 0;
					$bTime = 0;
					$stdayTimings = explode("-",$dayTimings);
					if (strpos($stdayTimings[0],'AM') !== false) {
						$aTime = (int) str_replace('AM','',$stdayTimings[0]);
					}
					if (strpos($stdayTimings[1],'PM') !== false) {
						$bTime = (int)str_replace('PM','',$stdayTimings[1]);
						if($bTime != 12)
						{
							$bTime = $bTime + 12;
						}
					}
					$IsDayFares = false;
					if($astTime > $aTime &&  $astTime < $bTime)
					{
						$IsDayFares = true;
					}
					if($IsDayFares)
					{
						foreach($TaxiForSure["response_data"]["day"]["at-km"] as $item) {
							$CabsAllData = new stdClass;
							$CabsAllData->CabName = "TaxiForSure";
							$CabsAllData->CarName = $item["car_name"];
							$CabsAllData->Rating = $arrList[1];
							$CabsAllData->CabMode = $arrList[2];
							$CabsAllData->extra_km_fare = $item["extra_km_fare"];
							$CabsAllData->base_km = $item["base_km"];
							$CabsAllData->has_ac = $item["has_ac"];
							$CabsAllData->base_fare = $item["base_fare"];
							$CabsAllData->waiting_message =  $TaxiForSure["response_data"]["day"]["waiting_message"];							
							$mainCabsData[] = $CabsAllData;	
						}
					}
					else
					{										
						foreach($TaxiForSure["response_data"]["night"]["at-km"] as $item) {
							$CabsAllData = new stdClass;	
							$CabsAllData->CabName = "TaxiForSure";
							$CabsAllData->CarName = $item["car_name"];
							$CabsAllData->Rating = $arrList[1];
							$CabsAllData->CabMode = $arrList[2];
							$CabsAllData->extra_km_fare = $item["extra_km_fare"];
							$CabsAllData->base_km = $item["base_km"];
							$CabsAllData->has_ac = $item["has_ac"];
							$CabsAllData->base_fare = $item["base_fare"];
							$CabsAllData->waiting_message =  $TaxiForSure["response_data"]["day"]["waiting_message"];							
							$mainCabsData[] = $CabsAllData;		
						}
					}
				}					
			}
			else if($arrList[0] == 6) //Mega Cabs
			{				
				$cabData = CallCabAPI($arrList[0],$FromCity,'',$lat,$lon,'','');					
				$MegaCabProducts= json_decode($cabData);	
				if(isset($MegaCabProducts->data))
				{					
					foreach($MegaCabProducts->data->CAB as $item) { 						
						$CabsAllData = new stdClass;	
						$CabsAllData->CabName = "Mega";
						$CabsAllData->Carno = $item->CARNO;
						$CabsAllData->Rating = $arrList[1];
						$CabsAllData->CabMode = $arrList[2];
						$CabsAllData->Car_Rto_No = $item->CAR_RTO_NO;
						$CabsAllData->Lat = $item->LAT;
						$CabsAllData->Lng = $item->LONG;					
						$mainCabsData[] = $CabsAllData;	
					}
				}						
			}
		}		
	}	
	
	$mainData = array();	
	$mainData = CustomSort($mainCabsData, $con);	
	//file_put_contents($myfile, PHP_EOL . "result : " . json_encode($mainData), FILE_APPEND | LOCK_EX);
	echo json_encode($mainData);	
}
else
{
	echo "Error";
}
?>