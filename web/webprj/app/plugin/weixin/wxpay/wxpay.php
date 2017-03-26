<?php 

require_once(__DIR__ . "/lib/WxPay.Api.php");
require_once(__DIR__ . "/wpay/WxPay.JsApiPay.php");

//For 商户付款(用户提现）
class ENPAY {
    private $parameters = [];
    public function __construct() {
    }
 
    /*初始化数组参数*/
    public function setParameter($key,$value){
        $this->parameters[$key] = $value;
    }
    function formatBizQueryParaMap($paraMap, $urlencode){
        var_dump($paraMap);//die;
        $buff = "";
        ksort($paraMap);
        foreach ($paraMap as $k => $v){
            if($urlencode){
                $v = urlencode($v);
            }
            //$buff .= strtolower($k) . "=" . $v . "&";
            $buff .= $k . "=" . $v . "&";
        }
        $reqPar="";
        if (strlen($buff) > 0){
            $reqPar = substr($buff, 0, strlen($buff)-1);
        }
        var_dump($reqPar);
        return $reqPar;
    }

    /* 	作用：生成签名 */
    function getSign($Obj){
        var_dump($Obj);//die;
        $tmpparam =[];
        foreach ($Obj as $k => $v){
            $tmpparam[$k] = $v;
        }
        //签名步骤一：按字典序排序参数
        ksort($tmpparam);
        $String = $this->formatBizQueryParaMap($tmpparam, false);
        //echo '【string1】'.$String.'</br>';
        //签名步骤二：在string后加入KEY

        $String = $String."&key=".WxPayConfig::KEY;
        //echo "【string2】".$String."</br>";
        //签名步骤三：MD5加密
        $String = md5($String);

        //echo "【string3】 ".$String."</br>";
        //签名步骤四：所有字符转为大写
        $result_ = strtoupper($String);
        //echo "【result】 ".$result_."</br>";
        return $result_;
    }

    private function createNonceStr($length = 16) {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }
    /**
     * 将数组转换成xml
     */
    public function ToXml(){
        if(!is_array($this->parameters)|| count($this->parameters) <= 0){
            throw new WxPayException("数组数据异常！");
        }
        $xml = "<xml>";
        foreach ($this->parameters as $key=>$val){
            if (is_numeric($val)){
                    $xml.="<".$key.">".$val."</".$key.">";
            }else{
                    $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
            }
        }
        $xml.="</xml>";
       // tracelog("ToXml----");
       // tracelog($xml);
        return $xml; 
    }

    /*用户提现，企业付款*/
   
    public function en_pay_package($openid,$trade_no,$amount,$name,$desc){
        $this->setParameter('mch_appid',WxPayConfig::APPID);
        $this->setParameter('mchid',WxPayConfig::MCHID);
        $this->setParameter('nonce_str',$this->createNonceStr(16));
         // 用户openid
        $this->setParameter('openid', $openid);
        // 商户订单号
        $this->setParameter('partner_trade_no',$trade_no);
        // 校验用户姓名选项
        $this->setParameter('check_name', 'NO_CHECK');
        // 企业付款金额  单位为分
        $this->setParameter('amount', $amount);
        // 企业付款描述信息
        $this->setParameter('desc',$desc);
        // 调用接口的机器IP地址  自定义
        $this->setParameter('spbill_create_ip',$_SERVER["REMOTE_ADDR"]); 
        // 收款用户姓名
        $this->setParameter('re_user_name', $name);
        // 设备信息

        $sign=$this->getSign($this->parameters);
        $this->setParameter('sign', $sign);
        $xml = $this->ToXml();
        if( !empty($xml) ) {
              return $xml ;
        }
        return  false;
    }
    
}

//③、在支持成功回调通知中处理成功之后的事宜，见 notify.php
/**
 * 注意：
 * 1、当你的回调地址不可访问的时候，回调通知会失败，可以通过查询订单来确认支付是否成功
 * 2、jsapi支付时需要填入用户openid，WxPay.JsApiPay.php中有获取openid流程 （文档可以参考微信公众平台“网页授权接口”，
 * 参考http://mp.weixin.qq.com/wiki/17/c0f37d5704f0b64713d5d2c37b468d75.html）
 */
class WXPAY {
      private $jsParameters;
      private $editAddress;
      private $enpay;
      public function __construct() {
          $this->enpay = new ENPAY();
      }

