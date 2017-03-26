<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of api
 *
 * @author Administrator
 */
class wxapi {

    /**
     * @ignore
     */
    public $client_id;

    /**
     * @ignore
     */
    public $client_secret;

    /**
     * @ignore
     */
    public $access_token;

    /**
     * @ignore
     */
    public $refresh_token;

    /**
     * Set the useragnet.
     *
     * @ignore
     */
    public $useragent = 'Sae T OAuth2 v0.1';

    /**
     * Contains the last HTTP headers returned.
     *
     * @ignore
     */
    public $http_info;

    /**
     * Set connect timeout.
     *
     * @ignore
     */
    public $connecttimeout = 30;
/**
	 * print the debug info
	 *
	 * @ignore
	 */
	public $debug = FALSE;
    /**
     * Set timeout default.
     *
     * @ignore
     */
    public $timeout = 30;

    /**
     * Verify SSL Cert.
     *
     * @ignore
     */
    public $ssl_verifypeer = FALSE;
/**
	 * Contains the last HTTP status code returned. 
	 *
	 * @ignore
	 */
	public $http_code;
	/**
	 * Contains the last API call.
	 *
	 * @ignore
	 */
	public $url;
    //construct
    public function __construct() {
        
    }

    /**
     * Make an HTTP request
     * GET请求
     *
     * @return string API results
     * @ignore
     */
    function http($url, $method, $postfields = NULL, $headers = array()) {
        $this->http_info = array();
        $ci = curl_init();
        /* Curl settings */
//        curl_setopt($ci, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
//        curl_setopt($ci, CURLOPT_USERAGENT, $this->useragent);
//        curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, $this->connecttimeout);
//        curl_setopt($ci, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ci, CURLOPT_RETURNTRANSFER, TRUE);
//        curl_setopt($ci, CURLOPT_ENCODING, "");
//        curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, $this->ssl_verifypeer);
//        curl_setopt($ci, CURLOPT_HEADERFUNCTION, array($this, 'getHeader'));
        curl_setopt($ci, CURLOPT_HEADER, FALSE);
//
//        if (isset($this->access_token) && $this->access_token)
//            $headers[] = "Authorization: OAuth2 " . $this->access_token;
//
//        $headers[] = "API-RemoteIP: " . $_SERVER['REMOTE_ADDR'];
        curl_setopt($ci, CURLOPT_URL, $url);
        curl_setopt($ci, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ci, CURLINFO_HEADER_OUT, TRUE);
        $response = curl_exec($ci);
//        print_r($response);exit;
        $this->http_code = curl_getinfo($ci, CURLINFO_HTTP_CODE);
        $this->http_info = array_merge($this->http_info, curl_getinfo($ci));
        $this->url = $url;

        if ($this->debug) {
            echo "=====post data======\r\n";
            var_dump($postfields);

            echo '=====info=====' . "\r\n";
            print_r(curl_getinfo($ci));

            echo '=====$response=====' . "\r\n";
            print_r($response);
        }
        curl_close($ci);
        return $response;
    }

}
