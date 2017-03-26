<?php

namespace App\Foreground\Controllers;
use Plugin\Core\QSTBaseController;
use Plugin\Core\QSTBaseModel;
use Phalcon\Mvc\View;


/**
 * Display the default index page.
 */
class ApiController extends QSTBaseController{
    public function indexAction(){
        //img图片上传
        $this->addLibJs('libs/js3party/ajaxfileupload/ajaxfileupload.js');
        $this->addLibJs('libs/jsapi/service.js');
        $this->addLibJs('libs/jsapi/test.js');
        $this->wxjssdkload();
        
    }

    /*
     *功能描述:图片上传
     * 包括 plugin/upload
           libs/ajaxfileupload/ajaxfileupload.js
           libs/imgupload.js
     输入参数 
        servicecode      自定义
        type             自定义
     输出:
        status           0.成功，其他失败
        url[]            图片地址数组，相对URL地址，如:/qstcom/WFWPHA/webprj/uploadfile/2016/08/17/d57b7b945047ad79bbba13aabc98c02ea71fe74a.png
     */
    function imguploadAction(){
        $out_data = array();
        if ($this->request->isPost()) {
            $service = $this->request->getPost('service');
            $this->log('imguploadAction:' . $service);
        }
        // Check if the user has uploaded files
        if ($this->request->hasFiles()) {
            foreach ($this->request->getUploadedFiles() as $file) {
                $fileUrl = $this->upload->upload($file);
                $out_data['url'][] = $fileUrl;
            }
        }
        return $this->responseJson($out_data,0);
    }
    
     /*
     输入参数 
        {"images":[image1b64,image2b64]} ,如：{"images":["data:image/jpeg;base64,iVBORw0KGgoAAAANSU","data:image/jpeg;base64,iVBORw0KGgoAAAANSU"]}
     输出:
        status           0.成功，其他失败
        url[]            图片地址数组，相对URL地址，如:/qstcom/WFWPHA/webprj/uploadfile/2016/08/17/d57b7b945047ad79bbba13aabc98c02ea71fe74a.png
     */
    function imgb64uploadAction(){
        $out_data = array();
       // $this->log("imgb64uploadAction------------");
        $content = file_get_contents('php://input');
        //$this->log($content);
        $files= json_decode($content,true);
        //$this->log("imgb64uploadAction---------------------------------------1");
        //$this->log($files);
        
        foreach ($files['images'] as $file) {
            $fileUrl = $this->upload->bs64upload($file);
            $out_data['url'][] = $fileUrl;
        }
        return $this->responseJson($out_data,0);
    }
   
  /* 用户登录注册相关接口*/
   /*
     依赖表 qst_user_verify qst_user  和存储过程 p_make_smscode
     功能描述:获取短信验证码
     入参: 
        account         账号(手机号码/邮箱账号)
    出参: 
        code            验证码
        deadminutes     剩余的过期时间
        leftsecond      禁止重发，查出剩余时间
        status          0.成功，1002.禁止重发，1033.账号已注册,查出剩余时间，1007.参数错误 ,1021.发送短信失败
     */
    public function sendsmsAction(){
       $body = $this->request->getRawBody();
       $body = json_decode($body,true);
       
       $resp =$this->sms->sms_general($body);
       return $this->responseJson($resp,$resp['status']);
    }
   
    /*
    依赖表 qst_user_verify 表 和 p_check_code 存储过程
    功能描述: 校验用户输入验证码的合法性
    入参: account   注册账号:手机号码或邮箱
          code      用户输入的验证码
    出参:
          status        0.成功,1003.验证码不对,1007.参数不对,9999.数据库异常
     */
    
    public function checksmscodeAction(){
       $body = $this->request->getRawBody();
       $body = json_decode($body,true);
       $resp =$this->sms->checksmscode($body);
       return $this->responseJson($resp,$resp['status']);
      
    }
    
