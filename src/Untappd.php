<?php namespace Untappd;

use GuzzleHttp\Client;

class Untappd{

    // private properties
    private $client_id = "";
    private $client_secret = "";
    private $redirect_url = "";
    private $access_token = "";
    protected $error = "";

    // untappd API URL's
    public $apiBase = "https://api.untappd.com/v4/";
    public $authenticateURL = "https://untappd.com/oauth/authenticate/";
    public $authorizeURL = "https://untappd.com/oauth/authorize/";

    /**
     * @param array $config
     */
    public function __construct($config = [])
    {
        $this->client_id = (isset($config['client_id'])) ?	$config['client_id'] : '';
        $this->client_secret = (isset($config['client_secret'])) ? $config['client_secret'] : '';
        $this->redirect_url = (isset($config['redirect_url'])) ? $config['redirect_url'] : '';
    }

    /**
     * Build URL for authentication
     * @return string url
     */
    public function getAuthenticateUrl()
    {
        return $this->authenticateURL.
        "?client_id=".$this->client_id.
        "&response_type=code".
        "&redirect_url=".$this->redirect_url;
    }

    /**
     * Build URL for authorisation
     * @param string $code
     * @return string URL
     */
    public function getAuthoriseUrl($code)
    {
        return $this->authorizeURL .
        "?client_id=".$this->client_id.
        "&client_secret=".$this->client_secret.
        "&response_type=code".
        "&redirect_url=".$this->redirect_url.
        "&code=".$code;
    }

    /**
     * Set access token
     * @param string $token
     */
    private function setAccessToken($token)
    {
        $this->access_token = $token;
    }

    /**
     * Set error message
     * @param string $error
     */
    private function setError($error)
    {
        $this->error = $error;
    }

    /**
     * Get error
     * @return string error
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * Authorise at Untappd
     * @param string $url
     * @return bool
     */
    public function authorise($url)
    {
        $responses = $this->request($url);
        if($responses['meta']['http_code'] == '200')
        {
            $token = $responses['response']['access_token'];
            $this->setAccessToken($token);
            return true;
        }
        else
        {
            $this->setError($responses['meta']['error_detail']);
            return false;
        }
    }

    /**
     * Utility method to retrieve data from Untappd
     * @param string $method
     * @param array $params
     * @return mixed $responses
     */
    public function query($method, $params = [])
    {
        $url = $this->apiBase.$method;

        // merge passed params with existing params
        $params = array_merge($params,["access_token" => $this->access_token]);
        $responses = $this->request($url,$params);
        return $responses;
    }


    /**
     * Wrapper around Guzzlehttp\Client
     * @param string $url
     * @param array $params
     * @return mixed $responses
     */
    private function request($url, $params = [])
    {
        $client = new Client();
        if ( count($params) > 0 )
        {
            $client->setDefaultOption('query', $params);
        }
        $response = $client->get($url);
        $responses = json_decode($response->getBody(),true);
        return $responses;
    }
}
