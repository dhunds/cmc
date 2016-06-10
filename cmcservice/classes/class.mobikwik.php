<?php

/**
 * Mobikwik API Integration Class
 * Class Mobikwik
 * @package clubmycab\api
 */
class Mobikwik
{

    protected $_rider;
    protected $_driver;

    protected $merchantId = MID;
    protected $merchantLoadMoneyId = MID_LOADMONEY;
    protected $merchantName = MERCHANT_NAME;
    protected $merchantNumber = MERCHANT_NUMBER;
    protected $walletId = WALLET_ID;
    protected $apiSecret = API_SECRET;
    protected $tokenRegenerateKey = MOBIKWIK_TOKEN_REGENERATE_KEY;

    protected $loadmoneyApi = LOAD_MONEY_URL;
    protected $tokenRegenerateApi = TOKEN_REGENERATE_URL;
    protected $balanceCheckApi = MOBIKWIK_BALANCE_CHECK_URL;
    protected $peerTransferApi = PEER_TRANSFER_URL;
    protected $debitApi = DEBIT_API;


    public function __construct()
    {
        // Initialize something..
    }

    public function checkBalance($mobileNumber)
    {
        $wallet = $this->getWallet($mobileNumber);
        $mobileNumberWithPrefix = $mobileNumber;

        if (!empty($wallet)) {
            $mobileNumber = substr(trim($mobileNumber), -10);

            $params = array('cell' => $mobileNumber, 'token' => $wallet['token'], 'msgcode' => 501, 'mid' => $this->merchantId, 'merchantname' => $this->merchantName, 'encryptKey' => $this->apiSecret);

            $checksum = $this->generateChecksum($params);

            unset($params['encryptKey']);

            if ($checksum) {
                $params['checksum'] = $checksum;
                $resp = $this->curl_get($this->balanceCheckApi, $params);

                if ($resp->status == 'SUCCESS') {
                    $this->regenerateToken($mobileNumberWithPrefix);

                } else if ((string)$resp->statuscode == '199') {

                    $tokenResp = simplexml_load_string($this->regenerateToken($mobileNumberWithPrefix));

                    if ($tokenResp->status == 'SUCCESS') {
                        $this->checkBalance($mobileNumberWithPrefix);
                    }
                }
                return $resp;
            }
        }
        return false;
    }

    public function transferFromUserToMerchant($mobileNumber, $amount, $orderId, $cabId, $serviceCharge='', $serviceTax='')
    {
        $mobileNumberWithPrefix = $mobileNumber;
        $wallet = $this->getWallet($mobileNumber);

        if ($wallet['token']) {
            $mobileNumber = substr(trim($mobileNumber), -10);

            $params = array('amount' => $amount, 'cell' => $mobileNumber, 'comment' => 'serviceCharge', 'merchantname' => $this->merchantName, 'mid' => $this->merchantId, 'msgcode' =>503, 'orderid' => $orderId, 'token' => $wallet['token'],  'txntype' => 'debit', 'encryptKey' => $this->apiSecret);

            $checksum = $this->generateChecksum($params);
            unset($params['encryptKey']);

            if ($checksum) {
                $params['checksum'] = $checksum;
                $resp = $this->curl_get($this->debitApi, $params);

                if ($resp === FALSE) {
                    return FALSE;
                } else {
                    $resp = simplexml_load_string($resp);

                    $return = array("status"=>"fail");

                    if ($resp->status == 'SUCCESS') {
                        $this->regenerateToken($mobileNumberWithPrefix);

                        $this->logTransaction($resp, $mobileNumberWithPrefix, 'system', $amount, $serviceCharge, $serviceTax, $cabId, $orderId);

                        $return = array("status"=>"success");

                    } else if ((string)$resp->statuscode == '199') {
                        $tokenResp = simplexml_load_string($this->regenerateToken($mobileNumberWithPrefix));

                        if($tokenResp->status == 'SUCCESS') {
                            $this->transferFromUserToMerchant($mobileNumberWithPrefix, $amount, $orderId, $cabId, $serviceCharge, $serviceTax);
                        }
                    }
                    return $return;
                }
            }
        }
        return false;
    }

    public function transferFromMerchantToDriver($mobileNumber, $amount, $orderId, $cabId, $serviceCharge='', $serviceTax='')
    {
        $mobileNumberWithPrefix = $mobileNumber;
        $mobileNumber = substr(trim($mobileNumber), -10);

        $params = array('amount' => $amount, 'typeofmoney' => 0, 'cell' => $mobileNumber, 'orderid' => $orderId, 'creditmethod' => 'cashback', 'walletid' => $this->walletId, 'mid' => $this->merchantId, 'merchantname' => $this->merchantName, 'encryptKey' => $this->apiSecret);

        $checksum = $this->generateChecksum($params);
        unset($params['encryptKey']);

        if ($checksum) {
            $params['checksum'] = $checksum;
            $resp = $this->curl_get($this->debitApi, $params);

            if ($resp === FALSE) {
                return FALSE;
            } else {
                $resp = simplexml_load_string($resp);

                if ($resp->status == 'SUCCESS') {

                    $this->logTransaction($resp, 'system', $mobileNumberWithPrefix,  $amount, $serviceCharge, $serviceTax, $cabId, $orderId);

                    return array("status"=>"success");
                } else {
                    return array("status"=>"fail");
                }
            }
        }

        return false;
    }