    /* 微信公众号-卫星支付接口
     * @param type $ordercode  订单编号
     * @param type $fee        支付金额 以元为单位
     * @param type $notiyurl   设置接收微信支付异步通知回调地址  "http://www.dalaozhaopin.com/dlzp/wxpay/wpay/notify.php"
     * @param type $body       设置商品或支付单简要描述
     * @param type $attach     设置附加数据，在查询API和支付通知中原样返回，该字段主要用于商户携带订单的自定义数据
     * @param type $goodstag   设置商品标记，代金券或立减优惠功能的参数，说明详见代金券或立减优惠
     */
    public function wxpay($ordercode,$fee,$notiyurl,$openId,$body="",$attach="",$goodstag="") {
        ini_set('date.timezone','Asia/Shanghai');
        $tools = new JsApiPay();
        $input = new WxPayUnifiedOrder();
        $input->SetOut_trade_no($ordercode);
        //$vfee = 1;
        $vfee =$fee  * 100;
        $input->SetTotal_fee($vfee);
        $input->SetNotify_url($notiyurl);
        
        $time = time();
        $input->SetTime_start(date("YmdHis"),$time);
        $input->SetTime_expire(date("YmdHis", time() + 600));

        if(!empty($body)&&$body!=""){
            $input->SetBody($body);
        }
        if(!empty($attach)&&$attach!=""){
            $input->SetAttach($attach);
        }
        if(!empty($goodstag)&&$goodstag!=""){
            $input->SetGoods_tag($goodstag);
        }
        

        $type ="";
        if(!empty($openId)&&$openId!=""){ //微信公众号
            $input->SetTrade_type("JSAPI");
            $input->SetOpenid($openId);
            $type ="JS";
        }
        else{ // APP
            $input->SetTrade_type("APP");
            $type ="APP";

        }
        $order = WxPayApi::unifiedOrder($input);


        $resp=array();
        if($order['return_code'] =='FAIL'){
            $resp['status']='1';
            $resp['parameters']= $order;
        }
        else {
            $resp['status']='0';
            $resp['parameters']= $tools->GetParameters($order,$type);
        }
        tracelog($order);
        return $resp;
    }
    
     //微信提现,企业推荐
     private function en_pay($xml){
         return WxPayApi::en_pay($xml);
     }
     
     private function en_pay_package($openid,$trade_no,$amount,$name,$desc){
         return $this->enpay->en_pay_package($openid,$trade_no,$amount,$name,$desc);
     }
     /*
      微信提现-后台操作提现
      uuid ： 提现单的唯一id，qz_user_tixian表的uuid
      htuserid  后台操作员id
      vstate    0.申请提现，1提现成功 ，2拒绝提现 3，提现失败
      操作步骤
        1.根据提现单id 查找提现人的 openid ,money，name trade_no
        2.涉及到 p_ht_user_tixian_agree p_ht_tixian_user_query 两个存储过程
        3.涉及到 qz_user_tixian qst_user_bind qst_user_info 三个表
     */
     function wx_tixian($uuid,$htuserid,$vstate,$desc){
         
       // $resp=ht_tixian_user_query($body);
        tracelog('wx_tixian 输入---------------');
        tracelog('uuid:'.$uuid.' htuserid:'.$htuserid);
         
        $body['uuid']=$uuid;
        $resp = exec_procedure($body, "p_ht_tixian_user_query");
        tracelog($resp);
        $body['vstate']=$vstate;
        if($resp['status']==0 && $vstate ==1 ){
            $openid     = $resp['out_data']['openid'];
            $trade_no   =$resp['out_data']['trade_no']; //"10000011";
            $amount     = $resp['out_data']['money'];
            $name       =$resp['out_data']['name'];
            //$desc ="验房师提现";
            //$amount =100; //测试所用 1元钱
            tracelog("提现同意-------------");

            $xml = $this->en_pay_package($openid,$trade_no,$amount,$name,$desc);
            tracelog("en_pay_package 返回 -------------");
            tracelog($xml);
            $xml = $this->en_pay($xml);
            tracelog("en_pay 返回-------------");
            tracelog($xml);
            libxml_disable_entity_loader(true);
            $datas = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
            tracelog("datas 返回-------------");
            tracelog($datas);
             
            $datas['vstate']=$body['vstate'];
            $datas['uuid']=$body['uuid'];
            tracelog($datas['result_code']);
            if($datas['result_code']=='FAIL'){
                 $datas['vstate']=3;
            }
            else {
                //成功时，return_msg 返回的 return_msg 为空，将payment_no 值付跟 return_msg
                $datas['return_msg'] =$datas['payment_no'];
            }
            
            $datas['htuserid'] =$htuserid;
            $resp = exec_procedure($datas, "p_ht_user_tixian_agree");
            tracelog("同意提现 en_pay 执行完毕-------------");
            return $resp;
        }
        else {
            $body['return_code']="";
            $body['return_msg']="";
            $body['result_code']="";
            $body['err_code']="";
            $body['err_code_des']="";
            $body['htuserid'] =$htuserid;
            $resp = exec_procedure($body, "p_ht_user_tixian_agree");
            tracelog("拒绝提现 执行完毕-------------");
            return $resp;
        }
     }
     
    //微信退款
     function wx_refund($ordercode,$totalfee,$refundfee){
            $input = new WxPayRefund();
            $input->SetOut_trade_no($ordercode);
            $input->SetTotal_fee($totalfee);
            $input->SetRefund_fee($refundfee);
            $input->SetOut_refund_no(WxPayConfig::MCHID.date("YmdHis"));
            $input->SetOp_user_id(WxPayConfig::MCHID);
            return WxPayApi::refund($input);
         
     }
     
}
