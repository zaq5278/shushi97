<?php 
//③、在支持成功回调通知中处理成功之后的事宜，见 notify.php
/**
 * 注意：
 * 1、当你的回调地址不可访问的时候，回调通知会失败，可以通过查询订单来确认支付是否成功
 * 2、jsapi支付时需要填入用户openid，WxPay.JsApiPay.php中有获取openid流程 （文档可以参考微信公众平台“网页授权接口”，
 * 参考http://mp.weixin.qq.com/wiki/17/c0f37d5704f0b64713d5d2c37b468d75.html）
 */
class ALIPAY {

    /*
     * 阿里PC端，扫码支付
    */
    public function joinalipayinfo($order_no,$amount,$notifyurl) {
        require_once(__DIR__."/alipay/alipayapi_qr.php");
        $body['order_no'] = $order_no;  #订单号
        $body['amount'] = $amount;  #价格，元，最小到0.01
        $body['alipay_notify_url'] = $notifyurl; #回调地址
        $infonew = alipayqr($body);
        tracelog("joinalipayinfo，body:" . $infonew);
//        print_r(($infonew));exit;
        $ret['status'] =0;
        $ret['desc'] =0;
        $ret['infonew'] =$infonew;
        return $ret;exit;
//        $this->doPayRequest($infonew);
    }

    /**
     * 构造pc端请求
     * @return string
     */
    function render($type,$order_no,$amount,$notify_url,$return_url) {
        $body['order_no'] = $order_no;  #订单号
        $body['amount'] = $amount;  #价格，元，最小到0.01
        $body['alipay_notify_url'] = $notify_url; #回调地址  异步
        $body['alipay_return_url'] = $return_url; #回调地址  同步跳转
        switch($type){
            case 1: //  PC端  网页跳转  支持账户密码与扫码
                require_once __DIR__ . "/alipay/alipayapi_pc.php";
                return web_render($body);
                break;
            case 2: //  PC端  网页跳转  支持网银支付
                require_once __DIR__ . "/alipay/alipayapi_bank.php";
                return bank_render($body);
                break;
            case 3: //  手机端  网页跳转
                require_once __DIR__ . "/alipay/alipayapi_web.php";
                return mobileweb_render($body);
                break;
            default:
                break;
        }
    }

    /**
     * 回调，调用支付宝提供的接口，查询订单状态
     * 同步，页面跳转
     */
    public function CBreturn(){
        require_once __DIR__ . "/alipay/return_url.php";
        $html = callback();
        return $html;
    }
    /**
     * 回调，调用支付宝提供的接口，查询订单状态
     * 异步，消息通知
     */
    public function CBnotify(){
        require_once __DIR__ . "/alipay/notify_url.php";
        $info = callback();
        return $info;

    }
    /**
     * 回调，调用支付宝提供的接口，查询订单状态
     * 异步，消息通知
     * 用于手机端APP支付
     */
    public function CBAppnotify(){
        require_once __DIR__ . "/alipay/notify_url_app.php";
        $info = callback();
        return $info;

    }

    /*
     * 发送post请求
     */
    function doPayRequest($url, $param = array()) {
        $urlinfo = parse_url($url);
        $host = $urlinfo['host'];
        $path = $urlinfo['path'];
        $query = isset($param) ? http_build_query($param) : '';
        $port = 80;
        $errno = 0;
        $errstr = '';
        $timeout = 10;

        $fp = fsockopen($host, $port, $errno, $errstr, $timeout);

        $out = "POST " . $path . " HTTP/1.1\r\n";
        $out .= "host:" . $host . "\r\n";
        $out .= "content-length:" . strlen($query) . "\r\n";
        $out .= "content-type:application/x-www-form-urlencoded\r\n";
        $out .= "connection:close\r\n\r\n";
        $out .= $query;

        $ret = fputs($fp, $out);
        fclose($fp);
        return $ret;
    }


    /*
     * To change this license header, choose License Headers in Project Properties.
     * To change this template file, choose Tools | Templates
     * and open the template in the editor.
     */
//    /*
//     * 统一返回错误信息
//     */
//
//    function echoerrinfo($status, $desc) {
//        $resp['status'] = $status;
//        $resp['desc'] = $desc;
//        print_r(json_encode($resp));
//        exit;
//    }

    /*
     * 发送post请求  json串
     */
//    function doPayRequestjsonstr($url, $param = array()) {
//        $urlinfo = parse_url($url);
//        $host = $urlinfo['host'];
//        $path = $urlinfo['path'];
//        $query = isset($param) ? json_encode($param) : '';
//        $port = 80;
//        $errno = 0;
//        $errstr = '';
//        $timeout = 10;
//
//        $fp = fsockopen($host, $port, $errno, $errstr, $timeout);
//
//        $out = "POST " . $path . " HTTP/1.1\r\n";
//        $out .= "host:" . $host . "\r\n";
//        $out .= "content-length:" . strlen($query) . "\r\n";
//        $out .= "content-type:application/x-www-form-urlencoded\r\n";
//        $out .= "connection:close\r\n\r\n";
//        $out .= $query;
//        $ret = fputs($fp, $out);
//        fclose($fp);
//        return $ret;
//    }



    /*
    * 生成RSA密码
    * $info:数组
    */
    function creatersanew($info,$alipay_config){
        $alisub = new AlipaySubmit($alipay_config);
        $rsa=$alisub->buildRequestParaToString($info);
        return $rsa;
    }


