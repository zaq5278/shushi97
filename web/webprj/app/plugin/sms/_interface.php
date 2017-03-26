<?php //提交短信

abstract class SMSInterface {
	// 发送手机号码
	protected $phone;
	public function __construct($phone) {
		$this->phone = $phone;
	}

	// todo send sms
	abstract public function send();
}

?>