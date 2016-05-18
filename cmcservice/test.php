<?php

include ('connection.php');
include ('includes/functions.php');
$orderid = rand().'009199101174481463382462240';

$token = getMobikwikToken('00919920981334');
$resp = mobikwikTransfers('10', '0', 'MyMerchantName', 'MBK9005', $orderid, '9910117448', '9920981334', $token);

if ($resp->status == 'SUCCESS') {
    mobikwikTokenRegenerate('00919920981334');
}

echo '<pre>';
print_r($resp);