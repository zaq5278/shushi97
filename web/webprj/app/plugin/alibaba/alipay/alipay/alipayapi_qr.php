<?php
/* *
 * 功能：即时到账交易接口接入页
 * 版本：3.3
 * 修改日期：2012-07-23
 * 说明：
 * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。
 * 该代码仅供学习和研究支付宝接口使用，只是提供一个参考。

 *************************注意*************************
 * 如果您在接口集成过程中遇到问题，可以按照下面的途径来解决
 * 1、商户服务中心（https://b.alipay.com/support/helperApply.htm?action=consultationApply），提交申请集成协助，我们会有专业的技术工程师主动联系您协助解决
 * 2、商户帮助中心（http://help.alipay.com/support/232511-16307/0-16307.htm?sh=Y&info_type=9）
 * 3、支付宝论坛（http://club.alipay.com/read-htm-tid-8681712.html）
 * 如果不想使用扩展功能请把扩展功能参数赋空值。
 */

function alipayqr($body) {
    require_once(dirname(dirname(__FILE__)) . "/lib/alipay.config.php");
    require_once(dirname(dirname(__FILE__)) . "/lib/alipay_submit_pc.class.php");
    /* ************************请求参数************************* */
    //支付类型
    $payment_type = "1";
    //必填，不能修改
    //服务器异步通知页面路径
    $notify_url = $body['alipay_notify_url'];//$alipay_config["alipay_notify_url"]; //"http://商户网关地址/create_direct_pay_by_user-PHP-UTF-8/notify_url.php";
    //需http://格式的完整路径，不能加?id=123这类自定义参数
    //卖家支付宝帐户
    $seller_email = $alipay_config["WIDseller_email"]; //_POST['WIDseller_email'];
    //必填
    //商户订单号//商户网站订单系统中唯一订单号，必填
    $out_trade_no = $body['order_no']; //记录订单号
    //付款金额
    $total_fee = $body['amount']; //支付宝支持：元
    //
    $subject = _PAYSUBJECT_;
    // 日志
    $info = 'alipay/alipayqr.php,operator success,order_no:' . $out_trade_no
            . ',totalfee:' . $total_fee;
    tracelog($info);

    //订单描述
    $bodyinfo = _PAYSUBJECT_ . '，订单为：' . $out_trade_no; //$_POST['WIDbody'];
    //商品展示地址
    $show_url = $alipay_config['goods_show_url']; //$_POST['WIDshow_url'];
    //需以http://开头的完整路径，例如：http://www.商户网址.com/myorder.html
    //防钓鱼时间戳
    $anti_phishing_key = "";
    //若要使用请调用类文件submit中的query_timestamp函数
    //客户端的IP地址
    $exter_invoke_ip = "";
    //非局域网的外网IP地址，如：221.0.0.1
    /*     * ********************************************************* */

    //构造要请求的参数数组，无需改动
    $parameter = array(
        "service" => "create_direct_pay_by_user",
        "partner" => trim($alipay_config['partner']),
        "payment_type" => $payment_type,
        "notify_url" => $notify_url,
        "return_url" => '',
        "seller_email"	=> trim($seller_email),
        "out_trade_no" => $out_trade_no,
        "subject" => $subject,
        "total_fee" => $total_fee,
        "body" => $bodyinfo,
        "qr_pay_mode" => 1, //  1  2  3不同的模式，仅展示二维码
        "show_url" => $show_url,
        "anti_phishing_key" => $anti_phishing_key,
        "exter_invoke_ip" => $exter_invoke_ip,
        "_input_charset" => trim(strtolower($alipay_config['input_charset']))
    );
    tracelog( 'pay:pc_pay:alipay,param:'.  json_encode($parameter));
    //建立请求
    $alipaySubmit = new AlipaySubmit($alipay_config);
    $html_text = $alipaySubmit->buildRequestParaToString($parameter);
//    print_r("https://mapi.alipay.com/gateway.do?".$html_text);exit;
    return "https://mapi.alipay.com/gateway.do?".$html_text;
}
