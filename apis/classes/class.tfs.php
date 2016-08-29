<?php

/**
 * TaxiForSure API Integration Class
 * Class TFSApi
 * @package clubmycab\api
 */
class TFS {
    protected $appVersion = '4.2.1';
    protected $appVersionCode = 40;
    protected $booking_type = 'p2p';
    protected $source = 'android';
    protected $trip_type = 'pn';

    protected $login_api = 'http://api.taxiforsure.com/user/login/'; // METHOD POST
    protected $nearby_cabs_api = 'http://iospush.taxiforsure.com/getNearestDriversForApp/'; // METHOD GET
    protected $fares_url = 'http://iospush.taxiforsure.com/api/consumer-app/fares-new/v3/'; // METHOD GET
    protected $booking_api = 'http://iospush.taxiforsure.com/api/consumer-app/book-now/'; // METHOD POST
    protected $booking_status_api = 'http://iospush.taxiforsure.com/api/consumer-app/booking-status/'; // METHOD GET
    protected $booking_cancel_api = 'http://iospush.taxiforsure.com/api/customer/cancel-taxi/'; // METHOD POST


    /**
     * @param array $_params
     */
    public function __construct() {
        // Initialize something..
    }

    /**
     * @param string $username
     * @param string $password
     * @return JSON
     * Get user details from login Api
     */
    public function login($username, $password) {
        $params = array(
            'appVersion' => $this->appVersion,
            'username' => $username,
            'password' => $password
        );
        $resp = $this->curl_post($this->login_api, $params);

        return $resp;
    }

    /**
     * @param array $params
     * @return JSON
     * Get fare details from TFS Api
     */
    public function getFareChart($params) {
        $params['booking_type'] = $this->booking_type;
        $params['appVersion'] = $this->appVersion;
        $params['appVersionCode'] = $this->appVersionCode;
        $params['trip_type'] = $this->trip_type;

        $resp = $this->curl_get($this->fares_url, $params);

        return $resp;
    }

    /**
     * @param array $params
     * @return JSON
     * Make booking through TFS Api
     */
    public function makeBooking($params) {
        $params['appVersion'] = $this->appVersion;
        $params['appVersionCode'] = $this->appVersionCode;
        $params['source'] = $this->source;
        $params['booking_type'] = $this->booking_type;

        $resp = $this->curl_post($this->booking_api, $params);

        return $resp;
    }

    /**
     * @param array $params
     * @return JSON
     * Check booking status through TFS Api
     */
    public function checkBookingStatus($params) {
        $params['appVersion'] = $this->appVersion;
        $params['appVersionCode'] = $this->appVersionCode;

        $resp = $this->curl_get($this->booking_status_api, $params);

        return $resp;
    }

    /**
     * @param array $params
     * @return JSON
     * Cancel booking through TFS Api
     */
    public function cancelBooking($params) {
        $params['appVersion'] = $this->appVersion;
        $params['appVersionCode'] = $this->appVersionCode;

        $resp = $this->curl_post($this->booking_cancel_api, $params);

        return $resp;
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
            CURLOPT_USERAGENT => 'Clubmycab cURL Get Request'
        ));
        $resp = curl_exec($curl);
        curl_close($curl);

        return $resp;
    }

    /**
     * @param string $url
     * @param array $params
     * @return Mixed
     * Call POST Api
     */
    public function curl_post($url, $params) {
        $strParams = http_build_query($params);

        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_POST, count($params));
        curl_setopt($ch,CURLOPT_POSTFIELDS, $strParams);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
        $resp = curl_exec($ch);
        curl_close($ch);

        return $resp;
    }
}