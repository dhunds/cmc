<?php

	function sendSMS($MobileNumber, $message)
    {
        global $con;

        $url = "http://luna.a2wi.co.in:7501/failsafe/HttpLink?aid=572697&pin=c12&signature=ISHARE&mnumber=" . $MobileNumber . "&message=" . urlencode($message);
        //echo $url;die;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.0.3705; .NET CLR 1.1.4322)');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $curl_scraped_page = curl_exec($ch);
        curl_close($ch);

        $messageData = explode("&", $curl_scraped_page);
        $infoData = explode("=", $messageData[1]);
        $timeData = explode("=", $messageData[2]);
        $zeroVal = explode("~", $messageData[0]);
        $codeData = explode("=", $zeroVal[1]);
        $requestidData = explode("=", $zeroVal[0]);

        $sql = "INSERT INTO smsLogs(sentTo, message, sentDateTime, smsRequestId, smsCode, smsInfo, smsTimeApi) VALUES ('$MobileNumber','$message', now(), '$requestidData[1]','$codeData[1]','$infoData[1]','$timeData[1]')";
        $stmt = $con->prepare($sql);
        $res = $stmt->execute();

        return $res;
    }

    function setResponse($args){
	    $code = $args['code'];
	    unset($args['code']);

	    http_response_code($code);
	    header('Content-Type: application/json');
	    echo json_encode($args);
	    exit;
	}

    function checkPostForBlank($arrParams){
        $error = 0;
        foreach ($arrParams as $value) {
            if (!isset($_POST[$value]) || trim($_POST[$value]) =='') {
                $error = 1;
            }
        }
        return $error;
    }
