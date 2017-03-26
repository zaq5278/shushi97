<?php

namespace App\Wap\Controllers;

use App\Models\Assess;
use App\Models\Category;
use App\Models\Depot;
use App\Models\Franchise;
use App\Models\Goods;
use App\Models\Banners;
use App\Models\Integral;
use App\Models\Member;
use App\Models\MemberInfo;
use App\Models\Order;
use App\Models\OrderGoods;
use App\Models\OrderLog;
use App\Models\PayLog;
use App\Models\UAddress;
use App\Models\shoppingCart;
use App\Models\Collection;
use Plugin\Core\QSTBaseController;
use Phalcon\Mvc\View;
use Phalcon\Http\Request;
use Phalcon\Mvc\Model\Query;


/**
 * Display the default index page.
 */
class ApiController extends QSTBaseController{

    private  $userid;

    public function initialize()
    {
        parent::initialize();
        $this->userid = $this->request->get('sessid');
        /*$sessid = $this->request->get('PHPSID');
        $this->log('记录sessid:'.$sessid);
        if(!empty($sessid)){
            session_id($sessid);
            session_start();
        }*/

        if(empty($this->userid)){
            $this->userid = $this->session->get('userid');
        }
        //$this->log('记录用户session:'.json_encode($_SESSION));
        if(empty($this->userid)) {
            $action = $this->router->getActionName();
            if (in_array($action, ['uAddress', 'ucollection', 'ushoppingCart', 'uintegral', 'memberInfo','integralOrder','Order'])) {
                echo json_encode(['status' => 10014, 'desc' => '请登录后访问！']);
                exit;
            }
        }

        $this->log('记录用户id:'.$this->userid);
//        //print_r($_SESSION);exit;
//        if(!empty($sessid)){
//            /*session_id($sessid);
//            session_start();
//            if(empty($this->session->get('userid'))){
//                wxmemlogin
//            }else{
//                $this->userid = $this->session->get('userid');
//            }*/
//        }
//        if(empty($this->userid)){
//            $action =  $this->router->getActionName();
//
//            if(in_array($action,['uAddress','ucollection','ushoppingCart','uintegral','memberInfo'])){
//                echo json_encode(['status'=>10014,'desc'=>'请关注后访问！']);
//                exit;
//            }
//        }
    }
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
        //$body = $this->request->getRawBody();
        //$body = json_decode($body,true);
        $body = ['account'=>$this->request->getPost('tel')];
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
        //$body = $this->request->getRawBody();
        //$body = json_decode($body,true);
        $body = ['account'=>$this->request->getPost('tel'),'code'=>$this->request->getPost('code'),'type'=>2];
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
        require_once(APP_DIR."/plugin/flogin/apiuseropt.php");
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
        require_once(API_DIR."/login/apiuseropt.php");
        $body = $this->request->getRawBody();
        $this->log($body);
        $body = json_decode($body,true);

