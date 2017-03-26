<?php //提交短信

require_once(dirname(__FILE__).DIRECTORY_SEPARATOR."../_interface.php");
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR."lib".DIRECTORY_SEPARATOR."Sms.php");

class SMSClass extends SMSInterface {
	// appkey
	private $appkey;
	// 发送短信内容
	private $content;

	public function __construct($param = array()) {
		parent::__construct($param['phone']);
		$this->content = $param['content'];
		$this->appkey = $param['appkey'];
	}
	
	public function send() {
		$smsSdk = new Sms(array('api_key' => $this->appkey, 'use_ssl' => FALSE));
		$result = $smsSdk->send($this->phone, $this->content);
		if ($result && isset($res['error']) && $res['error'] == 0) {
			return true;	
		}

		return false;
	}
}

?>