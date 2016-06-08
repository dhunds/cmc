<?php

/**
 * Mobikwik API Integration Class
 * Class Mobikwik
 * @package clubmycab\api
 */
class Mobikwik {

    protected $_rider;
    protected $_driver;

    protected $merchantId = MID;
    protected $merchantLoadMoneyId = MID_LOADMONEY;
    protected $merchantName = MERCHANT_NAME;
    protected $merchantNumber = MERCHANT_NUMBER;
    protected $apiSecret = API_SECRET;
    protected $tokenRegenerateKey = MOBIKWIK_TOKEN_REGENERATE_KEY;

    protected $loadmoneyApi = LOAD_MONEY_URL; // METHOD GET
    protected $tokenRegenerateApi = TOKEN_REGENERATE_URL; // METHOD POST
    protected $balanceCheckApi = MOBIKWIK_BALANCE_CHECK_URL; // METHOD GET
    protected $peerTransferApi = PEER_TRANSFER_URL; // METHOD POST
    protected $debitApi = DEBIT_API; // METHOD POST

    /**
     * @param array $_params
     */
    public function __construct() {
        // Initialize something..
    }

    /**
     * @param array $params
     * @return JSON
     * Get Token from Mobikwik Api
     */
    public function regenerateToken($mobileNumber) {

        $wallet = $this->getWallet($mobileNumber);

        if (!empty($wallet)) {
            $mobileNumber = substr(trim($mobileNumber), -10);

            $params = array('cell' => $mobileNumber, 'token' => $wallet['token'], 'tokentype' => 1, 'msgcode' => 507, 'mid' => $this->merchantId, 'merchantname' => $this->merchantName, 'encryptKey' => $this->tokenRegenerateKey);
            $checksum = $this->generateChecksum($params);

            unlink($params['encryptKey']);

            if ($checksum) {
                $params['checksum'] = $checksum;
                $this->curl_get($this->tokenRegenerateApi, $params);
            }
        }
        return false;
    }

    /**
     * @param string $mobileNumber
     * @param array $token
     * @return BOOL
     * Save User Token
     */
    public function saveToken($mobileNumber, $token) {
        global $con;

        if ($mobileNumber !='' && $token !='') {

            $wallet = $this->getWallet($mobileNumber);

            if (!empty($wallet)) {
                $sql = "UPDATE userLinkedWallet SET token = '" . $token . "' where mobileNumber = '" . $mobileNumber . "' AND walletId=2";
            } else{
                $sql = "INSERT INTO userLinkedWallet SET mobileNumber = '" . $mobileNumber . "', walletId=2, token = '" . $token . "'";
            }

            $stmt = $con->prepare($sql);

            if ($stmt->execute()){
                return true;
            }
        }

        return false;
    }

    public function generateChecksum($params){

        if (!empty($params)) {
            $arrParams = [];
            $string = '';

            $encryptKey = $param['encryptKey'];
            unlink($param['encryptKey']);

            foreach ($params as $key => $value) {
                $key = strtolower($key);
                $arrParams[$key] = $value;
            }

            ksort($arrParams);

            foreach ($arrParams as $value) {
                $string .= $value;
            }
            $checksum = hash_hmac('sha256', $string, $encryptKey);

            return $checksum;
        }
        return false;
    }

    /**
     * @param string $mobileNumber
     * @param array $token
     * @return BOOL
     * Save User Token
     */
    public function getWallet($mobileNumber) {
        global $con;

        if ($mobileNumber !='') {

            $con->query("SELECT token FROM userLinkedWallet WHERE mobileNumber = '" . $mobileNumber . "' AND walletId=2");
            $walletLinked = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

            if ($walletLinked) {
                $wallet = $stmt->fetch();
                return $wallet;
            }
        }

        return array();
    }
    /**
     * @param string $url
     * @param array $params
     * @return Mixed
     * Call GET Api
     */
    public function curl_get($url, $params) {
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