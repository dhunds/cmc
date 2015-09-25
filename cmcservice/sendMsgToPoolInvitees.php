<?php
include ('connection.php');

$sql = "SELECT ps.MemberNumber, ps.MemberName, ps.PoolId
FROM userpoolsslave ps
LEFT JOIN registeredusers ru ON TRIM(ru.MobileNumber) = TRIM( ps.MemberNumber )
WHERE ru.MobileNumber IS NULL AND (SELECT COUNT(SmsTo) FROM smslogs WHERE smslogs.SmsTo=ps.MemberNumber) < 5";

$stmt = $con->query($sql);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

if(count($rows) > 0){
    foreach($rows as $val){
        $sqlSMS = "SELECT SmsMessage FROM smstemplates WHERE SmsshortCode = 'CLUBADD'";
        $stmtSMS = $con->query($sqlSMS);
        $messageSMS = $stmtSMS->fetchColumn();
        $messageSMS = str_replace("OXXXXX", $val['MemberName'] ,$messageSMS);
        $number = "[" . $val['MemberNumber'] . "]";
        //echo $number.'  msg=='.$messageSMS.'<br />';
        sendSMS($number, $messageSMS);
    }
}

function sendSMS($nos,$message)
{
    $ch = curl_init();
    $fields_string = '';
    $fieldsNew = array(
        'Message' => $message,
        'Numbers' => $nos
    );
    foreach($fieldsNew as $key=>$value) {
        $fields_string .= $key.'='. urlencode($value) .'&';
    }
    rtrim($fields_string, '&');
    curl_setopt($ch, CURLOPT_URL, "http://127.0.0.1/cmc/cmcservice/sendsms.php");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
    curl_exec($ch);
}