    /*
     * 拼接阿里app支付的消息
     */
    public function joinalipayappinfo($out_trade_no,$fee,$notify_url) {
        require_once("lib/alipay_submit_app.class.php");
        require_once("lib/alipay_rsa.function.php");
        require_once("lib/alipay.config.php");
//    $info = 'partner="' . _ALIPAYPARTNER_ . '"&seller_id="' . _ALIPAYSELLEREMIAL_
//            . '"&out_trade_no="'. $out_trade_no . '"&subject="' . $order_no . '"&body="'
//            . $order_no . '"&total_fee="' . $fee . '"&notify_url="' . _ALIPAYAPPNTURL_
//            . '"&service="mobile.securitypay.pay'. '"&payment_type="1"&_input_charset="utf-8'
//            . '"&it_b_pay="30m';//&show_url='._MAILCALLBACK_;

//        _input_charset="utf-8"
//        &body="骑士团,订单号：orderno123456"
//        &it_b_pay="30m"
//        &notify_url="123.57.223.193/webprj/index/cbnotify"
//        &out_trade_no="orderno123456"
//        &partner="2088021351438614"
//        &payment_type="1"
//        &seller_id="master@knighteam.com"
//        &service="mobile.securitypay.pay"
//        &subject="骑士团"
//        &total_fee="0.01"
//        &sign="f%2B2SD9yHgOIpsnUNqV4%2Fu2xYI2heOGv8eO44inUmyZgzt%2BM0RHvuD%2BshDR2qvCzCSIIn41d2fWi8e1IeEJakqEcRBFt%2Fzd6OHsyuDwOmbjlXf4bBukIxvsUZ3gzbYH4xsV%2BwbnB4SxolTCPlCtn%2FuPpgHB3NYCc40snF8LZUpMU%3D"
//        &sign_type="RSA"

        $infonew = array(
            'partner' => $alipay_config['partner'],
            'seller_id' => $alipay_config['WIDseller_email'],
            'out_trade_no' => $out_trade_no,
            'subject' => _PAYSUBJECT_,
            'body' => _PAYSUBJECT_.",订单号：".$out_trade_no,
            'total_fee' => $fee,
            'notify_url' => $notify_url,
            'service' => 'mobile.securitypay.pay',
            'payment_type' => '1',
            '_input_charset' => 'utf-8',
            'it_b_pay' => '30m',
        );
        $infonew = $this->creatersanew($infonew,$alipay_config);
//    $info = $info. '"&sign="' . $sign . '"&sign_type="RSA"';
//        print_r($infonew);exit;
        tracelog("RESP:" . json_encode($infonew));
        $ret['order_no'] = $out_trade_no;
        $ret['ali_pay_info'] =$infonew;
//        print_r($ret);exit;
        return $ret;
    }


    /*
     * 生成RSA密码
     * 测试有问题，使用creatersanew
     */

    public function creatersa($info) {
        $rsa = rsaSign($info, dirname(__FILE__) . '/mobilepay/alipaysdk/key/rsa_private_key.pem');
        return $rsa;
    }


    /*
    * 验证签名
    */
    public function verifysign($body) {
        $realdata = $body['realdata'];
        $sign = $body['sign'];
        $ret = rsaVerify($realdata, _PAYROOT_URL_ . '/pay/mobilepay/alipaysdk/key/alipay_public_key.pem', $sign);
        if ($ret) {
            $ret1['status'] = 0;
            $ret1['ret'] = $ret;
        } else {
            $ret1['status'] = -1;
            $ret1['ret'] = $ret;
        }
        return $ret1;
    }





    /*
    * 客户端主动请求数据，如果没有成功，
    */
    public function queryorderstatus() {
        $resp = array();
        $body_arr = $this->body;
//        $ret = exec_procedure($body_arr, 'p_order_query_result');
//        $this->tmporder_no = isset($ret['out_data']['tmporder_no'])?$ret['out_data']['tmporder_no']:'';
//        if(!$this->tmporder_no){
//            $ret['status'] = -2;
//            print_r(json_encode($ret));exit;
//        }
//        if ($ret['status'] == 0 && $ret['out_data']['orderstatus'] == 2) {
//            $resp = $ret;
//            $resp['orderstatus'] = $ret['out_data']['orderstatus'];
//            return $resp;
//        } else {
//            //主动请求，如果对账出现差异，修改数据库
//            $channel = $this->channel;
//            switch ($channel) {
//                case "wx"://扫码支付  微信
//                    $resp = $ret;
//                    $resp['orderstatus'] = $ret['out_data']['orderstatus'];
//                    break;
//                case 'wxapp':
////                    $resp = $this->wxqueryorderdata();  //后面添加
//                    $resp = $ret;
//                    $resp['orderstatus'] = $ret['out_data']['orderstatus'];
//                    break;
//                case 'alipayapp':
//                    $resp = $ret;
//                    $resp['orderstatus'] = $ret['out_data']['orderstatus'];
//                    break;
//                default :
//                    $resp['status'] = 1041;
//                    $resp['desc'] = '等待支付';
//                    break;
//            }
//        }
        return $resp;
    }

    /**
     * 查询订单状态
     */
    public function orderstate($body) {
        $orderno = $body['orderno'];
        $orderno = $body['orderno'];
        //查询数据库，这里先返回0，调通接口，不做查询
        $ret['status'] = 0;
        $ret['desc'] = '';
        $ret['orderstate'] = 1; //0.订单生成,1.订单已支付成功,2.订单支付失败3.订单取消,4.申请退款 5.已退款
        return $ret;
    }



}
