<?php //提交短信

require_once(dirname(__FILE__).DIRECTORY_SEPARATOR."../_interface.php");
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR."lib".DIRECTORY_SEPARATOR."ChuanglanSmsApi.php");

class SMSClass extends SMSInterface {
	// appkey
	private $account;
        private $passwd;
        
	// 发送短信内容
	private $content;
	public function __construct($param = array()) {
		parent::__construct($param['phone']);
		$this->content = $param['content'];
		$this->account = $param['account'];
		$this->passwd = $param['passwd'];
                
	}
	public function send() {
            $clapi  = new ChuanglanSmsApi(array('account'=>$this->account, 'passwd'=>$this->passwd));
            $result = $clapi->sendSMS($this->phone, $this->content);
            $result = $clapi->execResult($result);
            if(isset($result[1]) && $result[1]==0){
                   return true;
            }else{
                    return false ;
            }
	}
    }

?>