    public function peerTransfer($senderMobileNumber, $receiverMobileNumber, $amount, $orderId)
    {
        $senderMobileNumberWithoutPrefix = substr(trim($senderMobileNumber), -10);
        $receiverMobileNumberWithoutPrefix = substr(trim($receiverMobileNumber), -10);

        $wallet = $this->getWallet($senderMobileNumber);

        if ($wallet['token']) {

            $params = array('amount' => $amount, 'fee' => 0, 'merchantname' => $this->merchantName, 'mid' => $this->merchantId, 'orderid' => $orderId, 'receivercell' => $receiverMobileNumberWithoutPrefix, 'sendercell' => $senderMobileNumberWithoutPrefix, 'token' => $wallet['token'], 'encryptKey' => $this->apiSecret);

            $checksum = $this->generateChecksum($params);
            unset($params['encryptKey']);

            if ($checksum) {
                $params['checksum'] = $checksum;
                $resp = $this->curl_get($this->peerTransferApi, $params);

                if ($resp === FALSE) {
                    return FALSE;
                } else {
                    $resp = simplexml_load_string($resp);

                    if ($resp->status == 'SUCCESS') {
                        $this->regenerateToken($senderMobileNumber);

                    } else if ((string)$resp->statuscode == '199') {

                        $tokenResp = simplexml_load_string($this->regenerateToken($senderMobileNumber));

                        if($tokenResp->status == 'SUCCESS') {
                            $this->peerTransfer($senderMobileNumber, $receiverMobileNumber, $amount, $orderId);
                        }
                    }
                    return $resp;
                }
            }
        }
        return false;
    }

    public function logTransaction($responseObj, $paidBy, $paidTo, $amount, $serviceCharge, $serviceTax, $cabId, $orderId) {
        global $con;

        $sql = "INSERT INTO walletTransactionLogs SET transactionId = '" . $responseObj->refId . "', orderId = '$orderId', paidBy='$paidBy', paidTo = '$paidTo', amount = $amount, serviceCharge =  $serviceCharge, serviceTax = '$serviceTax', cabId='$cabId', walletId=2, status='".$responseObj->status."', transactionResp='".json_encode($responseObj)."'";

        $stmt = $con->prepare($sql);

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    public function regenerateToken($mobileNumber)
    {

        $wallet = $this->getWallet($mobileNumber);

        if (!empty($wallet)) {
            $mobileNumber = substr(trim($mobileNumber), -10);

            $params = array('cell' => $mobileNumber, 'token' => $wallet['token'], 'tokentype' => 1, 'msgcode' => 507, 'mid' => $this->merchantId, 'merchantname' => $this->merchantName, 'encryptKey' => $this->tokenRegenerateKey);
            $checksum = $this->generateChecksum($params);

            unset($params['encryptKey']);

            if ($checksum) {
                $params['checksum'] = $checksum;
                $resp = $this->curl_get($this->tokenRegenerateApi, $params);
                return $resp;
            }
        }
        return false;
    }

    public function saveToken($mobileNumber, $token)
    {
        global $con;

        if ($mobileNumber != '' && $token != '') {

            $wallet = $this->getWallet($mobileNumber);

            if (!empty($wallet)) {
                $sql = "UPDATE userLinkedWallet SET token = '" . $token . "' where mobileNumber = '" . $mobileNumber . "' AND walletId=2";
            } else {
                $sql = "INSERT INTO userLinkedWallet SET mobileNumber = '" . $mobileNumber . "', walletId=2, token = '" . $token . "'";
            }

            $stmt = $con->prepare($sql);

            if ($stmt->execute()) {
                return true;
            }
        }

        return false;
    }

    public function generateChecksum($params)
    {

        if (!empty($params)) {
            $arrParams = [];
            $string = '';

            $encryptKey = $params['encryptKey'];
            unset($params['encryptKey']);

            foreach ($params as $key => $value) {
                $key = strtolower($key);
                $arrParams[$key] = $value;
            }

            ksort($arrParams);

            foreach ($arrParams as $value) {
                $string .= "'".$value."'";
            }
            $checksum = hash_hmac('sha256', $string, $encryptKey);

            return $checksum;
        }
        return false;
    }

    public function getWallet($mobileNumber)
    {
        global $con;

        if ($mobileNumber != '') {

            $stmt = $con->query("SELECT token FROM userLinkedWallet WHERE mobileNumber = '" . $mobileNumber . "' AND walletId=2");
            $walletLinked = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

            if ($walletLinked) {
                $wallet = $stmt->fetch();
                return $wallet;
            }
        }

        return array();
    }

    public function curl_get($url, $params)
    {
        $strParams = http_build_query($params);
        $url = $url . '?' . $strParams;

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $url,
            CURLOPT_HTTPHEADER => array('Content-Type: application/json')
        ));
        $resp = curl_exec($curl);
        curl_close($curl);

        return $resp;
    }

}