    /*     
    功能描述:用户注册
          包括手机号码注册;邮箱账号注册;第3方登录必须绑定,如果是第3方登录,必须填写手机号或邮箱账号时，需要做一次第3方绑定操作;
     入参:
            account       手机号
            passwd        密码
            type          注册账号类型:1.邮箱,2.手机,3.QQ,4.微信,5.微博
            code          验证码
      出参: 
            userid   用户的id
            account  注册账号
            status   0.成功, 1007.参数错误,1032.邮箱已存在,1033.账号已注册,1034.手机号已被绑定,1036.邮箱已注册但未激活，9999.数据异常
    */
    function userRegistrationAction(){
        $body= $this->request->getRawBody();
        $body = json_decode($body,true);
        $resp ="";
        $resp =$this->execsql($body, 'p_account_isexist');
        if($resp['status'] !=0){
            return $this->responseJson($resp,$resp['status']);
        }
        $resp =$this->execsql($body, 'p_check_code');
        if($resp['status'] != 0){
            return $this->responseJson($resp,$resp['status']);
        }
        //执行用户注册
        $resp =$this->execsql($body, 'p_user_registration');
        if($resp['status'] != 0){
            return $this->responseJson($resp,$resp['status']);
        }
        $body['userid'] = $resp['out_data']['userid'];
        
        if((!empty($body['sex']) && $body['sex']!="") || (!empty($body['email']) && $body['email']!="")){
            $tmp['userid']=$body['userid'];
            $tmp['sex']=$body['sex'];
            $tmp['email']=$body['email'];
            $resp =$this->execsql($tmp, 'p_userinfo_modify');
        }
        
        //一般业务场景都是注册后立即登录,如果不需立即登录，下面登录代码注释即可
        $body['token'] = md5(time().rand(10000, 99999).rand(10000, 99999));
        $resp =$this->execsql($body, 'p_user_login');
        $ret['status'] =$resp['status'];
        if($resp['status']==0){
           $this->session->set('userid',$resp['out_data']['userid']);
           $this->session->set('account',$resp['out_data']['account']);
           $this->session->set('token',$resp['out_data']['token']);
           $this->log($resp); 
           $ret['userid'] =$resp['out_data']['userid'];
           $ret['account'] =$resp['out_data']['account'];
           $ret['token'] =$resp['out_data']['token'];
        }
        
        return $this->responseJson($ret,$ret['status']);
    }
    /*
     功能描述: 用户账号登录(手机号码或邮箱）
     入参: 
           account       登录账号(系统账号或第3方openid)
           passwd        密码
           token         登录令牌
           type	  	 1.邮箱账号登录,2.手机账号登录

     出参: 
           userid        用户的id
           token         登录令牌
           isbind        第3方登录是否已绑定：0.未绑定, 1.绑定
           status        0.成功,1001.账号不存在,1061.账号失效,1008.密码错误,9999.数据库异常
     */
     function userLoginAction(){
        $body = $this->request->getRawBody();
        $body = json_decode($body,true);
        $random = new \Phalcon\Security\Random();
        $body['token'] = $random->uuid();
      
        $this->session->set("userid","");
        $this->session->set("account","");
        $this->session->set("token","");
        $this->session->set("isbind","");
        $resp =$this->execsql($body, "p_user_login");
        if($resp['status']==0){
            $this->session->set("userid",$resp['out_data']['userid']);
            $this->session->set("account",$resp['out_data']['account']);
            $this->session->set("token",$resp['out_data']['token']);
            $this->session->set("isbind",$resp['out_data']['isbind']);
        }
        return $this->responseJson($resp,$resp['status']);
     }
    
   function user3PLoginAction(){
       $this->log('user3PLogin');
       require_once(API_DIR."/login/apiuseropt.php");
       $body['type'] = $this->request->getQuery('logintype');       //1登录  2绑定
       $body['thirdtype'] = $this->request->getQuery('thirdtype');  //所属平台：3QQ  4微信  5微博
       $this->log($body);
       $url =user3PLogin($body);
       return $this->response->redirect($url);
   }

