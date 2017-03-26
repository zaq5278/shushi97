<?php
/**
 * Created by PhpStorm.
 * User: caohailiang
 * Date: 2016/9/9
 * Time: 11:28
 */

namespace Plugin\Cloopen;

include_once (dirname(__FILE__)."/CCPRestSDK.php");
use Plugin\Core\QSTBaseLogger;
use CCPRestSDK as REST;

class IM
{
    /**
     * @var array
     */
    private $_config;
    /**
     * @var REST
     */
    private $_sdk;

    function __construct($cfg = null)
    {
        if(isset($cfg) && is_array($cfg)){
            $this->_config = $cfg;
        }else{
            $vCfg = include dirname(__FILE__)."/config.php";
            $this->_config = $vCfg['ccp'];
        }
    }

    public function init()
    {
        if(!isset($this->_config)){
            QSTBaseLogger::getInstance()->log("init ccp IM interface no invalid config", \Phalcon\Logger::ERROR);
            return false;
        }
        $this->_sdk = new REST($this->_config['serverIP'], $this->_config['serverPort'], $this->_config['softVersion'],
            LOG_DIR.'/ccp_rest.log');
        $this->_sdk->setAccount($this->_config['accountSid'], $this->_config['accountToken']);
        $this->_sdk->setAppId($this->_config['appId']);
        return $this;
    }

    /**
     * @param $sender string
     * @param $receivers array
     * @param $content string
     * @param $extension string
     * @return bool|mixed|\SimpleXMLElement|\stdClass
     */
    public function pushNotice($sender, $receivers, $content, $extension)
    {
        return $this->_sdk->PushMsg("1", "1", $sender, $receivers, $content, $extension);
    }
}