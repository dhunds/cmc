<?php

include('connection.php');

$Message = $_POST['Message'];
$Numbers = $_POST['Numbers'];
$Numbers = str_replace(" ", "", $Numbers);
$Numbersnew = substr($Numbers, 1, -1);

$allNumbers = explode(",", $Numbersnew);

foreach ($allNumbers as $MobileNumber) {
    $MobileNumber = trim($MobileNumber);
    $url = "http://luna.a2wi.co.in:7501/failsafe/HttpLink?aid=572697&pin=c12&mnumber=" . $MobileNumber . "&message=" . urlencode($Message);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.0.3705; .NET CLR 1.1.4322)');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $curl_scraped_page = curl_exec($ch);
    curl_close($ch);
    //echo $curl_scraped_page;

    $messageData = split("\&", $curl_scraped_page);
    $infoData = split("\=", $messageData[1]);
    $timeData = split("\=", $messageData[2]);
    $zeroVal = split("\~", $messageData[0]);
    $codeData = split("\=", $zeroVal[1]);
    $requestidData = split("\=", $zeroVal[0]);

    $sql3 = "INSERT INTO smslogs(SmsTo, SmsMessage, SmsRequestID, SmsCode, SmsInfo, SmsTimeAPI) VALUES ('$MobileNumber','$Message','$requestidData[1]','$codeData[1]','$infoData[1]','$timeData[1]')";
    $stmt3 = $con->prepare($sql3);
    $res3 = $stmt3->execute();
    echo $res3;
}


?>