   /*
    功能描述: 忘记密码，进行密码重置
    入参: 
        account       手机号或邮箱账号
        passwd        密码
        code          验证码
    出参:
        status        0 成功 ,1001.账号不存在,1007.参数错误,9999.其他表示异常
    */
   function passwdresetAction(){
        $body = $this->request->getRawBody();
        $body = json_decode($body,true);
        $resp =$this->execsql($body, 'p_check_code');
        if($resp['status'] != 0){
            return $this->responseJson($resp,$resp['status']);
        }
       $resp =$this->execsql($body, 'p_passwd_reset');
       return $this->responseJson($resp,$resp['status']);
   }
    /*
    功能描述:    密码修改
    入参: 
     userid       手机号或邮箱账号
     passwd       新密码
     oldpasswd    老密码
    出参:
     status        0 成功 ,1001.账号不存在,1007.参数错误,1008.密码错误,其他表示异常
    */
   function passwdmodifyAction(){
        $body = $this->request->getRawBody();
        $body = json_decode($body,true);
        $resp = $this->execsql($body, 'p_passwd_modify');
        return $this->responseJson($resp,$resp['status']);
   }
   /*
  功能描述: 用户信息修改
  入参: 
    userid  
    sex         性别
    headurl     头像url
    tel         手机
    nick        昵称
    name        真实姓名
    IDcard      身份证号
    IDurl       身份证图片url   
  出参:
    vo_res        0 成功 ,1001.账号不存在,1007.参数错误,其他表示异常
 */
   function userinfo_modifyAction(){
       $body = $this->request->getRawBody();
       $body = json_decode($body,true);
       $resp = $this->execsql($body, 'p_userinfo_modify');
       return $this->responseJson($resp,$resp['status']);
   }
   
      /*功能描述: 用户退出*/
   function logoutAction(){
        $this->log('logout');
        $this->session->destroy();
        $ret = array('status'=>0);
        return $this->responseJson($ret,$ret['status']);
    }
    /*第3方账号绑定*/
    function bind3accountAction(){
       $this->log('bind3account');
       //require_once(API_DIR."/login/apiuseropt.php");
       $body = $this->request->getRawBody();
       $this->log($body);
       $body = json_decode($body,true);
       $body['openid']=$this->session->get('openid');
       $this->log($body);
       $ret = $this->execsql($body, 'p_bind3account');
       $this->log('-------111'.$ret);
       if($ret['status'] ==0) {
            $this->session->set("isbind",1);
       }
       else {
            $this->session->set("isbind",0);
       }
       #号码绑定可以带扩展参数，扩展参数根据业务功能来定
       $ext = $body['ext'];
       return $this->responseJson($ret,$ret['status']);
    }
    /*产生图片验证码*/
    function getpicCodeAction(){
       $this->view->disable();
       require_once(API_DIR . "/login/apiuseropt.php");
       $this->log('getpicCode');
       $body['fontSize'] = $this->request->getQuery('fontSize');
       $body['length'] = $this->request->getQuery('length');  
       $body['useNoise'] = $this->request->getQuery('useNoise');
       $body['useCurve'] = $this->request->getQuery('useCurve');
       $this->log($body);
       $ret = getpicCode($body);
       echo $ret;
   }
   /*图片验证码验证*/
   function checkpiccodeAction() {
        require_once(API_DIR . "/login/apiuseropt.php");
        $this->log('checkpiccode');
        $body['fontSize'] = $this->request->getQuery('fontSize');
        $body['length'] = $this->request->getQuery('length');
        $body['useNoise'] = $this->request->getQuery('useNoise');
        $body['useCurve'] = $this->request->getQuery('useCurve');
        $body['code'] = $this->request->getQuery('code');
        $this->log($body);
        $ret =checkpiccode($body);
        return $ret;
    }

    /* 微信支付流程
     WEB/APP             应用平台                        微信平台
      订单生成
      |------ordercode----->|
      |<--------------------|
     
      APP/微信发起支付
      |-------wxjspay------>|----------------------------->|
      |<--------prepare_id--|<---------prepare_id----------|
      |-----------JS或APP发起------------------------------>|
      |<-----------------------支付结果 --------------------|
                            |<---------支付结果通知---------|
    
    /*
        URL：http://[地址]/api/ordercode
     * 功能描述:生成订单编号
     * 逻辑：先生成订单，产生订单编号，再根据订单号添加具体商品信息
     * 依赖 : 依赖 qst_pay_ordercode 和 p_pay_ordercode
     生成订单号
        无
      出参: 
        ordercode      订单流水号
        uuid           订单流水号的唯一id
        status         0 成功， 其他表示失败
     */
    function ordercodeAction(){
        $body =array();
        $resp =$this->execsql($body, 'p_pay_ordercode');
        $ret['ordercode']=$resp['out_data']['ordercode'];
        $ret['uuid']=$resp['out_data']['uuid'];
        $ret['status'] =$resp['status'];
        return $this->responseJson($ret,$ret['status']);
    }
     
