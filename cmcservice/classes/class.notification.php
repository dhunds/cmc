<?php

/**
 * Push Notification Class for IOS and Android
 * Class Notification
 * @package clubmycab\notification
 */
class Notification {
    protected $_bodyParams;
    protected $_deviceIds;

    const GCM_ACCESS_KEY = 'AIzaSyBqd05mV8c2VTIAKhYP1mFKF7TRueU2-Z0';
    const PASSPHRASE = '!Getgoing15';

    protected $gcmUrl = 'https://android.googleapis.com/gcm/send';

    /**
     * Constructor
     */
    public function __construct() {
            // Do Something
    }

    /**
     * @param string $deviceId
     * @param array $bodyParams
     */
    public function setVariables($deviceIds='', $bodyParams = array()) {
        $this->_bodyParams = $bodyParams;
        $this->_deviceIds = $deviceIds;
    }

    /**
     * @return JSON
     * Send GCM Notification
     */
    public function sendGCMNotification() {
        global $con;

        $tokenFound =0;
        foreach ($this->_deviceIds as $gcmToken){
            if ($gcmToken !=''){
                $tokenFound =1;
            }
        }
        if (!$tokenFound)
            return;

        $fields = array(
            'registration_ids' => $this->_deviceIds,
            'data' => $this->_bodyParams
        );
        $headers = array(
            'Authorization: key=' . self::GCM_ACCESS_KEY,
            'Content-Type: application/json'
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->gcmUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        $result = curl_exec($ch);
        curl_close($ch);

        if ($result === FALSE) {
            return curl_error($ch);
        } else {
            $data = json_decode($result);

            if ($data->failure) {

                foreach ($this->_deviceIds as $gcmToken){
                    $sql = "UPDATE registeredusers SET DeviceToken='' WHERE DeviceToken='".trim($gcmToken)."'";
                    $stmt = $con->prepare($sql);
                    $stmt->execute();
                }
            }
            return $result;
        }

    }

    /**
     * @param string $numbers
     * @param string $message
     * @return JSON
     * Send SMS
     */
    function sendSMS($numbers, $message)
    {
        global $con;

        $numbers = str_replace(" ", "", $numbers);
        $numbersNew = substr($numbers, 1, -1);
        $allNumbers = explode(",", $numbersNew);

        foreach ($allNumbers as $MobileNumber) {
            $MobileNumber = trim($MobileNumber);
            $url = "http://luna.a2wi.co.in:7501/failsafe/HttpLink?aid=572697&pin=c12&signature=ISHARE&mnumber=" . $MobileNumber . "&message=" . urlencode($message);

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.0.3705; .NET CLR 1.1.4322)');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $curl_scraped_page = curl_exec($ch);
            curl_close($ch);

            $messageData = preg_split("\&", $curl_scraped_page);
            $infoData = preg_split("\=", $messageData[1]);
            $timeData = preg_split("\=", $messageData[2]);
            $zeroVal = preg_split("\~", $messageData[0]);
            $codeData = preg_split("\=", $zeroVal[1]);
            $requestidData = preg_split("\=", $zeroVal[0]);

            $sql = "INSERT INTO smslogs(SmsTo, SmsMessage, SmsRequestID, SmsCode, SmsInfo, SmsTimeAPI) VALUES ('$MobileNumber','$message','$requestidData[1]','$codeData[1]','$infoData[1]','$timeData[1]')";
            $stmt = $con->prepare($sql);
            $res = $stmt->execute();
            return $res;
        }
    }

    /**
     * @param array $params
     * @return string
     * Send SMS
     */
    function sendEmailOTP($params){
        require("classes/class.phpmailer.php");

        $mail = new PHPMailer();

        $mail->From     = "support@clubmycab.com";
        $mail->FromName = "ClubMyCab";
        $mail->AddAddress($params['to']);

        $mail->Subject  = "One Time Password (OTP)";
        $mail->Body     = $params['body'];
        $mail->IsHTML(true);
        $mail->WordWrap = 50;

        if(!$mail->Send()) {
            $return = 'Message was not sent.';
            $return .= 'Mailer error: ' . $mail->ErrorInfo;
        } else {
            $return = 'success';
        }
        return $return;
    }

    /**
     * @param array $values
     * @return Boolean
     * Send Log Notification
     */
    function logNotification($values)
    {
        global $con;

        if (!isset($values['RefId'])){
            $values['RefId'] = NULL;
        }

        $sql = "INSERT INTO notifications(NotificationType, SentMemberName, SentMemberNumber, ReceiveMemberName, ReceiveMemberNumber,Message, CabId, DateTime, RefId) VALUES ('".$values['NotificationType']."', '".$values['SentMemberName']."','".$values['SentMemberNumber']."','".$values['ReceiveMemberName']."','".$values['ReceiveMemberNumber']."','".$values['Message']."','".$values['CabId']."', ".$values['DateTime'].", '".$values['RefId']."')";
        $stmt = $con->prepare($sql);
        $res = $stmt->execute();
        $notificationId =  $con->lastInsertId();
        return $notificationId;
    }

    /**
     * @return JSON
     * Send IOS Push Notification
     */
    public function sendIOSNotification() {
        set_time_limit(0);
        if (ENV =='prod') {
            $push_url = 'ssl://gateway.push.apple.com:2195';
            $pemFile = 'cmc.pem';
        } else {
            $push_url = 'ssl://gateway.sandbox.push.apple.com:2195';
            $pemFile = 'cmcdev.pem';
        }
        header('content-type: text/html; charset: utf-8');

        $this->_bodyParams['alert'] = $this->tr_to_utf($this->_bodyParams['gcmText']);
        unset($this->_bodyParams['gcmText']);
        $this->_bodyParams['sound'] = "default";
        $payload["aps"] = $this->_bodyParams;
        $payload = json_encode($payload);
        
        $ctx = stream_context_create();
        stream_context_set_option($ctx, 'ssl', 'local_cert', $pemFile);
        stream_context_set_option($ctx, 'ssl', 'passphrase', self::PASSPHRASE);



        foreach ($this->_deviceIds as $item) {
            //echo $pemFile;
            sleep(1);
            $fp = stream_socket_client($push_url, $err, $errstr, 60, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT, $ctx);

            if (!$fp) {
                //exit("Failed to connect: $err $errstr" . '<br />');
            }

            $msg = chr(0) . pack('n', 32) . pack('H*', $item) . pack('n', strlen($payload)) . $payload;
            fwrite($fp, $msg, strlen($msg));

            if ($fp) {
                fclose($fp);
            }
        }
        set_time_limit(30);
    }

    function tr_to_utf($text) {
        $text = trim($text);
        $search = array('Ü', 'Þ', 'Ð', 'Ç', 'Ý', 'Ö', 'ü', 'þ', 'ð', 'ç', 'ý', 'ö');
        $replace = array('Ãœ', 'Åž', '&#286;ž', 'Ã‡', 'Ä°', 'Ã–', 'Ã¼', 'ÅŸ', 'ÄŸ', 'Ã§', 'Ä±', 'Ã¶');
        $new_text = str_replace($search, $replace, $text);
        return $new_text;
    }
}