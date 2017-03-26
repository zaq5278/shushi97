<?php //提交短信

require_once(dirname(__FILE__).DIRECTORY_SEPARATOR."../_interface.php");
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR."lib".DIRECTORY_SEPARATOR."CCPRestSDK.php");

class SMSClass extends SMSInterface {
	// 账户信息
	private $accountSid;
	private $accountToken;
	// 应用id
	private $appId;
	/* 
	 * 生成环境: app.cloopen.com
	 * 测试环境: sandboxapp.cloopen.com
	 */
	private $serverIP = 'sandboxapp.cloopen.com';
	// 固定端口号
	private $serverPort = '8883';
	// 版本号
	private $softVersion = '2013-12-26';
	// 模板替换内容
	private $datas = array();
	// 模板id
	private $tempId;

	public function __construct($param = array()) {
		parent::__construct($param['phone']);
		// 
		$this->accountSid = $param["accountSid"];
		$this->accountToken = $param["accountToken"];
		$this->appId = $param["appId"];
		$this->tempId = $param['tempId'];
		$this->datas = $param['datas'];
		if ($param['release']) {
			$this->serverIP = "app.cloopen.com";
		}
	}
	
	public function send() {
		 $rest = new REST($this->serverIP, $this->serverPort, $this->softVersion);
		 $rest->setAccount($this->accountSid, $this->accountToken);
		 $rest->setAppId($this->appId);
		
		 // 发送模板短信
		 $result = $rest->sendTemplateSMS($this->phone, $this->datas, $this->tempId);
		 if ($result != NULL && $result->statusCode == 0) {
			 return true;
		 }

		 return false;
	}
}

?>