    /*微信公众号支付*/
     /*
        URL：http://[地址]/api/wxjspay
        输入参数：
            ordercode  订单编号
            fee        支付金额 以元为单位
            body       设置商品或支付单简要描述
            attach     设置附加数据，在查询API和支付通知中原样返回，该字段主要用于商户携带订单的自定义数据
            goodstag   设置商品标记，代金券或立减优惠功能的参数，说明详见代金券或立减优惠
       输出参数:
            parameters[
            appid = wx94550dbd11ee13d2;
            noncestr = k1ks1s4ljjncymrzy9eh6tskdesdma96;
            package = "Sign=WXPay";
            partnerid = 1417011302;
            paysign = B5C928A15FDA307766DF065283FAB3E6;
            prepayid = wx201612051426004418b3b3bc0576288646;
            timestamp = 1480919160;
            ]

      */
     public function wxjspayAction(){
        $package = $this->request->getRawBody();
        $package = json_decode($package,true);
        $this->log('wxjspay 输入参数:');
        $this->log($package);
        $ordercode=$package['ordercode'];
        $fee = $package['fee'];
        $attach =$package['attach'];
        $goodstag = $package['goodstag'];
        $body = $package['body'];
        if(empty($ordercode)||empty($fee)){
            return $this->responseJson($package,1007);
        }

        // 微信支付测试代码，不依赖微信的任何JS
        $resp['status']=0;
        $resp['desp'] ='微信支付，这三条代码需注释';
        return $this->responseJson($resp,$resp['status']);
        
        $body =$ordercode;
        $attach = $ordercode;
        $notiyurl = "http://" . $this->request->getHttpHost();
        $notiyurl .= $this->url->get('api/wxjspaynotify');
        $openid = $this->session->get('openid');
        $resp =$this->weixin->wxpay()->wxpay($ordercode,$fee,$notiyurl,$openid,$body,$attach,$goodstag);
        $this->log($resp);
        return $this->responseJson($resp,$resp['status']);
     }
     /*
        功能描述:订单状态修改
        URL：http://[地址]/api/paystatemodify
        入参:
            seqno       支付流水号
            ordercode   订单号
            vstate      1.订单已支付成功,2.订单支付失败
            errdsp      订单支付错误信息
        出参: 
            status    0.成功,1007.参数错误,9999.数据异常
      */
     public function paystatemodifyAction(){ 
         $this->log('paystatemodify');
         $body = $this->request->getRawBody();
         $this->log($body);
         $body = json_decode($body,true);
         $ordercode=$body['ordercode'];
         $vstate=$body['vstate'];
         if(empty($ordercode)||empty($vstate)){
             return $this->responseJson($body,1007);
         }
         $resp =$this->execsql($body, 'p_pay_statemodify');
         return $this->responseJson($body,$resp['status']);
     }
     /*微信公众号支付通知
      此接口在响应通知消息时需要在php.ini 中设置如下参数
      always_populate_raw_post_data =-1
    */

