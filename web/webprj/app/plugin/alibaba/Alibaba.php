<?php

/**
 * Created by PhpStorm.
 * User:
 * Date:
 * Time:
 */
namespace Plugin\Alibaba;

class Alibaba
{
    private $appId;
    private $appSecret;
    private $alipay;

    /**
     * Alibaba constructor.
     * @param $appId
     * @param $appSecret
     */
    public function __construct($appId, $appSecret) {
        $this->appId = $appId;
        $this->appSecret = $appSecret;
    }

    public function alipay() {
        require_once(APP_DIR."/plugin/alibaba/alipay/alipay.php");
        if (empty($this->alipay)) {
            $this->alipay = new \ALIPAY();
        }
        return $this->alipay;
    }


}