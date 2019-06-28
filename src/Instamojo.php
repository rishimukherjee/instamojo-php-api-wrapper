<?php

namespace Instamojo;

use Instamojo\Exceptions\AuthenticationException;
use Instamojo\Exceptions\InvalidRequestException;
use Instamojo\Exceptions\MissingParameterException;

class Instamojo {
    // Constants
    const API_VERSION         = '2';
    const VALID_TYPES         = ['app', 'user', 'refresh'];
    const TEST_BASE_URL       = 'https://test.instamojo.com/';
    const PRODUCTION_BASE_URL = 'https://api.instamojo.com/';
    
    const URIS = [
        "auth"     => "oauth2/token/",
        "payments" => "v".self::API_VERSION."/payments/"
    ];

    // Static Variables

    /**
     * @property string
     * 
     */
    private static $apiVersion;

    /**
     * @property string
     * 
     */
    private static $authType;

    /**
     * @property string
     * 
     */
    private static $baseUrl;

    /**
     * @property string
     * 
     */
    private static $clientId;

    /**
     * @property string
     * 
     */
    private static $clientSecret;

    /**
     * @property string
     * 
     */
    private static $username;

    /**
     * @property string
     * 
     */
    private static $password;

    /**
     * @property string
     * 
     */
    private static $accessToken;

    /**
     * @property string
     * 
     */
    private static $refreshToken;

    /**
     * @property string
     * 
     */
    private static $scope;

    /**
     * @property Instamojo
     * 
     */
    private static $thisObj;

    /**
     * @return string
     * 
     */
    public function getAuthType()
    {
        return self::$authType;
    }

    /**
     * @return string
     * 
     */
    public function getClientId()
    {
        return self::$clientId;
    }

    /**
     * @return string
     * 
     */
    public function getClientSecret()
    {
        return self::$clientSecret;
    }

    /**
     * @return string
     * 
     */
    public function getAccessToken()
    {
        return self::$accessToken;
    }

    /**
     * @return string
     * 
     */
    public function getRefreshToken()
    {
        return self::$refreshToken;
    }

    /**
     * @return string
     * 
     */
    public function getBaseUrl()
    {
        return self::$baseUrl;
    }

    /**
     * @return string
     * 
     */
    public function getScope()
    {
        return self::$scope;
    }

    /**
     * @return string
     * 
     */
    public function __toString()
    {
        return sprintf("Instamojo {\nauth_type=%s, \nclient_id=%s, \nclient_secret=%s, \nbase_url=%s, \naccess_token=%s \n}", $this->getAuthType(), $this->getClientId(), $this->getClientSecret(), $this->getBaseUrl(), $this->getAccessToken());
    }

    /**
     * Initializes the Instamojo environment with default values 
     * and returns a singleton object of Instamojo class.
     * 
     * @param $type 
     * @param $params
     * @param $test
     * 
     * @return Instamojo
     */
    static function init($type='app', $params, $test=false)
    {
        if (self::$thisObj != null) {
            return self::$thisObj;
        } else {
            self::validateTypeParams($type, $params);

            self::$authType     = $type;
            self::$clientId     = $params['client_id'];
            self::$clientSecret = $params['client_secret'];
            self::$username     = isset($params['username']) ? $params['username'] : '';
            self::$password     = isset($params['password']) ? $params['password'] : '';
            self::$baseUrl      = Instamojo::PRODUCTION_BASE_URL;

            if ($test) {
                self::$baseUrl = Instamojo::TEST_BASE_URL;
            }

            self::$thisObj = new Instamojo();

            $auth_response = self::$thisObj->auth();

            self::$accessToken  = $auth_response['access_token'];
            self::$refreshToken = isset($auth_response['refresh_token']) ? $auth_response['refresh_token'] : '';
            self::$scope        = isset($auth_response['scope']) ? $auth_response['scope'] : '';

            return self::$thisObj;
        }
    }

    /**
     * Validates params for Instamojo initialization
     * 
     * @param $type
     * @param $params
     * 
     * @return null
     * 
     * @throws InvalidRequestException 
     * @throws MissingParameterException
     * 
     */
    private static function validateTypeParams($type, $params)
    {
        if (!in_array(strtolower($type), Instamojo::VALID_TYPES)) {
            throw new InvalidRequestException('Invalid init type');
        }

        if (empty($params['client_id'])) {
            throw new MissingParameterException('Client Id is missing');
        }

        if (empty($params['client_secret'])) {
            throw new MissingParameterException('Client Secret is missing');
        }

        if (strtolower($type) == 'user') {
            if (empty($params['username'])) {
                throw new MissingParameterException('Username is missing');
            }

            if (empty($params['password'])) {
                throw new MissingParameterException('Password is missing');
            }
        }
    }

    public function withBaseUrl($baseUrl) 
    {
        self::$baseUrl = $baseUrl;
        return $this;
    }

    /**
     * Build headers for api request
     * 
     * @return array
     */
    private function build_headers($auth=false) 
    {
        $headers = [];

        if(!$auth && empty(Instamojo::$accessToken)) {
            throw new InvalidRequestException('Access token not available');
        }

        $headers[] = "Authorization: Bearer ".Instamojo::$accessToken;

        return $headers;        
    }

    /**
     * Requests api data
     * 
     * @param $method
     * @param $path
     * @param $data
     * 
     * @return array
     * 
     */
    private function request_api_data($method, $path, $data=[])
    {
        $headers = $this->build_headers(Instamojo::URIS['auth'] == $path);

        $url = self::$baseUrl . '/' . $path;

        return api_request($method, $url, $data, $headers);
    }

    /**
     * Make auth request
     * 
     * @return array
     * 
     * @throws Exception
     * 
     */
    public function auth() {
        $data = [
            'client_id'     => self::$clientId,
            'client_secret' => self::$clientSecret,
        ];

        switch(self::$authType) {
            case 'app':
                $data['grant_type'] = 'client_credentials';
            break;

            case 'user':
                $data['grant_type'] = 'password';
                $data['username'] = self::$username;
                $data['password'] = self::$password;
            break;

            case 'refresh':
                $data['grant_type']    = 'refresh_token';
                $data['refresh_token'] = self::$refreshToken;
            break;
        };

        $response = $this->request_api_data('POST', Instamojo::URIS['auth'], $data);

        if (!isset($response['access_token'])) {
            throw new AuthenticationException();
        }

        return $response;
    }

    /**
     * Get payments
     * 
     * @return array
     * 
     */
    public function getPayments($limit=null, $page=null) {
        $data = [];

        if (!is_null($limit)) {
            $data['limit'] = $limit;
        }

        if (!is_null($page)) {
            $data['page'] = $page;
        }

        $response = $this->request_api_data('GET', Instamojo::URIS['payments'], $data);

        return $response['payments'];
    }

    /**
     * Get details of payment
     * 
     * @return object
     * 
     */
    public function getPaymentDetails($id) {
        return $this->request_api_data('GET', Instamojo::URIS['payments'] . $id . '/');
    }
}