     public function wxjspaynotifyAction(){ 
        $this->log('wxjspaynotify');
        $xml = $this->request->getRawBody();
        $this->log($xml);

        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $datas = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);		
        //$this->log($datas);
        $resp =$this->execsql($datas, 'p_pay_wxnotify_add');
        $this->log($resp);
        $OK = 'OK';
        $content="<xml><return_code><![CDATA[".$datas['result_code']."]]></return_code><return_msg><![CDATA[".$OK."]]></return_msg></xml>";
        return $this->response->setContent($content)->setContentType("text/xml");
     }
     
     /*微信提现*/
       public function wxrefundAction(){
           
        $body = $this->request->getRawBody();
        $body = json_decode($body,true);
        $this->log('wxjspay 输入参数:');
        $this->log($body);
        $resp = $this->weixin->wxpay()->wx_refund($body['ordercode'],$body['totalfee'],$body['refundfee']);
        $this->log($resp);
        
       }
   /* 微信授权后，获取用户信息*/
   /*微信授权在BaseController里已实现*/
    public function wxloginAction(){
        $code = $this->request->getQuery('code');
        $state =$this->request->getQuery('state');
	$this->log("wxloginnAction code:".$code.' state:'.$state);
	$resp = $this->weixin->jssdk()->wxLogin($code);

        $this->log("wxloginnAction resp:");
        $this->log($resp);	

        $this->session->set("openid",$resp->openid);
        $this->session->set("nickname",$resp->nickname);
        $this->session->set("sex",$resp->sex);
        $this->session->set("city",$resp->city);
        $this->session->set("province",$resp->province);
        $this->session->set("country",$resp->country);
        $this->session->set("headimgurl",$resp->headimgurl);
        
        $openid =$resp->openid;
        $random = new \Phalcon\Security\Random();
        $loginparam['account']=$openid;
        $loginparam['passwd']="";
        $loginparam['token']=$random->uuid();
        $loginparam['type']="4";
        $loginparam['nick']=$resp->nickname;
        $loginparam['sex']=$resp->sex;
        $loginparam['city']=$resp->city;
        $loginparam['province']=$resp->province;
        $loginparam['country']=$resp->country;
        $loginparam['headurl']=$resp->headimgurl;
        $resp = $this->execsql($loginparam, 'p_user_login');
        if($resp['status']==0){
            $this->session->set("userid",$resp['out_data']['userid']);
            $this->session->set("account",$resp['out_data']['account']);
            $this->session->set("token",$resp['out_data']['token']);
            $this->session->set("isbind",$resp['out_data']['isbind']);
            $this->session->set("thirdtype", $loginparam['type']);
            $loginparam['userid']=$resp['out_data']['userid'];

           //  $this->log("wxloginAction-----------------");
            //$this->log($loginparam['headurl']);

            $this->execsql($loginparam, 'p_userinfo_add');
            $this->session->set("sex", $loginparam['sex']);
            $this->session->set("nick", $loginparam['nick']);
            $this->session->set("headurl", $loginparam['headurl']);
            
            $body['userid']=$resp['out_data']['userid'];
            
        }
        $url = '../index';
        return $this->response->redirect( $url );
    }
    
    public function getsessionparamAction(){
        $body = $this->request->getRawBody();
        $body = json_decode($body,true);
        $this->log('getsessionparam 输入参数:');
        $this->log($body);
        $resp['status'] = 0;
        $pieces = explode(",", $body['params']); 
        foreach ($pieces as $value){
            $resp[$value]=$this->session->get($value);
        }
        return $this->responseJson($resp,$resp['status']);
    }


    /**lxd 1102
        功能描述: 阿里扫码支付
        入参:
            orderno       订单号
            amount        价格，单位：元
        出参:
            status        0 成功
     */
    public function alismAction(){
        $data = $this->getJsonArrayBody(array("orderno","amount"));
        $order_no = !empty($data['orderno'])?$data['orderno']:"test1111";
        $amount = isset($data['amount'])?$data['amount']:'0.01';
        $notify_url = _ROOT_URL_._F_BASE_URL_."index/cbnotify";
        $alipay = $this->alibaba->alipay();
        $ret = $alipay->joinalipayinfo($order_no,$amount,$notify_url);
        return $this->responseJson($ret,0);
    }

    /**lxd 1102
       功能描述: 阿里支付pc端
       入参:
           orderno       订单号
           amount        价格，单位：元
       出参:
           status        0 成功
    */
    public function alitzAction(){
        $order_no = isset($_GET['orderno'])?$_GET['orderno']:"test1111";
        $amount = isset($_GET['amount'])?$_GET['amount']:'0.01';
        $notify_url = _ROOT_URL_._F_BASE_URL_."index/cbnotify";
        $return_url = _ROOT_URL_._F_BASE_URL_."index/cbreturn";
        $ali= $this->alibaba->alipay();
        $pay_html = $ali->render(1,$order_no,$amount,$notify_url,$return_url);
        print_r($pay_html);exit;
    }

    /**lxd 1102
        功能描述: 阿里pc 网银端
        此功能被支付宝下线，直接跳转到快捷支付
        入参:
            orderno       订单号
            amount        价格，单位：元
        出参:
            status        0 成功
     */
    public function alibankAction(){
        $order_no = isset($_GET['orderno'])?$_GET['orderno']:"test1111";
        $amount = isset($_GET['amount'])?$_GET['amount']:'0.01';
        $notify_url = _ROOT_URL_._F_BASE_URL_."index/cbnotify";
        $return_url = _ROOT_URL_._F_BASE_URL_."index/cbreturn";
        $ali= $this->alibaba->alipay();
        $pay_html = $ali->render(2,$order_no,$amount,$notify_url,$return_url);
        print_r($pay_html);exit;
    }

    /**lxd 1102
        功能描述: 阿里同步回调，在阿里发送请求时，填写本地址，支付成功后，支付宝会携带参数跳转到本地址
        主要参数:
            user_trade_no：订单号
            trade_no：流水号
     */
    public function cbreturnAction(){
        $ali= $this->alibaba->alipay();
        $info = $ali->CBreturn();
        tracelog("cbreturnAction,body:".json_encode($info));
        if($info['status']==0){
            echo "success";//给支付宝网关回消息，告诉已经收到通知
            //业务逻辑，更新数据库等操作
        }
    }


    /**lxd 1102
        功能描述: 阿里异步回调，在阿里发送请求时，填写本地址，支付成功后，支付宝会给本接口发送请求
        主要参数:
            user_trade_no：订单号
            trade_no：流水号
     */
    public function cbnotifyAction(){
        $ali= $this->alibaba->alipay();
        $info = $ali->CBnotify();
        tracelog("cbnotifyAction，body:" . json_encode($info));
        //根据$info下面的元素，更改数据库
        // body:{"user_trade_no":"orderno00001112","trade_no":"2016110221001004640220461541","alitype":"notify_url","channel":"alipay","status":0}
        if($info['status']==0){
            echo "success";//给支付宝网关回消息，告诉已经收到通知
            //业务逻辑，更新数据库等操作
        }
    }


    /**lxd 1102
        功能描述: 测试阿里手机端web支付
        入参:
            orderno       订单号
            amount        价格，单位：元
        出参:
            status        0 成功
     */
    public function aliwebAction(){
        $order_no = isset($_GET['orderno'])?$_GET['orderno']:"test1111";
        $amount = isset($_GET['amount'])?$_GET['amount']:'0.01';
        $notify_url = _ROOT_URL_._F_BASE_URL_."index/cbnotify";
        $return_url = _ROOT_URL_._F_BASE_URL_."index/cbreturn";
        $ali= $this->alibaba->alipay();
        $pay_html = $ali->render(3,$order_no,$amount,$notify_url,$return_url);
        print_r($pay_html);exit;
    }


    /**lxd 1102
        功能描述: 测试阿里手机端APP支付
        入参:
            orderno       订单号
            amount        价格，单位：元
        出参:
            status        0 成功
     */
    public function aliappAction(){
        $data = $this->getJsonArrayBody(array("ordercode"));
        $order_no = isset($data['ordercode'])?$data['ordercode']:'orderno123456';
        $amount = isset($data['fee'])?$data['fee']:'0.01';
        $notify_url = _ROOT_URL_._F_BASE_URL_."api/cbappnotify";
        $ali= $this->alibaba->alipay();
        $pay_html = $ali->joinalipayappinfo($order_no,$amount,$notify_url);
        $realret['ali_pay_info']  = $pay_html['ali_pay_info'];
        $realret['status']  = 0;
        $realret['desc']  = '';
        return $this->responseJson($realret,0);exit;

    }


    /**lxd 1102
        功能描述: 阿里APP异步回调，在阿里发送请求时，填写本地址，支付成功后，支付宝会给本接口发送请求
        主要参数:
            user_trade_no：订单号
            trade_no：流水号
     */
    public function cbappnotifyAction(){
        $ali= $this->alibaba->alipay();
        $info = $ali->CBAppnotify();
        tracelog("cbnotifyAction，body:" . json_encode($info));
        //根据$info下面的元素，更改数据库
        // body:{"user_trade_no":"orderno00001112","trade_no":"2016110221001004640220461541","alitype":"notify_url","channel":"alipay","status":0}
        if($info['status']==0){
            echo "success";//给支付宝网关回消息，告诉已经收到通知
            //业务逻辑，更新数据库等操作
        }
    }

    /**lxd 1107
        功能描述: 阿里 订单查询接口
        入参:
            ordercode：订单号
        出参：
            orderstate：订单状态
     */
    public function aliorderstateAction(){
        $order_no = isset($_GET['ordercode'])?$_GET['ordercode']:'';
        $ali= $this->alibaba->alipay();
        $info = $ali->orderstate($order_no);
        tracelog("aliorderstateAction，body:" . json_encode($info));
        return $this->responseJson($info,0);
    }
}

