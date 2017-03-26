<?php

/**
 * Created by PhpStorm.
 * User: MiaoGang
 * Date: 2016/8/31
 * Time: 21:40
 */
namespace Plugin\Weixin;

class Weixin
{
    private $appId;
    private $appSecret;
    private $jssdk;
    private $wxpay;
    private $wxchat;

    // public $jssdk;
    public function __construct($appId, $appSecret) {
        $this->appId = $appId;
        $this->appSecret = $appSecret;
    }

    public function wxchat() {
        $options = array(
            'appid'=>$this->appId, 
            'appsecret'=>$this->appSecret
        );
        require_once __DIR__ . "/wxsdk/wechat.class.php";
        $this->wxchat = new \Wechat($options);
        return $this->wxchat;
    }

    public function jssdk() {
        require_once(APP_DIR."/plugin/weixin/wxsdk/jssdk.php");
        if (empty($this->jssdk)) {
            $this->jssdk = new \JSSDK($this->appId, $this->appSecret);
        }
        return $this->jssdk;
    }
    
    public function wxpay() {
        require_once(APP_DIR."/plugin/weixin/wxpay/wxpay.php");
        if (empty($this->wxpay)) {
            $this->wxpay = new \WXPAY();
        }
        return $this->wxpay;
    }
}