        $body['openid']=$this->session->get('openid');
        $this->log($body);
        $ret =bind3account($body);
        if($ret['status'] ==0) {
            $this->session->set("isbind",1);
        }
        else {
            $this->session->set("isbind",0);
        }
        #号码绑定可以带扩展参数，扩展参数根据业务功能来定
        $ext = $body['ext'];
        if(!empty($ext)){
            require_once("serviceinterface.php");
            $param['userid'] = $ret['out_data']['userid'];
            $param['vtype'] = 0;
            if($body['ext'] ==true){ //验房师
                $param['vtype'] = 1;
            }
            $param['vstate'] = 0;
            $this->log("param----------");
            $this->log($param);
            user_yfs_state_update($param);
        }
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
    /* 微信授权后，获取用户信息*/
    /*微信授权在BaseController里已实现*/
    public function wxloginAction(){
        $code = $this->request->getQuery('code');
        $state =$this->request->getQuery('state');
        $this->log("wxloginnAction code:".$code.' state:'.$state);
        $resp = $this->weixin->jssdk()->wxLogin($code);

        $this->log("wxloginnAction resp:");
        $this->log(json_encode($resp));
        $this->session->set("openid",$resp->openid);
        $this->session->set("nickname",$resp->nickname);
        $this->session->set("sex",$resp->sex);
        $this->session->set("city",$resp->city);
        $this->session->set("province",$resp->province);
        $this->session->set("country",$resp->country);
        $this->session->set("headimgurl",$resp->headimgurl);
        require_once(API_DIR."/flogin/apiuseropt.php");
        //require_once("serviceinterface.php");
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
        $resp = $this->execsql($loginparam, "p_user_login");
        if($resp['status']==0){
            $this->session->set("userid",$resp['out_data']['userid']);
            $this->session->set("account",$resp['out_data']['account']);
            $this->session->set("token",$resp['out_data']['token']);
            $this->session->set("isbind",$resp['out_data']['isbind']);
            $this->session->set("thirdtype", $loginparam['type']);
            $loginparam['userid']=$resp['out_data']['userid'];

            //  $this->log("wxloginAction-----------------");
            //$this->log($loginparam['headurl']);

            $ret =$this->execsql($loginparam,'p_userinfo_add');
            $this->session->set("sex", $loginparam['sex']);
            $this->session->set("nick", $loginparam['nick']);
            $this->session->set("headurl", $loginparam['headurl']);

            /*$body['userid']=$resp['out_data']['userid'];
            $ret = user_is_yfs($body);
            if(empty($ret['out_data']['vtype'])){
                $this->session->set("isyfs",0);
                $this->session->set("yfsstate",0);
                $this->log("vstate-------------1111");
                $this->log($this->session->get("yfsstate"));
           }
            else {
                $vtype =$ret['out_data']['vtype'];
                $vstate =$ret['out_data']['vstate'];
                $this->log("vstate-------------444".$vtype.$vstate);
                $this->session->set("isyfs",$vtype);
                $this->session->set("yfsstate",$vstate);
                $this->log($vstate);
            }*/
        }
        $url = '../index';
        /*if($this->session->get("isyfs") <>0){ //# 去验房师页面
           //0.待审批、1.已取消、2.已通过、3.未通过
           $url = '../master';
            //if($this->session->get("yfsstate")==2){
            //审核通过去验房师页面
            //             $url = '../master';
            //           }
            //           else { 审核不通过或待审核，普通用户页面}
        }
        $this->log($url);*/
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

    /**手机首页数据 接口
     * @return mixed
     */
    public  function getGoodsAction(){
        header("Access-Control-Allow-Origin: *");
        $id = $this -> request->get('id',"int",0);
        $where = ' is_delete =0 and is_on_sale = 1';//普通商品调用时要上架和没有被删除
        $integralSql = ' gd.is_delete =0 and gd.is_on_sale = 1 ';//在销售和综合排序里使用
        $data = array();
        if(isset($id) && !empty($id)){
            $where = ' goods_id = :id: and ' . $where;
            //$fields = 'goods_id ,depot_id, cat_id, goods_name , goods_brief , market_price , shop_price , goods_number , is_integral , integral , good_introduction , good_details , good_spec';
            $getGoodsOne = Goods::find([
                'conditions' => $where,
                'bind' =>['id' => $id]
            ]);
            //获取商品是否被收藏1是 0 否
            $is_coll = 0;
            if(!empty($this->userid)){
                $collen = new Collection();
                $coll = $collen->findFirst('userid=' . $this->userid .' and g_id =' .$id);
                $is_coll = empty($coll->id)? 0 : 1;
            }
            if($getGoodsOne->count()){
                foreach($getGoodsOne as $value){
                    $sellNum = $this->getGoodsNum($value->goods_id);//获取商品销售数量
                    $assessNum = Assess::count('goods_id = '.$value->goods_id);
                    $depot = $value->depots->title;
                    $data['goods_id'] = $value->goods_id;
                    $data['depot_id'] = $value->depot_id;
                    $data['depot_province'] = $value->depot->province;
                    $data['freight'] = $value->depot->freight;
                    $data['cate_id'] = $value->cat_id;
                    $data['cate_name'] = $value->category->name;
                    $data['goods_name'] = $value->goods_name;
                    $data['market_price'] = $value->market_price;
                    $data['shop_price'] = $value->shop_price;
                    $data['goods_number'] = $value->goods_number;
                    $data['is_integral'] = $value->is_integral;
                    $data['is_on_sale'] = empty($value->is_on_sale) ? 0 : $value->is_on_sale;
                    $data['integral'] = $value->integral;
                    $data['goods_brief'] = $value->goods_brief;
                    $data['is_coll'] = $is_coll;
                    $data['sellNum'] = empty($sellNum) ? 0 : $sellNum;
                    $data['assessNum'] = empty($assessNum) ? 0 : $assessNum;
                    $data['goods_introduction'] = explode(',',str_replace('"', '', trim($value->good_introduction,'"] ["')));
                    $data['goods_details'] = explode(',',str_replace('"', '', trim($value->good_details,'"] ["')));
                    $data['goods_spec'] = explode(',',str_replace('"', '', trim($value->good_spec,'"] ["')));


                }
            }else{
                return $this->responseJson($data,10001);
            }
        }else {
            //获取商品数据列表
            $searchStr = $this->request->get('searchStr', "string", '');//搜索关键词
            $startPrice = $this->request->get('startPrice');//开始价格
            $endPrice = $this->request->get('endPrice');//结束价格
            $cat_id = $this->request->get('cate_id');//栏目id
            $page = $this->request->get('page', "int", 1);//当前页
            $total = $this->request->get('total', "int", 10);//一页多少条
            $sort = $this->request->get('sort', 'string', 'DESC');//排序方式
            $is_integral = $this->request->get('integral', 'int', 0);//1是积分
            $is_recom = $this->request->get('is_recom', 'int', 0);//1是积分
            $sortKey = $this->request->get('sortKey','string','coll');//推荐

            $starNum = empty($page) || $page == 1 ? 0 : ($page-1) * $total;

            $where .= empty($is_integral) ? '  and is_integral = 0 ' : ' and is_integral = 1 ';//在普通获取商品里判断
            $where .= !empty($is_recom) ? ' and is_recom = 1 ' : '' ;
            $where .= empty($searchStr) ? '' : ' and goods_name like "%'.$searchStr.'%"';
            if(is_array($cat_id)){
                $where .= !empty(implode(',',$cat_id)) ? ' and cat_id in ( ' . implode(',',$cat_id) .')' : '';
            }else{
                $where .= empty($cat_id) ? '' : ' and cat_id = ' . $cat_id;
            }

            $where .= empty($startPrice) && empty($endPrice) ? '' : ' and shop_price >= ' . $startPrice .' and  shop_price <= ' . $endPrice .'';
            //print_r(['number' => $total, 'offset' => $page]);exit;
            $fields = ['goods_id', 'goods_name', 'cat_id', 'good_introduction', 'market_price' ,'on_saleTime', 'shop_price' , 'goods_number', 'is_integral','integral', 'sort_order'];

            $goodsData = [];
            //按销售商品排序
            if($sortKey == 'hot'){
                $integralSql .= empty($is_integral) ? ' and gd.is_integral = 0' : ' and gd.is_integral = 1';//在销量综合判断是否是积分
                $integralSql .= empty($searchStr) ? '' : ' and gd.goods_name like "%'.$searchStr.'%"';//根据商品名字筛选
                $integralSql .= !empty($is_recom) ? ' and gd.is_recom = 1 ' : '' ;//推荐商品
                if(is_array($cat_id)){
                    $integralSql .= !empty(implode(',',$cat_id)) ? ' and gd.cat_id in (' . implode(',',$cat_id) .')' : '';
                }else{
                    $integralSql .= empty($cat_id) ? '' : ' and gd.cat_id = ' . $cat_id;
                }
                if(empty($is_integral)){
                    $integralSql .= empty($startPrice) && empty($endPrice) ? '' : ' and gd.shop_price >= ' . $startPrice .' and gd.shop_price <= ' . $endPrice .'';
                }else{
                    $integralSql .= empty($startPrice) && empty($endPrice) ? '' : ' and gd.integral >= ' . $startPrice .' and gd.integral <= ' . $endPrice .'';
                }
                //$sqls  = 'select og.goods_id,SUM(og.num) num from qst_order_goods og where og.ordercode in (select o.ordercode from qst_ordercode o where o.vstate = 0) GROUP By goods_id';
                //$sql = 'select gd.goods_id,gd.goods_name,IFNULL(m.num,0) from goods as gd left JOIN (select goods_id,SUM(num) num from App\Models\OrderGoods where ordercode in (select ordercode from App\Models\Order where vstate = 3) GROUP BY goods_id ) m on gd.goods_id = m.goods_id ORDER BY num DESC';
                $sqlss = 'select gd.goods_id,gd.goods_name,gd.cat_id,gd.good_introduction,gd.market_price,gd.shop_price,gd.goods_number,gd.is_integral,gd.integral,gd.sort_order,gd.is_delete , gd.is_on_sale,IFNULL(m.num,0) num from goods gd LEFT  JOIN (SELECT og.goods_id,sum(og.num) num from qst_order_goods og where og.ordercode in (select o.ordercode from qst_ordercode o where o.vstate = 3 or o.vstate = 1 or o.vstate = 2) GROUP BY og.goods_id) m on gd.goods_id = m.goods_id WHERE '.$integralSql.' ORDER BY m.num '.$sort.' limit '.$starNum.','.$total.'';
                $goodsNum = $this->db->query($sqlss);
                while ($goodsInof = $goodsNum->fetch(2)) {
                    $goodsInof['goods_introduction'] = explode(',',str_replace('"', '', trim($goodsInof['good_introduction'], '"] ["')));
                    $goodsData[] = $goodsInof;
                }
            }elseif($sortKey == 'coll'){//综合排序
                $integralSql .= empty($is_integral) ? ' and gd.is_integral = 0' : ' and gd.is_integral = 1';//在销量综合判断是否是积分
                $integralSql .= empty($searchStr) ? '' : ' and gd.goods_name like "%'.$searchStr.'%"';//根据商品名字筛选
                $integralSql .= !empty($is_recom) ? ' and gd.is_recom = 1 ' : '' ;//推荐商品
                if(is_array($cat_id)){
                    $integralSql .= !empty(implode(',',$cat_id)) ? ' and gd.cat_id in (' . implode(',',$cat_id) .')' : '';
                }else{
                    $integralSql .= empty($cat_id) ? '' : ' and gd.cat_id = ' . $cat_id;
                }

                if(empty($is_integral)){
                    $integralSql .= empty($startPrice) && empty($endPrice) ? '' : ' and gd.shop_price >= ' . $startPrice .' and gd.shop_price <= ' . $endPrice .'';
                }else{
                    $integralSql .= empty($startPrice) && empty($endPrice) ? '' : ' and gd.integral >= ' . $startPrice .' and gd.integral <= ' . $endPrice .'';
                }

                $sqlss = 'select gd.goods_id,gd.goods_name,gd.cat_id,gd.good_introduction,gd.market_price,gd.shop_price,gd.goods_number,gd.is_integral,gd.integral,gd.sort_order,gd.on_saleTime,gd.is_delete , gd.is_on_sale,IFNULL(m.num,0) num from goods gd LEFT  JOIN (SELECT og.goods_id,sum(og.num) num from qst_order_goods og where og.ordercode in (select o.ordercode from qst_ordercode o where o.vstate = 3 or o.vstate = 1 or o.vstate = 2) GROUP BY og.goods_id) m on gd.goods_id = m.goods_id WHERE '.$integralSql.'  ORDER BY m.num '.$sort.' , gd.shop_price ASC,gd.on_saleTime '.$sort.'  limit '.$starNum.','.$total.'';
                $goodsNum = $this->db->query($sqlss);
                while ($goodsInof = $goodsNum->fetch(2)) {
                    $goodsInof['goods_introduction'] = explode(',',str_replace('"', '', trim($goodsInof['good_introduction'], '"] ["')));
                    $goodsData[] = $goodsInof;
                }
            }else {//其他排序
                $orderField = $sortKey . ' ' . $sort; //别的排序

                $goodDatas = Goods::find(array(
                    'columns' => $fields,
                    'conditions' => $where,
                    'order' => $orderField,
                    'limit' => ['number' => $total , 'offset' => $starNum]
                ));

                foreach ($goodDatas as $value) {
                    $num = $this->getGoodsNum($value->goods_id);
                    $goodsData[] = array(
                        'goods_id' => $value->goods_id,
                        'goods_name' => $value->goods_name,
                        'cat_id' => $value->cat_id,
                        'goods_introduction' => explode(',', str_replace('"', '', trim($value->good_introduction, '"] ["  " " '))),
                        'market_price' => $value->market_price,
                        'shop_price' => $value->shop_price,
                        'goods_number' => $value->goods_number,
                        'is_integral' => $value->is_integral,
                        'integral' => $value->integral,
                        'on_saleTime' => date('Y-m-d H:i:s',$value->on_saleTime),
                        'num' => empty($num) ? 0 : $num,
                        'sort_order' => $value->sort_order
                    );
                }
            }
            $data['goodsData'] = $goodsData;
            //获取轮播图
            $bannum = $this->request->get('bannum', 'int', 5);
            $fields = 'name , image_url , param , sort,is_integral';
            $where = ' image_url <> "" ';
            $where .= empty($is_integral) ? '  and is_integral = 0 ' : ' and is_integral = 1 ';

            $banDatas = Banners::find(array(
                'columns' => $fields,
                'conditions' => $where,
                'order' => 'sort asc',
                'limit' => ['number' => $bannum]
            ));
            $bannerData = [];
            foreach ($banDatas as $value) {
                $bannerData[] = array(
                    'name' => $this->getGoodsName($value->param),
                    'image_url' => $value->image_url,
                    'param' => $value->param,
                    'sort' => $value->sort,
                    'is_integral' => $value->is_integral
                );
            }
            $data['bannerData'] = $bannerData;

            //获取栏目
            $fields = 'id , name ,title, pid , sort_order';
            $where = ' is_show = 1 ';
            $cateDatas = Category::find(array(
                'columns' => $fields,
                'conditions' => $where,
                'order' => ' sort_order asc,addTime asc'
            ));
            $cateData = [];
            foreach ($cateDatas as $value) {
                $datas = array(
                    'id' => $value->id,
                    'name' => $value->name,
                    'pid' => $value->pid,
                    'sort_order' => $value->sort_order
                );
                if (empty($value->pid)) {
                    $cateData[$value->id] = $datas;
                }
            }

            foreach ($cateDatas as $value) {
                if (!empty($value->pid)) {
                    $datas = array(
                        'id' => $value->id,
                        'name' => $value->title,
                        'pid' => $value->pid,
                        'sort_order' => $value->sort_order
                    );
                    $cateData[$value->pid]['childData'][] = $datas;
                }
            }
            $data['cateData'] = $cateData;
        }
        return $this->responseJson($data,0);
    }
    public function bannerAction(){
        $num = $this -> request->get('num','int',5);
        $fields = ' id , name , image_url , sort , type , param';
        $where = ' 1 ' ;
        $goodDatas = Banners::query()->columns($fields)->where($where)->orderBy('sort desc')->limit($num)->execute();

        $data = array();
        foreach($goodDatas as $value){
            $data['data'][] =array(
                'name' => $value->name,
                'image_url' => $value->image_url,
                'param' => $value->param,
                'sort' => $value->sort
            );
        }
        return $this->responseJson($data,0);
    }

    /** 加盟店信息接口
     * @return mixed
     */
    public function franchiseAction(){
        header("Access-Control-Allow-Origin: *");
        $num = $this -> request->get('num','int',5);
        $province = $this -> request->get('province','string','');
        $fields = ' id , title , litpic , province , city , district , address , mobile , sort_order';
        $where = ' is_show = 1 and province like "%'. $province .'%" ' ;
        $frandDatas = Franchise::query()->columns($fields)->where($where)->orderBy('sort_order desc')->limit($num)->execute();

        $data = array();
        foreach($frandDatas as $value){
            $data['data'][] =array(
                'id' => $value->id,
                'title' => $value->title,
                'litpic' => trim($value->litpic, '"] ["'),
                'province' => $value->province,
                'city' => $value->city,
                'district' => $value->district,
                'address' => $value->address,
                'mobile' => $value->mobile,
                'sort_order' => $value->sort_order
            );
        }
        return $this->responseJson($data,0);
    }

    /** 获取会员中心收货地址接口
     * @return mixed
     */
    public function uAddressAction(){
        header("Access-Control-Allow-Origin: *");
        header('Access-Control-Allow-Methods: GET, POST, DELETE, PATCH');

        if($this->request->isGet()){
            $id = $this->request->get('id', "int", 0);
            $setdefault = $this->request->get('setdefault', "int",0);
            $page = $this->request->get('page', "int", 1);
            $total = $this->request->get('total', "int", 10);

            $starNum = empty($page) || $page == 1 ? 0 : ($page-1) * $total;
            $fields = ['id','vname', 'province', 'city', 'tel', 'address' , 'code','setdefault'];
            $where = ' userid = ' . $this->userid;
            $where .= empty($setdefault) ? '' : ' and setdefault = 1';

            if(empty($id)){
                $UAddressDatas = UAddress::find(array(
                    'columns' => $fields,
                    'conditions' => $where,
                    'order' => ' btime desc',
                    'limit' => ['number' => $total , 'offset' => $starNum]
                ));
            }else{
                $where .= ' and id = ' . $id;
                $UAddressDatas = UAddress::find(array(
                    'columns' => $fields,
                    'conditions' => $where
                ));
            }
            $UAddressData = [];
            foreach ($UAddressDatas as $value) {
                $UAddressData[] = array(
                    'id' => $value->id,
                    'vname' => $value->vname,
                    'province' => $value->province,
                    'city' => $value->city,
                    'tel' => $value->tel,
                    'address' => $value->address,
                    'code' => $value->code,
                    'setdefault' => $value->setdefault
                );
            }
            $data['addressData'] = $UAddressData;
            return $this->responseJson($data,0);
        }
        //新增一个地址
        if($this->request->isPost()){
            $vname = $this->request->get('vname', "string", '');
            $province = $this->request->get('province', "string", '');
            $city = $this->request->get('city', "string", '');
            $tel = $this->request->get('tel', "int", 0);
            $address = $this->request->get('address', "string", '');
            $setdefault = $this->request->get('setdefault', "int", 0);

            if(empty($vname)){
                return $this->responseJson(array(),10002);
            }
            if(empty($province)){
                return $this->responseJson(array(),10003);
            }
            if(empty($city)){
                return $this->responseJson(array(),10004);
            }
            if(empty($tel)){
                return $this->responseJson(array(),10005);
            }
            if(empty($address)){
                return $this->responseJson(array(),10006);
            }

            $UAddress = new UAddress();
            $UAddress->userid = $this->userid;
            $UAddress->vname = $vname;
            $UAddress->province = $province;
            $UAddress->city = $city;
            $UAddress->tel = $tel;
            $UAddress->address = $address;
            $UAddress->code = $this->request->get('code', "int", 000000);
            $UAddress->setdefault = $setdefault;
            $UAddress->btime = time();
            if($UAddress->save()){
                return $this->responseJson([],0);
            }
            return $this->responseJson([],10014);
        }
        //更新指定的地址PATCH
        if($this->request->isPatch()){
            $id = $this->request->get('id', "int", 0);
            $setdefault = $this->request->get('setdefault', "int", 0);

            if(empty($id)){
                return $this->responseJson(array(),10007);
            }

            if(!empty($setdefault)){
                $uaddSql = "update App\Models\UAddress set setdefault = 0  where userid =". $this->userid;
                $this->modelsManager->executeQuery($uaddSql);

                $UAddress = new UAddress();
                $uAdd = $UAddress->findFirst('id = '.$id);
                if(!empty($uAdd->id)) {
                    $uAdd->setdefault = 1;
                    if($uAdd->save()){
                        return $this->responseJson(array(),0);
                    }
                }
            }else{
                $vname = $this->request->get('vname', "string", '');
                $province = $this->request->get('province', "string", '');
                $city = $this->request->get('city', "string", '');
                $tel = $this->request->get('tel', "int", 0);
                $address = $this->request->get('address', "string", '');

                if(empty($vname)){
                    return $this->responseJson(array(),10002);
                }
                if(empty($province)){
                    return $this->responseJson(array(),10003);
                }
                if(empty($city)){
                    return $this->responseJson(array(),10004);
                }
                if(empty($tel)){
                    return $this->responseJson(array(),10005);
                }
                if(empty($address)){
                    return $this->responseJson(array(),10006);
                }

                $UAddress = new UAddress();
                $uAdd = $UAddress->findFirst('id = '.$id);
                if(!empty($uAdd->id)) {
                    $uAdd->vname = $vname;
                    $uAdd->province = $province;
                    $uAdd->city = $city;
                    $uAdd->tel = $tel;
                    $uAdd->address = $address;
                    $uAdd->code = $this->request->get('code', "int", 000000);

                    if ($uAdd->save()) {
                        return $this->responseJson(array(), 0);
                    }
                }
            }
            return $this->responseJson(array(),1);
        }
        //删除指定的地址DELETE
        if($this->request->isDelete()){
            $uaddress_ID = $this->request->getQuery("id",'int',0);
            $uaddress = UAddress::find(' userid = '. $this->userid .' and id = ' . $uaddress_ID);
            if ($uaddress) {
                $uaddress->id = $uaddress_ID;
                if($uaddress->delete()){
                    return $this->responseJson(array(),0);
                }
                return $this->responseJson(array(),1);
            }
            return $this->responseJson(array(),10007);
        }
    }

    /**更新会员收藏夹
     * @return mixed
     */
    public function ucollectionAction(){
        header("Access-Control-Allow-Origin: *");
        header('Access-Control-Allow-Methods: GET, POST, DELETE, PATCH');
        if($this->request->isGet()){
            $page = $this->request->get('page', "int", 1);
            $total = $this->request->get('total', "int", 10);

            $starNum = empty($page) || $page == 1 ? 0 : ($page-1) * $total;
            //$fields = ['id','userid','g_id','addTime'];
            $where = ' userid = ' . $this->userid;

            $phql = "SELECT c.id,c.userid,c.g_id,c.addTime,g.goods_name,g.goods_number,g.good_introduction,g.shop_price,g.market_price,g.integral,g.is_integral,g.is_on_sale FROM App\Models\Collection AS c left JOIN App\Models\Goods as g  on c.g_id=g.goods_id where $where ORDER BY c.id DESC limit $starNum,$total";
            $collectionDatas = $this->modelsManager->executeQuery($phql);

            $collectionData = [];
            foreach ($collectionDatas as $value) {
                $collectionData[] = array(
                    'id' => $value->id,
                    'g_id' => $value->g_id,
                    'title' => $value->goods_name,
                    'litpic' => str_replace('"', '', str_replace('"', '', trim($value->good_introduction, '"] ["'))),
                    'market_price' => $value->market_price,
                    'is_on_sale' => $value->is_on_sale,
                    'price' => (empty($value->is_integral)? $value->shop_price:$value->integral),
                    'num' => $value->goods_number,
                    'is_integral' => $value->is_integral
                );
            }
            $data['collectionData'] = $collectionData;
            return $this->responseJson($data,0);
        }
        //新增一个收藏
        if($this->request->isPost()){
            $goods_id = $this->request->getQuery("goods_id",'int',0);
            if(empty($goods_id)){
                return $this->responseJson(array(),10001);
            }
            $collection = Collection::findFirst(' userid = ' . $this->userid . ' and g_id = ' . $goods_id);
            if (empty($collection->id)) {
                $Collection = new Collection();
                $Collection->userid = $this->userid;
                $Collection->g_id = $goods_id;
                $Collection->addTime = time();
                if($Collection->save()){
                    $goodsData= Goods::findFirst(' goods_id = ' . $goods_id);
                    $goodsData->is_coll = 1;
                    $goodsData->save();
                    return $this->responseJson(array(),0);
                }
            }
            return $this->responseJson(array(),10013);
        }
        //删除指定的收藏DELETE
        if($this->request->isDelete()){
            $collection_ID = $this->request->getQuery("id",'int',0);
            $goods_id = $this->request->getQuery("goods_id",'int',0);
            $collection = new Collection();
            if(!empty($collection_ID)){
                $collection = Collection::findFirst(' userid = '. $this->userid .' and id = '. $collection_ID);
            }elseif(!empty($goods_id)){
                $collection = Collection::findFirst(' userid = ' . $this->userid . ' and g_id = ' . $goods_id);
            }else{
                return $this->responseJson(array(),10008);
            }
            if (!empty($collection->id)) {
                if($collection->delete()){
                    return $this->responseJson(array(),0);
                }
                return $this->responseJson(array(),1);
            }
            return $this->responseJson(array(),10008);
        }
    }

    /**更新会员购物车
     * @return mixed
     */
    public function ushoppingCartAction(){
        header("Access-Control-Allow-Origin: *");
        header('Access-Control-Allow-Methods: GET, POST, DELETE');
        if($this->request->isGet()){
            $page = $this->request->get('page', "int", 1);
            $total = $this->request->get('total', "int", 10);

            $starNum = empty($page) || $page == 1 ? 0 : ($page-1) * $total;
            $fields = ' c.id,c.userid,c.g_id,c.num,c.addTime,g.goods_name,g.good_introduction,g.market_price,g.is_on_sale,g.shop_price,g.goods_number ';
            $where = ' c.userid = ' . $this->userid;

            $phql = "SELECT $fields FROM App\Models\shoppingCart AS c left JOIN App\Models\Goods as g  on c.g_id=g.goods_id where $where ORDER BY c.id DESC limit $starNum,$total";

            $shoppingCartDatas = $this->modelsManager->executeQuery($phql);

            $shoppingCartData = [];
            foreach ($shoppingCartDatas as $value) {
                $litpic = explode(',',str_replace('"', '',trim($value->good_introduction, '"] ["')))[0];
                $shoppingCartData[] = array(
                    'id' => $value->id,
                    'g_id' => $value->g_id,
                    'title' => $value->goods_name,
                    'litpic' => $litpic,
                    'market_price' => $value->market_price,
                    'price' => $value->shop_price,
                    'is_on_sale' => $value->is_on_sale,
                    'is_Have' => empty($value->goods_number) ? 0 : 1,
                    'addTime' => $value->addTime,
                    'num' => $value->num
                );
            }
            $data['shoppingCart'] = $shoppingCartData;
            return $this->responseJson($data,0);
        }
        //新增一个购物车产品
        if($this->request->isPost()){
            $goods_id = $this->request->get('goods_id', "int", 0);
            $num = $this->request->get('num', "int", 1);
            if(empty($goods_id)){
                return $this->responseJson(array(),10001);
            }
            $shoppingCarts = shoppingCart::findFirst(' userid = ' . $this->userid . ' and g_id = ' . $goods_id);
            if (empty($shoppingCarts->id)) {
                $shoppingCartss = new shoppingCart();
                $shoppingCartss->userid = $this->userid;
                $shoppingCartss->g_id = $goods_id;
                $shoppingCartss->num = $num;
                $shoppingCartss->addTime = time();
                if ($shoppingCartss->save()) {
                    return $this->responseJson(array(), 0);
                }
            }else{
                $phqlss = "update App\Models\shoppingCart set num = num + {$num} where id =". $shoppingCarts->id;
                $this->modelsManager->executeQuery($phqlss);
                return $this->responseJson(array(),0);
            }
            return $this->responseJson(array(),10015);
        }
        //删除指定的购物车DELETE
        if($this->request->isDelete()){
            $shoppingCart_ID = $this->request->getQuery("id",'int',0);
            $shoppingCart = shoppingCart::find(' userid = '. $this->userid .' and id = ' . $shoppingCart_ID);
            if ($shoppingCart) {
                $shoppingCart->id = $shoppingCart_ID;
                if($shoppingCart->delete()){
                    return $this->responseJson(array(),0);
                }
                return $this->responseJson(array(),1);
            }
            return $this->responseJson(array(),10008);
        }
    }


    /** 获取会员中心积分接口
     * @return mixed
     */
    public function uintegralAction(){
        header("Access-Control-Allow-Origin: *");
        header('Access-Control-Allow-Methods: GET, POST');
        if($this->request->isGet()){
            $id = $this->request->get('id', "int", 0);
            $page = $this->request->get('page', "int", 1);
            $total = $this->request->get('total', "int", 10);

            $starNum = empty($page) || $page == 1 ? 0 : ($page-1) * $total;
            $fields = ['id','title','type', 'integral', 'totalIntegral', 'addTime'];
            $where = ' userid = ' . $this->userid;
            $UIntegralDatas = [];
            if(empty($id)){
                $UIntegralDatas = Integral::find(array(
                    'columns' => $fields,
                    'conditions' => $where,
                    'order' => ' id desc',
                    'limit' => ['number' => $total , 'offset' => $starNum]
                ));
            }else{
                $where .= ' and id = ' . $id;
                $UIntegralDatas = Integral::find(array(
                    'columns' => $fields,
                    'conditions' => $where
                ));
            }
            $UIntegralData = [];
            foreach ($UIntegralDatas as $value) {
                $typeStr = empty($value->type)? '+ ':'- ';
                $UIntegralData[] = array(
                    'id' => $value->id,
                    'title' => $value->title,
                    'integral' => $typeStr.$value->integral,
                    'totalIntegral' => $value->totalIntegral,
                    'addTime' => date('Y-m-d H:i:s',$value->addTime)
                );
            }
            $data['integralData'] = $UIntegralData;
            return $this->responseJson($data,0);
        }
        //新增一个积分
        if($this->request->isPost()){
            $title = $this->request->get('title', "string", '');
            $integral = $this->request->get('integral', "int", 0);
            $type = $this->request->get('type', "int", 0);
            $addTime = $this->request->get('addTime', "int", time());

            if(empty($title)){
                return $this->responseJson(array(),10009);
            }
            if(empty($integral)){
                return $this->responseJson(array(),10010);
            }
            if(empty($type)){
                return $this->responseJson(array(),10011);
            }
            if(empty($addTime)){
                return $this->responseJson(array(),10012);
            }
            $userInfor['integral'] = 0; //获取当前登录用额积分

            $Integral = new Integral();
            $Integral->userid = $this->userid;
            $Integral->title = $title;
            $Integral->type = $type;
            $Integral->integral = $integral;
            $Integral->totalIntegral = $integral + $userInfor['integral'];
            $Integral->addTime = $addTime;

            if($Integral->save()){
                return $this->responseJson(array(),0);
            }
            return $this->responseJson(array(),1);
        }
    }

    /**
     *获取signtrue
     */
    public function getSignAction(){
        header("Access-Control-Allow-Origin: *");
        header('Access-Control-Allow-Methods: GET');
        $url = $this->request->getQuery('url');
        $data = $this->weixin->jssdk()->getSignsPackage($url);
        return $this->responseJson($data,0);
    }
    /**
     *从自定义菜单点击入口（微信推送）
     */
    public function memberloginAction(){
        require_once(APP_DIR."/plugin/flogin/conf.php");
        $isp = $this->request->getQuery('isp','int',0);
        $callBackUrl = _CALLBACK_URL_;
        if(empty($isp)){//默认首页
            $callBackUrl .= '?url=wap/#?/tabs/homePage';
        }elseif($isp == 1){//积分商品
            $callBackUrl .= '?url=wap/#?/tabs/integralStore';
        }elseif($isp == 2){//个人会员中心
            $callBackUrl .= '?url=wap/#?/tabs/personal';
        }elseif($isp == 3){//扫码支付
            $napaid = $this->request->getQuery('napaid');
            $callBackUrl .= '?url=wap/scanCodePayment.html&napaid='.$napaid;
        }elseif($isp == 4){//推荐分享
            $recomid = $this->request->getQuery('c_user','int',0);
            if(!empty($recomid)){
                $callBackUrl .= '?url=wap&userid='.$recomid;
            }
        }elseif($isp == 5){//退款失败跳转
            $callBackUrl .= '?url=wap/#?/tabs/payRecord';
        }
        $url = $this->weixin->jssdk()->wxAuthorization($callBackUrl);
        return $this->response->redirect($url);
    }

    /**
     *微信授权后，获取用户信息
     */
    public function wxmemloginAction(){
        $code = $this->request->getQuery('code');
        $state =$this->request->getQuery('state');
        $napaid =$this->request->getQuery('napaid');
        $url =$this->request->getQuery('url');
        $recomid =$this->request->getQuery('userid','int',0);

        $resp = $this->weixin->jssdk()->wxLogin($code);

        //判断是否关注，如果关注了拉取关注时间
        $follow_time = 0;
        $userinfos = $this->weixin->jssdk()->wxUserInfo($resp->openid);
        if(!empty($userinfos->subscribe)){
            $follow_time = $userinfos->subscribe_time;
        }

        //$this->session->setId($resp->openid);
        $this->session->set("openid",$resp->openid);
        $this->session->set("nickname",$resp->nickname);
        $this->session->set("sex",$resp->sex);
        $this->session->set("city",$resp->city);
        $this->session->set("province",$resp->province);
        $this->session->set("country",$resp->country);
        $this->session->set("headimgurl",$resp->headimgurl);
        $random = new \Phalcon\Security\Random();

        $openid = $resp->openid;
        $mdata = Member::findFirst('account = "'. $openid .'"');
        if(empty($mdata->id)){
            $member = new Member();
            $member->account = $openid;
            $member->time = time();
            $member->lastlogintime = time();
            $member->token = $random->uuid();
            if ($member->save()) {
                $this->session->set("userid",$member->id);
                $memberInfo = new MemberInfo();
                $memberInfo->userid = $member->id;
                $memberInfo->sex = $resp->sex;
                $memberInfo->headurl = $resp->headimgurl;
                $memberInfo->nick = $resp->nickname;
                $memberInfo->city = $resp->city;
                $memberInfo->province = $resp->province;
                $memberInfo->country = $resp->country;
                $memberInfo->recId = empty($recomid)? 0 : $recomid;//关联推荐人
                $memberInfo->follow_time = $follow_time;
                $memberInfo->addTime = time();
                $memberInfo->lastTime = time();
                if($memberInfo->save() == true){
                    //如果关注了就判断是否是推荐过来的用户(推荐是分享人数加1)
                    if(!empty($recomid)){
                        $phql = "update App\Models\MemberInfo set follow_num = follow_num+1 where userid =".$recomid."";
                        $this->modelsManager->executeQuery($phql);
                    }
                }
            }

        }else{
            $this->session->set("userid",$mdata->id);
            $memberInfos = MemberInfo::findFirst(' userid = '. $mdata->id);
            //判断会员信息不存在再添加
            if(empty($memberInfos->userid)){
                $memberInfo = new MemberInfo();
                $memberInfo->userid = $mdata->id;
                $memberInfo->sex = $userinfos->sex;
                $memberInfo->headurl = $userinfos->headimgurl;
                $memberInfo->nick = $userinfos->nickname;
                $memberInfo->city = $userinfos->city;
                $memberInfo->province = $userinfos->province;
                $memberInfo->country = $userinfos->country;
                $memberInfo->recId = 0;//关联推荐人
                $memberInfo->follow_time = $follow_time;
                $memberInfo->addTime = time();
                $memberInfo->lastTime = time();
                $memberInfo->save();
                $this->log('保存会员信息：'.$mdata->id.','.$userinfos->sex.','.$userinfos->headimgurl.','.$userinfos->nickname.','.$userinfos->city.','.$userinfos->country.',');
            }

            //更新关注时间
            $strSql = '';
            if($memberInfos->follow_time != $follow_time){
                $strSql = ' follow_time ='.$follow_time;
            }
            //if($memberInfos->headurl != $resp->headimgurl) {
                $strSql .= empty($strSql) ? ' headurl = "' . $userinfos->headimgurl . '"' : ' , headurl = "' . $userinfos->headimgurl . '"';
            //}
            //if($memberInfos->nick != $resp->nickname){
                $strSql .= ' , nick = "'. $userinfos->nickname .'"';
            //}
            //$this->log('微信信息拉取：'.$strSql);
            if(!empty($strSql)){
                $phql = "update App\Models\MemberInfo set {$strSql} where userid =".$mdata->id."";
                //$this->log('微信信息拉取：'.$phql);
                $this->modelsManager->executeQuery($phql);
            }
            $mdata->lastlogintime = time();
            $mdata->token = $random->uuid();
            $mdata->save();
        }
        if(empty($userinfos->subscribe)){
            $url = 'https://mp.weixin.qq.com/mp/profile_ext?action=home&__biz=MzI1NzU3ODU1Mw==&scene=110#wechat_redirect';
        }
        !empty($napaid) ? $this->session->set("napaid",$napaid):'';//获取加盟店id为扫码时使用

        return $this->response->redirect($url);
    }
    /**
     *获取会员个人信息
     */
    public function memberInfoAction(){
        header("Access-Control-Allow-Origin: *");
        header('Access-Control-Allow-Methods: GET');
        $data = [];
        if(!empty($this->userid)){
            $mdata = MemberInfo::findFirst('userid = '. $this->userid);
            $cartnum = shoppingCart::count('userid = '. $this->userid);
            $data['userid'] = $this->userid;
            $data['nickname'] = $mdata->nick;
            $data['headimgurl'] = $mdata->headurl;
            $data['integral'] = $mdata->integral;
            $data['cartnum'] = empty($cartnum)? 0 : $cartnum;
            return $this->responseJson($data,0);
        }
        return $this->responseJson([],1);
    }

    /**
     *积分订单
     */
    public function integralOrderAction(){
        header("Access-Control-Allow-Origin: *");
        header('Access-Control-Allow-Methods: GET, POST');
        if(!$this->request->isPost()){
            return $this->responseJson([],10023);
        }

        $this->db->begin();
        //判断会员是否登录
        if(empty($this->userid)){
            return $this->responseJson([],10016);
        }
        //获取商品id
        $goods_id = $this->request->get('goods_id','int',0);
        if(empty($goods_id)){
            return $this->responseJson([],10001);
        }
        //获取收货地址id
        $uaddid = $this->request->get('collid','int',0);
        if(empty($uaddid)){
            return $this->responseJson([],10018);
        }
        $uaddress = UAddress::findFirst(' userid = ' . $this->userid . ' and id = '.$uaddid);
        if(empty($uaddress->id)){
            return $this->responseJson([],10018);
        }
        //获取买家留言
        $message = $this->request->get('mess','string','');
        //获取积分商品信息
        //echo ' is_integral = 1 and  is_delete =0 and is_on_sale = 1 and goods_id = '. $goods_id;exit;
        $goods = Goods::findFirst(' is_integral = 1 and  is_delete =0 and is_on_sale = 1 and goods_id = '. $goods_id);
        if(empty($goods->goods_id)){
            return $this->responseJson([],10001);
        }
        //获取会员当前积分
        $integrald = MemberInfo::findFirst('userid = '. $this->userid);
        //计算差值
        $diff = $integrald->integral - $goods->integral;
        if(empty($integrald->integral) ||  ($diff < 0) || !is_integer($diff)){
            return $this->responseJson([],10017);
        }
        //把剩余积分更新到会员信息
        $integrald->integral = $diff;
        if($integrald->save() == false){
            $this->db->rollback();
            return $this->responseJson([],10019);
        }
        //添加积分消费记录
        $integraData = new Integral();
        $integraData->userid = $this->userid;
        $integraData->title = '积分兑换商品';
        $integraData->type = 1;
        $integraData->integral = $goods->integral;
        $integraData->totalIntegral = $diff;
        $integraData->addTime = time();
        if($integraData->save() == false){
            $this->db->rollback();
            return $this->responseJson([],10020);
        }
        //生成订单号
        $oid = $this->createOrderCode('SS'.date('YmdHis'));
        //更新订单信息表
        $order = new Order();
        $order->userid = $this->userid;
        $order->ordercode = $oid;
        $order->masterorder = $oid;
        $order->type = 1;
        $order->depotid = $goods->depot_id;
        $order->btime = time();
        $order->vstate = 1;
        $order->collid = $uaddid;
        $order->mess = $message;
        $order->distribution = '普通快递';
        $order->totalPrice = 0.00;
        $order->integral = $goods->integral;
        $order->disPrice = 0.00;
        $order->payment = 0;
        $order->vname = $uaddress->vname;
        $order->province = $uaddress->province;
        $order->city = $uaddress->city;
        $order->tel = $uaddress->tel;
        $order->address = $uaddress->address;

        if($order->save() == false){
            $this->db->rollback();
            return $this->responseJson([],10021);
        }
        //订单附加商品信息表
        $orderGoods = new OrderGoods();
        $orderGoods->ordercode = $oid;
        $orderGoods->goods_id = $goods_id;
        $orderGoods->goods_name = $goods->goods_name;
        $orderGoods->goods_introduction = $goods->good_introduction;
        $orderGoods->market_price = $goods->market_price;
        $orderGoods->goods_price = $goods->shop_price;
        $orderGoods->market_price = $goods->market_price;
        $orderGoods->con_integral = $goods->con_integral;
        $orderGoods->fran_cash = $goods->fran_cash;
        $orderGoods->ref_integral = $goods->ref_integral;
        $orderGoods->num = 1;
        $orderGoods->totalPrice = 0;
        $orderGoods->btime = time();
        if($orderGoods->save()==false){
            $this->db->rollback();
            return $this->responseJson([],10022);
        }
        //积分商品默认支付成功
        $orderlog = new OrderLog();
        $orderlog->ordercode = $oid ;
        $orderlog->state = 1 ;
        $orderlog->btime = time();
        if( $orderlog->save() == false ){
            $this->db->rollback();
            return $this->responseJson([],10022);
        }
        //减库存
        $goods_number = $goods->goods_number;
        $goods->goods_number = $goods_number - 1;
        if( $goods->save() == false ){
            $this->db->rollback();
            return $this->responseJson([],10022);
        }
        $this->db->commit();

        return $this->responseJson(['oid' => $oid],0);
    }

    //获取订单
    public function OrderAction(){
        header("Access-Control-Allow-Origin: *");
        header('Access-Control-Allow-Methods:  GET, POST, DELETE, PATCH');

        $where = ' userid = '. $this->userid;
        $oid = $this->request->get('oid', 'string', '');
        $orderLogs= [];//获取订单状态列表
        $address = [];

        if($this->request->isGet()) {
            $type = $this->request->get('type', 'int', 0);
            $state = $this->request->get('state');
            $is_evaluation = $this->request->get('is_evaluation');

            $where .= empty($type) ? '' : ($state == 1 ? ' and type = 0 ' :' and type = 1');
            if( $state == 7){ //用于区分全部产品和待付款产品
                $where .= ' and vstate = 0';
            }else{
                $where .= empty($state) ? '' : ' and vstate = ' . $state;
            }
            //待评价 is_evaluation 用2代替，
            if($is_evaluation == 2){
                $where .= ' and is_evaluation = 0';
            }
            $page = $this->request->get('page', "int", 1);
            $total = $this->request->get('total', "int", 10);
            $where .= empty($oid) ? '' : ' and ordercode = "' . $oid .'"  or  masterorder = "'.$oid.'"';
            $starNum = empty($page) || $page == 1 ? 0 : ($page-1) * $total;
            //获取订单列表
            $orderD = $this->getOrderData($where,$starNum,$total,$page);
            if(!empty($oid)){
                $order = Order::find(array(
                    'conditions' => $where,
                    'order' => 'id desc',
                    'limit' => ['number' => $total , 'offset' => $starNum]
                ));

                $ordLog = OrderLog::find(['conditions' => 'ordercode = "' . $oid . '"','order' => 'state asc']);
                if(!empty($ordLog->count())){
                    foreach ($ordLog as $value) {
                        $orderLogs[] = [
                            'state' => $this->getStateTitle($value->state),
                            'mess' => $value->mess,
                            'btime' => date('Y-m-d H:i:s',$value->btime)
                        ];
                    }
                }
                $address['logisticsnum'] = $order[0]->logisticsnum;
                $address['vname'] = $order[0]->vname;
                $address['province'] = $order[0]->province;
                $address['city'] = $order[0]->city;
                $address['tel'] = $order[0]->tel;
                $address['address'] = $order[0]->address;
                $orderD = array_merge($orderD[0],$address,['orderLogs'=>$orderLogs]);

            }
            $data['orderData'] = $orderD;
            return $this->responseJson($data,0);
        }
        if($this->request->isPatch()){
            $state = $this->request->get('state', 'int', 0);
            $mess = $this->request->get('mess', 'string', '');
            if(empty($oid)){
                return $this->responseJson([],10024);
            }
            //批量检查订单状态
            $wherestr = ' userid = '. $this->userid .' and ordercode = "' . $oid . '"';
            $orderInfo = Order::find($wherestr.' and vstate = '.$state);
            foreach($orderInfo as $value){
                if(!empty($value->id)){
                    return $this->responseJson([],10026);
                }
            }

            $orderD = Order::findFirst($wherestr);
            if(empty($orderD->id)){
                return $this->responseJson([],10024);
            }
            //$is_state = $this->getBoolState($state,$orderD->vstate);//控制下部状态
            /*if(!$is_state){
                return $this->responseJson([],10025);
            }*/

            $oid = $orderD->ordercode;
            $totalPrice = $orderD->totalPrice;//获取总钱数

            //事务开始
            $this->db->begin();
            //更新提交状态
            $phqlss = "update App\Models\Order set vstate= {$state} where $wherestr";
            $orderState = $this->modelsManager->executeQuery($phqlss);
            if($orderState->success() == false){
                $this->db->rollback();
                return $this->responseJson([],10025);
            }

            //支付成功时添加支付记录（前台判断）
            if($state == 1){
                $intPayLogSql = 'insert into App\Models\PayLog (userid,type,AddSub,ordercode,title,totalprice,state,addTime) VALUES ('.$this->userid.',0,1,"'.$oid.'","商城购买",'.$totalPrice.',1,'.time().')';
                $isPayLogSql = $this->modelsManager->executeQuery($intPayLogSql);
                if($isPayLogSql->success() == false){
                    $this->db->rollback();
                    return $this->responseJson([],10032);
                }
            }

            if($state == 3){//当点击确认收货时，1、需要给用户返积分 2、推荐者返积分 3、同城加盟店返现
                //判断订单类型 普通商品才返积分
                if(empty($orderD->type)){
                    $userid = $orderD->userid;//获取购买商品用户id
                    $city = $orderD->city;//当前订单城市
                    $province = $orderD->province; //当前订单省份
                    $orderGoods = OrderGoods::find('ordercode ="'.$oid.'"');
                    //可能会遇到一单多商品
                    foreach($orderGoods as $value){
                        $totalPrice = $value->totalPrice + $orderD->disPrice;//获取总钱数+运费
                        $con_integral = $value->con_integral;//返积分比例
                        $ref_integral = $value->ref_integral;//推荐人返积分比例
                        $fran_cash = $value->fran_cash;//加盟店返现比例
                        //返给购买者积分
                        $userInfo = MemberInfo::findFirst('userid = '.$userid);//当前用户的信息

                        //返现给推荐者积分
                        $this->log('推荐者id'.$userInfo->recId.'='.$con_integral);
                        if(!empty($userInfo->recId) && !empty($con_integral)){
                            $recUserInfo = MemberInfo::findFirst(' userid = '.$userInfo->recId);//推荐者信息
                            $userIntegral =  round($totalPrice * ($ref_integral / 100));//计算要返的积分
                            $countIntegral = $recUserInfo->integral + $userIntegral;//总的+要返的积分
                            //给推荐者加积分
                            $recUserInfo->integral = $countIntegral;
                            if($recUserInfo->save() == false){
                                $this->log('推荐者积分更新失败'.$countIntegral);
                                $this->db->rollback();
                                return $this->responseJson([],10025);

                            }
                            //添加积分消费记录
                            $values = $userInfo->recId.',"推荐返积分",0,'.$userIntegral.','.$countIntegral.','.time();
                            $integraSql = 'insert into App\Models\Integral (userid,title,type,integral,totalIntegral,addTime) VALUES ('.$values.')';
                            $integraData = $this->modelsManager->executeQuery($integraSql);
                            if($integraData->success() == false){
                                $this->log('推荐者积分记录更新失败'.$countIntegral);
                                $this->db->rollback();
                                return $this->responseJson([],10025);
                            }
                        }
                        //给购买者返积分
                        if(!empty($con_integral)){
                            $userIntegral = round($totalPrice * ($con_integral / 100));//计算要返的积分
                            $countIntegral = $userInfo->integral + $userIntegral;//总的+要返的积分
                            //更新当前会员积分
                            $userInfo->integral = $countIntegral;
                            if($userInfo->save() == false){
                                $this->log('购买者返积分'.$countIntegral);
                                $this->db->rollback();
                                return $this->responseJson([],10025);
                            }
                            //添加积分消费记录
                            $values = $userid.',"购买产品返积分",0,'.$userIntegral.','.$countIntegral.','.time();
                            $integraSql = 'insert into App\Models\Integral (userid,title,type,integral,totalIntegral,addTime) VALUES ('.$values.')';
                            $integraData = $this->modelsManager->executeQuery($integraSql);
                            if($integraData->success() == false){
                                $this->log('购买者返积分记录'.$countIntegral);
                                $this->db->rollback();
                                return $this->responseJson([],10025);
                            }
                        }

                        //给同城加盟店返现
                        if(!empty($fran_cash)){
                            $userBalance = round($totalPrice * ($fran_cash / 100),2);
                            //根据省份和城市筛选加盟店
                            if(!empty($city)){
                                $franchise = Franchise::findFirst('province = "'.$province.'" and city = "'.$city.'"');
                                if(!empty($franchise->id)){
                                    $franchise->balance = $franchise->balance + $userBalance;
                                    if($franchise->save() == false){
                                        $this->db->rollback();
                                        return $this->responseJson([],10025);
                                    }else{
                                        $this->log('加盟店余额更新：'.$userBalance);
                                    }

                                    $oids = 'FX'.dechex(date('ymdHis').rand(10,99).rand(10,99));//暂时没意思
                                    $intPayLogSql = 'insert into App\Models\PayLog (userid,fancheise_id,type,AddSub,ordercode,title,totalprice,state,addTime) VALUES ('.$orderD->userid.','.$franchise->id.',2,0,"'.$oids.'","同城购买返现",'.number_format($totalPrice,2).',1,'.time().')';
                                    $isPayLogSql = $this->modelsManager->executeQuery($intPayLogSql);
                                    if($isPayLogSql->success() == false){
                                        $this->db->rollback();
                                        return $this->responseJson([],10025);
                                    }else{
                                        $this->log('加盟店余额更新记录：'.$userBalance);
                                    }
                                }
                            }

                        }
                    }
                }
            }
            if($state == 4){//申请退款时需要向支付表里加记录
                $intPayLogSql = 'insert into App\Models\PayLog (userid,type,AddSub,ordercode,title,totalprice,vstate,state,addTime) VALUES ('.$this->userid.',0,0,"'.$oid.'","商城购买",'.$totalPrice.',4,1,'.time().')';
                $isPayLogSql = $this->modelsManager->executeQuery($intPayLogSql);

                if($isPayLogSql->success() == false){
                    $this->db->rollback();
                    return $this->responseJson([],10032);
                }
            }
            if($state == 6) {//申请退款时需要向支付表里加记录
                $goodsD = OrderGoods::find('ordercode = "'.$oid.'"');
                foreach($goodsD as $value){
                    $orderGoodsSql = 'update App\Models\Goods set goods_number =  goods_number + '.$value->num.' where goods_id ='.$value->goods_id;
                    $this->modelsManager->executeQuery($orderGoodsSql);
                }
            }

            //记录订单状态改变
            $orderlog = OrderLog::findFirst(' ordercode = "' . $oid . '" and state = ' .$state );
            if(empty($orderlog->id)){
                $intOrderLogSql = 'insert into App\Models\OrderLog (ordercode,state,btime,mess) VALUES ("'.$oid.'",'.$state.','.time().',"'.$mess.'")';
                $isOrderLog = $this->modelsManager->executeQuery($intOrderLogSql);
                if($isOrderLog->success() == false){
                    $this->db->rollback();
                    return $this->responseJson([],10032);
                }
            }else{
                return $this->responseJson([],10026);
            }

            $this->db->commit();
            return $this->responseJson([],0);

        }
    }

    public function changeOrderAction(){
        if($this->request->isPost()){

        }
    }

    /**生成订单号
     * @param $oid
     */
    private function createOrderCode($oid){
        $orders = Order::count('ordercode = "' . $oid . ' "');
        if($orders){
            $oid = 'SS' . date('YmdHis');
            return $this->createOrderCode($oid);
        }else{
            return $oid;
        }
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
        header("Access-Control-Allow-Origin: *");
        header('Access-Control-Allow-Methods: GET, POST');
        //if(!$this->request->isPost()){
        //    return $this->responseJson([],10023);
        //}
        //判断会员是否登录
        if(empty($this->userid)){
            return $this->responseJson([],10016);
        }
        //获取商品id
        $goods_id = $this->request->get('goods_id','string','');
        $goods_id = str_replace('&#34;','',$goods_id);
        if(empty($goods_id)){
            return $this->responseJson([],10001);
        }
        //获取收货地址id
        $uaddid = $this->request->get('collid','int',0);
        if(empty($uaddid)){
            return $this->responseJson([],10018);
        }
        //获取快递费
        $disPrice = $this->request->get('disPrice','string','');
        //购买数量
        $num = $this->request->get('num','string','');
        $num = str_replace('&#34;','',$num);//替换实体引号
        //获取买家留言
        $message = $this->request->get('mess','string','');
        //获取收货地址
        $uaddress = UAddress::findFirst(' userid = ' . $this->userid . ' and id = '.$uaddid);
        if(empty($uaddress->id)){
            return $this->responseJson([],10018);
        }
        //支付参数
        $jsapi = '';
        if(strpos($goods_id,',') !== false){
            $goods_ids = json_decode($goods_id);
            $nums = json_decode($num);

            $orderCollid = [];
            $masPrice = 0; //总价
            $goods_idStr = implode(',',$goods_ids);
            $disPriceo = $this->getdisPrice($goods_idStr);//计算平均快递费
            $disPrice = empty($disPrice) ? $disPrice : $disPriceo;
            $body = '';//记录订单号
            $is_ss = false;//判断商品是不是一个仓库的
            //$ss_price = 0; //判断多个仓库多个商品时一个仓库多个商品，统计当前订单的总价
            //主订单
            $masterorder = 'SS'.date('YmdHis').$this->get_millisecond().$this->userid;
            foreach($goods_ids as $key=>$value) {
                //获取商品信息
                $goods = Goods::findFirst(' is_integral = 0 and  is_delete =0 and is_on_sale = 1 and goods_id = ' . $value);
                if (empty($goods->goods_id)) {
                    return $this->responseJson([], 10038);
                }
                $numk = empty($nums[$key]) ? 1 : $nums[$key]; //获取商品数量
                $currPrice = $numk * $goods->shop_price;//获取当前产品的总价
                if ($numk > $goods->goods_number) {
                    return $this->responseJson([], 10027);
                }//10039
                if (empty($goods->goods_number)) {
                    return $this->responseJson([], 10039);
                }
                //判断是否是一个仓库
                if (array_key_exists($goods->depot_id, $orderCollid)) {
                    //获取同一个仓库订单号
                    $oid = $orderCollid[$goods->depot_id];
                    $is_ss = true;
                    $orderCollid['price'.$goods->depot_id] +=  $currPrice;
                } else {
                    //生成订单号
                    $oid = $this->createOrderCode('SS' . date('YmdHis') . $this->get_millisecond() . $goods->depot_id);
                    $orderCollid[$goods->depot_id] = $oid;
                    $orderCollid['price' . $goods->depot_id] = $currPrice;
                    $is_ss = false;
                }
                $this->createOrder($oid,$goods,$uaddress,$numk,$disPrice,$message,$masterorder,$is_ss);
                //计算一个仓库多个商品时，主订单的总价
                $masPrice = $orderCollid['price'.$goods->depot_id] + $disPriceo;
                if($is_ss){
                    $this->modelsManager->executeQuery('update App\Models\Order set totalPrice = '.$masPrice.' where ordercode= "'.$oid .'" ');
                }
                $body .= $oid . ',';
            }
            $totalPrices = Order::sum(array(
                "column"     => "totalPrice",
                "conditions" => 'masterorder = "'.$masterorder.'"'
            ));
            //预支付
            $fee = $totalPrices; //商品加+快递费用等于要支付的总费用
            $goodstag = $masterorder;
            $body = '主订单'.$masterorder.',子订单'.$body;
            $attach = $masterorder;
            $notiyurl = "http://" . $this->request->getHttpHost();
            $notiyurl .= $this->url->get('wap/api/wxjspaynotify');
            $openid = $this->session->get('openid');
            //echo $oid,$fee,$notiyurl,$openid,$body,$attach,$goodstag;exit;
            $jsapi =$this->weixin->wxpay()->wxpay($masterorder,$fee,$notiyurl,$openid,$body,$attach,$goodstag);
            $jsapi['parameters']['oid'] = $masterorder;
            return $this->responseJson($jsapi,0);
        }else{
            //获取商品信息
            $goods_id = json_decode($goods_id)[0];
            $num = json_decode($num)[0];

            $goods = Goods::findFirst(' is_integral = 0 and  is_delete =0 and is_on_sale = 1 and goods_id = '. $goods_id);
            if(empty($goods->goods_id)){
                return $this->responseJson([],10001);
            }
            //生成订单号
            $oid = $this->createOrderCode('SS'.date('YmdHis').$this->userid);
            $num = empty($num)? 1 : $num;
            if($num > $goods->goods_number){
                return $this->responseJson([],10027);
            }
            $this->createOrder($oid,$goods,$uaddress,$num,$disPrice,$message,$oid);
            //预支付
            $fee = $goods->shop_price * $num + $disPrice;
            $goodstag = $oid;
            $body =$goods->goods_name;
            $attach = $oid;
            $notiyurl = "http://" . $this->request->getHttpHost();
            $notiyurl .= $this->url->get('wap/api/wxjspaynotify');
            $openid = $this->session->get('openid');
            //echo $oid,$fee,$notiyurl,$openid,$body,$attach,$goodstag;exit;
            $jsapi =$this->weixin->wxpay()->wxpay($oid,$fee,$notiyurl,$openid,$body,$attach,$goodstag);
            $jsapi['parameters']['oid'] = $oid;
            return $this->responseJson($jsapi,0);
        }
        return $this->responseJson($jsapi,10023);
    }

    /**把数据添加到订单表里
     * @param $oid 订单号
     * @param $goods 商品对象
     * @param $uaddress 地址对象
     * @param $num 购买数量
     * @param $disPrice 快递费
     * @param $message 买家留言
     * @param string $masterorder 主订单号
     * @param bool|false $is_ss 是否是一库多商品,是的话只更新商品表
     * @return mixed
     */
    private function createOrder($oid,$goods,$uaddress,$num,$disPrice,$message,$masterorder = '',$is_ss = false){
        $this->db->begin();
        $totalPrice = $num * $goods->shop_price;
        if(empty($totalPrice) || intval($totalPrice) < 0){
            return $this->responseJson([],10028);
        }
        if(!$is_ss) {
            //更新订单信息表
            $order = new Order();
            $order->userid = $this->userid;
            $order->ordercode = $oid;
            $order->masterorder = $masterorder;
            $order->type = 0;
            $order->depotid = $goods->depot_id;
            $order->btime = time();
            $order->vstate = 0;
            $order->collid = $uaddress->id;
            $order->mess = $message;
            $order->distribution = '普通快递';
            $order->totalPrice = $totalPrice + $disPrice;
            $order->integral = 0;
            $order->disPrice = $disPrice;
            $order->payment = 1;
            $order->vname = $uaddress->vname;
            $order->province = $uaddress->province;
            $order->city = $uaddress->city;
            $order->tel = $uaddress->tel;
            $order->address = $uaddress->address;
            if ($order->create() == false) {
                $this->db->rollback();
                return $this->responseJson([], 10021);
            }
            //记录订单状态
            $orderlog = new OrderLog();
            $orderlog->ordercode = $oid;
            $orderlog->state = 0 ;
            $orderlog->btime = time();
            $orderlog->mess = $message;
            if($orderlog->create() == false){
                $this->db->rollback();
                return $this->responseJson([],10025);
            }
            //添加消费记录
            /*$paylog = new PayLog();
            $paylog->userid = $this->userid;
            $paylog->type = 0;
            $paylog->AddSub = 1;
            $paylog->ordercode = $oid;
            $paylog->title = '购买商品！';
            $paylog->totalprice = $totalPrice;
            $paylog->addTime = time();
            if($paylog->create() == false){
                $this->db->rollback();
                return $this->responseJson([],10029);
            }*/
        }
        //订单附加商品信息表
        $orderGoods = new OrderGoods();
        $orderGoods->ordercode = $oid;
        $orderGoods->goods_id = $goods->goods_id;
        $orderGoods->goods_name = $goods->goods_name;
        $orderGoods->goods_introduction = $goods->good_introduction;
        $orderGoods->goods_price = $goods->shop_price;
        $orderGoods->market_price = $goods->market_price;
        $orderGoods->con_integral = $goods->con_integral;
        $orderGoods->fran_cash = $goods->fran_cash;
        $orderGoods->ref_integral = $goods->ref_integral;
        $orderGoods->num = $num;
        $orderGoods->totalPrice = $totalPrice;
        $orderGoods->btime = time();
        if($orderGoods->create()==false){
            $this->db->rollback();
            return $this->responseJson([],10022);
        }
        //更新商品库存
        $goodsinfo = Goods::findFirst('goods_id = '.$goods->goods_id);
        $goodsinfo->goods_number = $goodsinfo->goods_number - $num;
        if($goodsinfo->save() == false){
            $this->db->rollback();
            return $this->responseJson([],10022);
        }
        $this->db->commit();
    }

    public function memOrderListPayAction(){
        header("Access-Control-Allow-Origin: *");
        header('Access-Control-Allow-Methods: GET, POST');
        //if(!$this->request->isPost()){
        //    return $this->responseJson([],10023);
        //}
        //判断会员是否登录
        if(empty($this->userid)){
            return $this->responseJson([],10016);
        }
        //获取商品id
        $oid = $this->request->get('oid','string','');
        $moid = $this->request->get('moid','string','');

        $orderData = '';
        if(!empty($oid)){
            $phql = "SELECT o.ordercode,o.masterorder,o.totalPrice,g.goods_id,g.goods_name FROM App\Models\Order AS o left JOIN App\Models\OrderGoods as g  on o.ordercode=g.ordercode where o.ordercode ='".$oid."' and o.vstate = 0  ";
            $orderDatas = $this->modelsManager->executeQuery($phql);
            foreach($orderDatas as $value){
                $orderData->totalPrice = $value->totalPrice;
                $orderData->goods_name = $value->goods_name;
                $orderData->ordercode = $value->ordercode;
                $orderData->masterorder = $value->masterorder;
            }
            if(($orderData->ordercode != $orderData->masterorder) || empty($orderData)){
                return $this->responseJson([],10024);
            }
        }
        if(!empty($moid)){
            $orderDatas = Order::sum(array(
                "column"     => "totalPrice",
                "conditions" => 'masterorder = "'.$moid.'" and vstate = 0'
            ));
            $orderData->totalPrice = $orderDatas;
            $orderData->goods_name = 'masterorder ='.$moid;
            $oid = $moid;
        }
        if(empty($orderData)){
            return $this->responseJson([],10024);
        }
        //$orderData = Order::find('ordercode = "'.$oid.'" or masterorder = "'.$oid.'"');


        //预支付
        $fee = $orderData->totalPrice;
        $goodstag = $oid;
        $body =$orderData->goods_name;
        $attach = $oid;
        $notiyurl = "http://" . $this->request->getHttpHost();
        $notiyurl .= $this->url->get('wap/api/wxjspaynotify');
        $openid = $this->session->get('openid');
        //echo $oid,$fee,$notiyurl,$openid,$body,$attach,$goodstag;exit;
        $jsapi =$this->weixin->wxpay()->wxpay($oid,$fee,$notiyurl,$openid,$body,$attach,$goodstag);
        $jsapi['parameters']['oid'] = $oid;
        return $this->responseJson($jsapi,0);
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
    public function wxjspayAction($package){
        $ordercode='SS20170121032323';
        $fee = 3;
        $goodstag = 'SS20170121032323';
        if(empty($ordercode)||empty($fee)){
            return $this->responseJson($package,1007);
        }
        $body ='SS20170121032323';
        $attach = $ordercode;
        $notiyurl = "http://" . $this->request->getHttpHost();
        $notiyurl .= $this->url->get('wap/api/wxjspaynotify');
        $openid = 'oObQqwjCmKbgjvTkUGf66k7cQNVI';//$this->session->get('openid');
        $resp =$this->weixin->wxpay()->wxpay($ordercode,$fee,$notiyurl,$openid,$body,$attach,$goodstag);
        print_r($resp);exit;
        return $this->responseJson($resp,$resp['status']);
    }
    /*
       功能描述:订单状态修改
       URL：http://[地址]/api/paystatemodify
       入参:
           ordercode    订单号
           vstate  订单支付状态:0.订单生成,1.订单已支付成功,2.订单支付失败3.订单取消,4.申请退款 5.已退款
           errdsp  订单支付错误信息
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
        $xml = $this->request->getRawBody();
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $datas = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        $resp =$this->execsql($datas, 'p_pay_wxnotify_add');
        $oid = $datas["out_trade_no"];
        if(strpos($oid,'SM') === false){
            $order = Order::find(' ordercode = "'.$oid.'" or masterorder = "'.$oid.'"');
            if($order->count() > 0){
                $this->modelsManager->executeQuery('update App\Models\Order set vstate = 1 where masterorder= "'.$oid.'" ');
            }
            foreach($order as $value){
                //添加订单状态改变记录
                $intOrderLogSql = 'insert into App\Models\OrderLog (ordercode,state,btime) VALUES ("'.$value->ordercode.'",1,'.time().')';
                $this->modelsManager->executeQuery($intOrderLogSql);
                //添加订单支付记录
                $intPayLogSql = 'insert into App\Models\PayLog (userid,type,AddSub,ordercode,title,totalprice,state,addTime) VALUES ('.$value->userid.',0,1,"'.$value->ordercode.'","商城购买",'.$value->totalPrice.',1,'.time().')';
                $this->modelsManager->executeQuery($intPayLogSql);
            }
        }else{
            //根据返回订单号获取支付记录里支付金额
            $payLog = PayLog::findFirst('ordercode = "'.$oid.'"');
            $franchise = Franchise::findFirst('id = '.$payLog->fancheise_id);
            if(!empty($franchise->id)) {
                $userBalance = number_format($payLog->totalprice * (8 / 100),2); //计算返现
                $franchise->balance = number_format($franchise->balance + $userBalance,2);
                $franchise->save();
            }
            $this->modelsManager->executeQuery('update App\Models\PayLog set state = 1 where ordercode= "'.$oid.'" ');

            /*//返给购买者积分
            $userInfo = MemberInfo::findFirst('userid = '.$payLog->userid);//当前用户的信息
            $userIntegral = round($payLog->totalprice * (8 / 100));//计算要返的积分
            $countIntegral = $userInfo->integral + $userIntegral;//总的+要返的积分
            //更新当前会员积分
            $userInfo->integral = $countIntegral;
            $userInfo->save();
            //添加积分消费记录
            $values = $payLog->userid.',"扫码返积分",0,'.$userIntegral.','.$countIntegral.','.time();

            $integraSql = 'insert into App\Models\Integral (userid,title,type,integral,totalIntegral,addTime) VALUES ('.$values.')';
            //$this->log($integraSql);
            $integraData = $this->modelsManager->executeQuery($integraSql);
            $integraData->success();*/
        }
        $OK = 'OK';
        $content="<xml><return_code><![CDATA[".$datas['result_code']."]]></return_code><return_msg><![CDATA[".$OK."]]></return_msg></xml>";
        return $this->response->setContent($content)->setContentType("text/xml");
    }
    //用于扫码支付异步通知(暂没用)
    public function memOrderpaynotifyAction(){
        $xml = $this->request->getRawBody();
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $datas = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        $resp =$this->execsql($datas, 'p_pay_wxnotify_add');
        $order = Order::findFirst(' ordercode = "'.$datas["out_trade_no"].'"');
        if(!empty($order->id) && empty($order->vstate)){
            $this->modelsManager->executeQuery('update App\Models\Order set vstate = 1 where ordercode= "'.$datas["out_trade_no"] .'" ');
        }
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

    //加盟店扫码支付
    public function wxFranchiseAction(){
        header("Access-Control-Allow-Origin: *");
        header('Access-Control-Allow-Methods: GET, POST');
        //if(!$this->request->isPost()){
        //    return $this->responseJson([],10023);
        //}
        //判断会员是否登录
        if(empty($this->userid)){
        	return $this->responseJson([],10016);
        }
        //获取加盟店id
        $franchise_id = $this->session->get('napaid');
        $franchise_id = empty($franchise_id) ? $this->request->get('franchise_id','int',0): $franchise_id;
        if(empty($franchise_id)){
            return $this->responseJson([],10036);
        }
        $francheisData = Franchise::findFirst($franchise_id);

        //获取支付价格
        $price = $this->request->get('price');
        if(empty($price)){
             return $this->responseJson([],10037);
        }
        $order = 'SM'.dechex(date('ymdHis').rand(10,99).rand(10,99));
        //添加消费记录
        $paylog = new PayLog();
        $paylog->userid = $this->userid;
        $paylog->fancheise_id = $franchise_id;
        $paylog->type = 1;
        $paylog->AddSub = 1;
        $paylog->ordercode = $order;
        $paylog->title = $francheisData->title.'支付';
        $paylog->totalprice = $price;
        $paylog->addTime = time();
        if($paylog->create() == false){
            return $this->responseJson([],10029);
        }
        //预支付
        $fee = $price; //商品加+快递费用等于要支付的总费用
        $goodstag = '扫码支付';
        $body = '扫码支付';
        $attach = time();
        $notiyurl = "http://" . $this->request->getHttpHost();
        $notiyurl .= $this->url->get('wap/api/wxjspaynotify');
        $openid = $this->session->get('openid');
        $jsapi =$this->weixin->wxpay()->wxpay($order,$fee,$notiyurl,$openid,$body,$attach,$goodstag);
        return $this->responseJson($jsapi,0);
    }
    //根据接口改订单状态时，限制
    private function getBoolState($state,$vstate){
        $statebool = true;
        //订单处于待付款状态时，改变订单状态只能是付款和关闭
        if(empty($vstate)){
            $statebool = $state == 1 || $state == 6 || $state == 7 ? true : false ;
        }elseif($vstate == 1){ //订单付款状态时可以改变订单状态为退款中
            $statebool = $state == 4 ? true : false ;
        }elseif($vstate == 2){//订单状态为待收货状态时可以改变订单为已完成
            $statebool = $state == 3 ? true : false ;
        }
        return $statebool;
    }
    //订单状态
    private function getState($sate){
        $strState = '待支付';
        switch($sate){
            case 1:
                $strState = '待发货';
                break;
            case 2:
                $strState = '待收货';
                break;
            case 3:
                $strState = '交易完成';
                break;
            case 4:
                $strState = '退款中';
                break;
            case 5:
                $strState = '已退款';
                break;
            case 6:
                $strState = '交易关闭';
                break;
        }
        return $strState;
    }
    //获取商品运费平均值
    public function getAvgPriceAction(){
        header("Access-Control-Allow-Origin: *");
        header('Access-Control-Allow-Methods: GET');
        $goods_id = $this->request->get('goods_id','string','');
        if(empty($goods_id)){
            return $this->responseJson([],10001);
        }
        $goods_id = str_replace('&#34;','',$goods_id);
        $goods_ids = trim($goods_id,'] [');

        $depot_ids = $this->modelsManager->executeQuery('select depot_id from App\Models\Goods where goods_id in('. $goods_ids .') ');
        $depot_idsstr = '';
        foreach($depot_ids as $value){
            if(strpos($depot_idsstr,$value->depot_id) === false){
                $depot_idsstr .= ','.$value->depot_id;
            }
        }
        $depot_ida = ltrim($depot_idsstr,',');
        $disPrice = Depot::average(array(
            "column"     => "freight",
            "conditions" => 'id in('.$depot_ida.')'
        ));
        $depotNum = count(explode(',',$depot_ida));
        if($depotNum > 1){
            $disPrice = $disPrice * $depotNum;
        }
        return $this->responseJson(['freight' => $disPrice],0);
    }
    //评价接口
    public function assessAction(){
        header("Access-Control-Allow-Origin: *");
        header('Access-Control-Allow-Methods: GET, POST');
        //获取列表
        if($this->request->isGet()){
            $goods_id = $this->request->get('goods_id', "int", 0);
            $page = $this->request->get('page', "int", 1);
            $total = $this->request->get('total', "int", 10);

            $starNum = empty($page) || $page == 1 ? 0 : ($page-1) * $total;

            //判断商品是否传值
            if(empty($goods_id)){
                return $this->responseJson([],10001);
            }
            $where = ' goods_id = '. $goods_id;
            //'selectf a.id,m.name,a.num,a.mess from App\Models\Assess left join App\Models\';
            //$phql = "SELECT c.id,c.userid,c.g_id,c.addTime,g.goods_name,g.goods_number,g.good_introduction,g.shop_price,g.market_price,g.is_integral FROM App\Models\Collection AS c left JOIN App\Models\Goods as g  on c.g_id=g.goods_id where $where ORDER BY c.id DESC limit $starNum,$total";
            //$collectionDatas = $this->modelsManager->executeQuery($phql);

            $assessData = Assess::find(array(
                'conditions' => $where,
                'order' => 'id desc',
                'limit' => ['number' => $total , 'offset' => $starNum]
            ));

            $assessDatas = [];
            foreach ($assessData as $value) {
                $memberInfo = $this->getMemberInfo($value->userid);
                $assessDatas[] = array(
                    'nick' => $memberInfo['nickname'],
                    'headurl' => $memberInfo['headimgurl'],
                    'num' => $value->num,
                    'mess' => $value->mess,
                    'addTime' => $value->addTime,
                );
            }
            $data['assessData'] = $assessDatas;
            return $this->responseJson($data,0);
        }
        //新增一个评价
        if($this->request->isPost()){
            $oid = $this->request->getQuery("oid",'string',0);
            $goods_id = $this->request->getQuery("goods_id",'int',0);
            $num = $this->request->getQuery("num",'int',0);
            $mess = $this->request->getQuery("mess",'string','');

            //判断会员是否登录
            if(empty($this->userid)){
                return $this->responseJson([],10016);
            }
            if(empty($oid)){
                return $this->responseJson(array(),10024);
            }

            if(empty($goods_id)){
                return $this->responseJson(array(),10035);
            }
            //获取订单信息
            $this->db->begin();
            $phql = "SELECT o.ordercode,o.masterorder,o.userid,o.totalPrice,o.vstate,o.depotid,o.vname,o.tel,o.province,o.city,o.address,o.distribution,o.logisticsnum,o.disPrice,o.is_evaluation,g.goods_id,g.goods_name,g.num,g.goods_price FROM App\Models\Order AS o left JOIN App\Models\OrderGoods as g  on o.ordercode=g.ordercode where vstate = 3 and  (o.ordercode ='".$oid."' or o.masterorder = '".$oid."') and g.goods_id = ".$goods_id;

            $orderDatas = $this->modelsManager->executeQuery($phql);

            if($orderDatas->count() <= 0){
                return $this->responseJson(array(),10035);
            }
            $ordercodeStr = '';
            foreach($orderDatas as $key =>$value){
                //是否已经评价过
                if($value->is_evaluation){
                    return $this->responseJson(array(),10034);
                }
                $assess = Assess::findFirst(' userid = ' . $this->userid . ' and ordercode = "' . $oid . '" and goods_id ='.$goods_id);
                if (empty($assess->id)) {
                    $assesss = new Assess();
                    $assesss->ordercode = $value->ordercode;
                    $assesss->userid = $this->userid;
                    $assesss->goods_id = $value->goods_id;
                    $assesss->num = $num;
                    $assesss->mess = $mess;
                    $assesss->addTime = time();
                    if($assesss->save() == false){
                        $this->db->rollback();
                        return $this->responseJson(array(),10032);
                    }else{
                        $ordercodeStr .= ','.$value->ordercode;
                    }
                }else{
                    return $this->responseJson(array(),10034);
                }
            }
            $this->db->commit();
            //修改订单评价状态
            $oidStr = ltrim($ordercodeStr,',');
            $oidStr = implode('","',array_unique(explode(',',$oidStr)));
            $this->modelsManager->executeQuery('update App\Models\Order set is_evaluation = 1 where ordercode in ("'.$oidStr.'") ');
            /*$orderData->is_evaluation = 1;
            if($orderData->save() == false){
                $this->db->rollback();
                return $this->responseJson(array(),10033);
            }*/
            return $this->responseJson(array(),0);
        }
    }

    /** 获取会员中心支付记录
     * @return mixed
     */
    public function payOrderLogAction(){
        header("Access-Control-Allow-Origin: *");
        header('Access-Control-Allow-Methods: GET');

        //判断会员是否登录
        if(empty($this->userid)){
            return $this->responseJson([],10016);
        }
        if($this->request->isGet()){
            $page = $this->request->get('page', "int", 1);
            $total = $this->request->get('total', "int", 10);

            $starNum = empty($page) || $page == 1 ? 0 : ($page-1) * $total;
            //$fields = ['id','userid','g_id','addTime'];
            $where = ' fancheise_id = 0 and userid = ' . $this->userid;

            $payLogDatas = PayLog::find(array(
                'conditions' => $where,
                'order' => 'id desc',
                'limit' => ['number' => $total , 'offset' => $starNum]
            ));


            $paylogOrderDatas = [];
            foreach ($payLogDatas as $value) {
                $totalprice = empty($value->totalprice) ? 0 : $value->totalprice;
                $paylogOrderDatas[] = array(
                    'ordercode' => $value->ordercode,
                    'title' => $value->title,
                    'totalprice' => (empty($value->AddSub)? '+':'-').number_format($totalprice,2),
                    'state' => $this->getReturnState($value->vstate,$value->type),
                    'addTime' => date('Y-m-d H:i:s',$value->addTime)

                );
            }
            $data['paylogOrderDatas'] = $paylogOrderDatas;
            return $this->responseJson($data,0);
        }
    }

    //商品点击
    public function clickGoodsAction(){
        header("Access-Control-Allow-Origin: *");
        header('Access-Control-Allow-Methods: POST');
        if($this->request->isPost()){
            $goods_id = $this->request->getQuery("goods_id",'int',0);
            if(empty($goods_id)){
                return $this->responseJson(array(),10035);
            }
            //获取订单信息

            $this->modelsManager->executeQuery('update App\Models\Goods set click_count = click_count + 1  where goods_id = '.$goods_id);
            return $this->responseJson(array(),0);
        }
    }

    public function clearAction(){
          print_r($_SESSION);
//        $collectionDatas = $this->modelsManager->executeQuery('delete from App\Models\OrderGoods');
//        $collectionDatas = $this->modelsManager->executeQuery('delete from App\Models\PayLog');
//        $collectionDatas = $this->modelsManager->executeQuery('delete from App\Models\Order');
//        $collectionDatas = $this->modelsManager->executeQuery('delete from App\Models\OrderLog');
    }

     /*
     * microsecond 微秒     millisecond 毫秒
     *返回时间戳的毫秒数部分
     */
    private function get_millisecond()
    {
        list($usec, $sec) = explode(" ", microtime());
        $msec=round($usec*1000);
        return $msec;

    }
    //根据商品id获取价格+平均快递费用
    public function getfsPriceAction(){
        header("Access-Control-Allow-Origin: *");
        header('Access-Control-Allow-Methods: GET');

        if($this->request->isGet()){
            $goods_id = $this->request->getQuery("goods_id");
            $nums = $this->request->getQuery("num");
            $strSql = $disSql = '';
            if(is_array($goods_id)){
                $strSql = ' goods_id in ('. implode(',', $goods_id).')';
                $disSql = implode(',', $goods_id);
                $orderby = 'field(goods_id,'.implode(',', $goods_id).')';
            }else{
                $strSql = 'goods_id in ('.$goods_id.')';
                $disSql = $goods_id;
                $orderby = 'id desc';
            }
            $disPrice = $this->getdisPrice($disSql);
            $goodsData =Goods::find(['conditions' => $strSql,'order' => $orderby]);
            $shopData = [];
            $shopPriece = 0;
            foreach($goodsData as $key=>$value){
                $shopPriece += $value->shop_price * $nums[$key];
                $shopData['goodsData'][] = ['goods_id'=>$value->goods_id,'shop_price'=>number_format($value->shop_price,2),'totalPrice'=>number_format($value->shop_price * $nums[$key],2)];
            }
            $shopData['disPrice'] = number_format($disPrice,2);
            $shopData['totalPrice'] = number_format($shopPriece,2);
        }

        return $this->responseJson($shopData,0);
    }
    //获取快递费
    private function getdisPrice($goods_id){
        $depot_ids = $this->modelsManager->executeQuery('select depot_id from App\Models\Goods where goods_id in('. $goods_id .') ');
        $depot_idsstr = '';
        foreach($depot_ids as $value){
            if(strpos($depot_idsstr,$value->depot_id) === false){
                $depot_idsstr .= ','.$value->depot_id;
            }
        }
        $depot_ida = ltrim($depot_idsstr,',');
        $disPrice = Depot::average(array(
            "column"     => "freight",
            "conditions" => 'id in('.$depot_ida.')'
        ));
        return $disPrice;
    }
    //获取一单多商品列表
    private function getGoodsData($oid){

        $orderGoods = OrderGoods::find(array(
            "conditions" => 'ordercode = "'.$oid.'" '
        ));
        $num = 0;
        foreach($orderGoods as $value) {
            $num += $value->num;
            $goodsData[] = ['goods_id' => $value->goods_id,'goods_name' => $value->goods_name,'goods_introduction' => explode(',',str_replace('"','',trim($value->goods_introduction,'"] ["'))),'market_price' => $value->market_price,'goods_price' => $value->goods_price,'num' => $value->num];
        }
        $goodsData['num'] = $num;
        return $goodsData;
    }

    private function getOrderData($where,$starNum,$total,$page){
        static $orderD = array();
        static $mastOidData = array();
        static $isloop = 0;
        $order = Order::find(array(
             'conditions' => $where,
             'order' => 'id desc',
             'limit' => ['number' => $total , 'offset' => $starNum]
        ));
        foreach ($order as $value) {
            $ordercode = $value->ordercode;
            $goods_data = $this->getGoodsData($ordercode);
            if($order->count() > 1){
                $priceData = $this->getTotalPrice($value->masterorder,1);
            }else{
                $priceData = $this->getTotalPrice($ordercode,2);
            }
            $num = $goods_data['num'];
            unset($goods_data['num']);
            $orderD[] = array(
                'ordercode' => $ordercode,
                'state' => $value->vstate,
                'type' => $value->type,
                'goods_data' => $goods_data,
                'num' => $num,
                //'disPrice' => $priceData['disPrice'],
                'disPrice' => number_format($value->disPrice,2),
                //'totalPrice' => $priceData['totalPrice'],
                'totalPrice' => number_format($value->totalPrice,2),
                'integral' => $value->integral,
                'is_evaluation' => $value->is_evaluation,
                'mess' => $value->mess,
                'distribution' => $value->distribution
            );
        }
        return  $orderD;
    }
    //根据订单号获取总价格
    private function getTotalPrice($mastOrderId,$type){
        if($type == 1){
            $countPrice = Order::find('masterorder = "'.$mastOrderId.'"');
        }else{
            $countPrice = Order::find('ordercode = "'.$mastOrderId.'"');
        }

        $totalPrice = $disPrice = 0;
        foreach($countPrice as $value){
            //计算总订单价格
            //$totalPrice += $value->totalPrice;
            //$disPrice += $value->disPrice;

            //计算单订单价格
            $totalPrice = $value->totalPrice;
            $disPrice = $value->disPrice;
        }
        return ['totalPrice'=>$totalPrice,'disPrice'=>$disPrice];
    }
    //获取会员的相关信息
    private function getMemberInfo($userid){
        $data = [];
        if(!empty($userid)){
            $mdata = MemberInfo::findFirst('userid = '. $userid);
            $data['userid'] = $userid;
            $data['nickname'] = $mdata->nick;
            $data['headimgurl'] = $mdata->headurl;
        }
        return $data;
    }

    private function getStateTitle($sate){
        $strState = '提交时间';
        switch($sate){
            case 1:
                $strState = '付款时间';
                break;
            case 2:
                $strState = '发货时间';
                break;
            case 3:
                $strState = '成交时间';
                break;
            case 4:
                $strState = '申请退款时间';
                break;
            case 5:
                $strState = '退款成功时间';
                break;
            case 6:
                $strState = '交易关闭时间';
                break;
        }
        return $strState;
    }
    //获取仓库名称
    private function getfancheiseInfo($id){
        $strs = '';
        if(!empty($id)){
            $strs = Franchise::findFirst('id =' . $id)->title;
        }
        return $strs;
    }
    //处理支付记录里支付状态
    private function getReturnState($state,$type){
        $str = '';
        if(empty($type)){
            if($state == 4){
                $str = '退款中';
            }elseif($state == 5){
                $str = '退款成功';
            }elseif($state == 6){
                $str = '退款失败';
            }
        }
        return $str;
    }
    //根据商品id获取销量
    private function getGoodsNum($goods_id){
        $num = 0;
        if(empty(!$goods_id)){
            $phql = "SELECT sum(g.num) num FROM App\Models\OrderGoods AS g left JOIN App\Models\Order as o on o.ordercode=g.ordercode where g.goods_id ='".$goods_id."' and o.vstate in(1,2,3)";
            $orderDatas = $this->modelsManager->executeQuery($phql);
            foreach($orderDatas as $value){
                $num = $value->num;
            }
        }
        return $num;
    }

    //根据商品id获取标题
    private function getGoodsName($goods_id){
        $goods_name = 0;
        if(empty(!$goods_id)){
            $phql = "SELECT goods_name FROM App\Models\Goods where goods_id =".$goods_id;
            $orderDatas = $this->modelsManager->executeQuery($phql);
            foreach($orderDatas as $value){
                $goods_name = $value->goods_name;
            }
        }
        return $goods_name;
    }
    //多维数组排序
    function sortArrByManyField(){
        $args = func_get_args();
        if(empty($args)){
            return null;
        }
        $arr = array_shift($args);
        if(!is_array($arr)){
            throw new Exception("第一个参数不为数组");
        }
        foreach($args as $key => $field){
            if(is_string($field)){
                $temp = array();
                foreach($arr as $index=> $val){
                    $temp[$index] = $val[$field];
                }
                $args[$key] = $temp;
            }
        }
        $args[] = &$arr;//引用值
        call_user_func_array('array_multisort',$args);
        return array_pop($args);
    }
    //定时任务，每天凌晨执行
    public function exOrderAction()
    {
        header("Access-Control-Allow-Origin: *");
        header('Access-Control-Allow-Methods: POST');
        $currTime = strtotime('- 1 day');
        $orderData = Order::find(' vstate = 0 and btime < '.$currTime);
        $ordercodes = [];
        foreach($orderData as $value){
            $ordercodes[] = $value->ordercode;
            //回滚商品库存
            $this->retGoodsNum($value->ordercode);
        }
        $this->modelsManager->executeQuery('update App\Models\Order set vstate = 6 where ordercode in ("'.implode('","',$ordercodes).'") ');
    }
    //根据订单号查询商品，如果订单取消则库存回滚
    private function retGoodsNum($ordercode){
        $goodsD = OrderGoods::find('ordercode = "'.$ordercode.'"');
        foreach($goodsD as $value){
            $orderGoodsSql = 'update App\Models\Goods set goods_number =  goods_number + '.$value->num.' where goods_id ='.$value->goods_id;
            $this->modelsManager->executeQuery($orderGoodsSql);
        }
    }
}


