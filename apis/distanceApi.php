<?php
	$origin = '';
	if(isset($_REQUEST['origin']))
	{
		$origin = $_REQUEST['origin'];
	}

	$destination = '';
	if(isset($_REQUEST['destination']))
	{
		$destination = $_REQUEST['destination'];
	}

	$url = "https://maps.googleapis.com/maps/api/directions/xml";
	$fields = array(
		'origin' =>  $origin,
		'destination' => $destination,	
		'sensor' =>  'false',
		'units' => 'metric',
		'alternatives' => 'false',
		'mode' => 'driving',
	);
	$url = $url . '?' . http_build_query($fields);
	$ch = curl_init();
	curl_setopt($ch,CURLOPT_URL, $url);
	curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch,CURLOPT_HTTPGET, true);
	curl_setopt($ch,CURLOPT_HEADER , false);
	$result = curl_exec($ch);
	$isError = false;
	if($result === false)
	{
		$isError = true;
		echo "Error Number:".curl_errno($ch)."<br>";
		echo "Error String:".curl_error($ch);
	}
	curl_close($ch);
	if($isError == false)
	{		
		$xml = simplexml_load_string($result) or die("Error: Cannot create object");
		//echo round(((int)$xml->route->leg->duration->value)/60,2) . "|" . $xml->route->leg->distance->value;
		echo round(((int)$xml->route->leg->duration->value),2) . "|" . $xml->route->leg->distance->value;		
	}	
?>