<?php
/* *
 * 功能：手机网站支付接口接入页
 * 版本：3.3
 * 修改日期：2012-07-23
 * 说明：
 * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。
 * 该代码仅供学习和研究支付宝接口使用，只是提供一个参考。

 *************************注意*************************
 * 如果您在接口集成过程中遇到问题，可以按照下面的途径来解决
 *1、开发文档中心（https://doc.open.alipay.com/doc2/detail.htm?spm=a219a.7629140.0.0.2Z6TSk&treeId=60&articleId=103693&docType=1）
 *2、商户帮助中心（https://cshall.alipay.com/enterprise/help_detail.htm?help_id=473888）
 *3、支持中心（https://support.open.alipay.com/alipay/support/index.htm）
 * 如果不想使用扩展功能请把扩展功能参数赋空值。
 */

function mobileweb_render($body){

    $html = "<!DOCTYPE html>";
    $html .= "<html>";
    $html .= "<head>";
    $html .= '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">';
    $html .= "<title>支付宝手机网站支付接口接口</title>";

    require_once(dirname(dirname(__FILE__)). "/lib/alipay.config.php");
    require_once(dirname(dirname(__FILE__)). "/lib/alipay_submit_web.class.php");


    /* ************************请求参数************************* */
    //支付类型
    $payment_type = "1";
    //必填，不能修改
    //服务器异步通知页面路径
    $notify_url = $body['alipay_notify_url'];//$alipay_config["alipay_notify_url"]; //"http://商户网关地址/create_direct_pay_by_user-PHP-UTF-8/notify_url.php";
    //需http://格式的完整路径，不能加?id=123这类自定义参数
    //商户订单号，商户网站订单系统中唯一订单号，必填
    $out_trade_no = $body['order_no'];
    //付款金额，必填
    $total_fee = $body['amount'] ; //支付宝支持：元

    //订单名称，必填
    $subject = _PAYSUBJECT_;
    // 日志
    $info = 'alipay/mobileweb_render,operator success,order_no:' . $out_trade_no
        . ',totalfee:' . $total_fee;
    tracelog($info);

    //商品描述，可空
    $bodyinfo = _PAYSUBJECT_.'，订单为：'.$out_trade_no;//$_POST['WIDbody'];


    //收银台页面上，商品展示的超链接，必填
    $show_url = $alipay_config['goods_show_url'];//$_POST['WIDshow_url'];

    /************************************************************/

//构造要请求的参数数组，无需改动
    $parameter = array(
        "service"       => "alipay.wap.create.direct.pay.by.user",
        "partner" => trim($alipay_config['partner']),
        "seller_id"  => $alipay_config['partner'],
        "payment_type"	=> $payment_type,
        "notify_url" => $notify_url,
        "return_url"	=> $body['alipay_return_url'],
        "out_trade_no"	=> $out_trade_no,
        "subject"	=> $subject,
        "total_fee"	=> $total_fee,
        "body"	=> $bodyinfo,
        "show_url"	=> $show_url,
        //"app_pay"	=> "Y",//启用此参数能唤起钱包APP支付宝
        "_input_charset"	=> trim(strtolower($alipay_config['input_charset'])),
        //其他业务参数根据在线开发文档，添加参数.文档地址:https://doc.open.alipay.com/doc2/detail.htm?spm=a219a.7629140.0.0.2Z6TSk&treeId=60&articleId=103693&docType=1
        //如"参数名"	=> "参数值"   注：上一个参数末尾需要“,”逗号。

    );
    tracelog( 'pay:alipaybank,param:'.  json_encode($parameter));
//建立请求
    $alipaySubmit = new AlipaySubmit($alipay_config);
    $html_text = $alipaySubmit->buildRequestForm($parameter, "get", "确认付款");

    $html .= $html_text;
    $html .="</body>";
    $html .="</html>";
    return $html;
}
?>

