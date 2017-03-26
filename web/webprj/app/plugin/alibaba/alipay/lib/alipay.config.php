<?php
/* *
 * 配置文件
 * 版本：3.3
 * 日期：2012-07-19
 * 说明：
 * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。
 * 该代码仅供学习和研究支付宝接口使用，只是提供一个参考。
	
 * 提示：如何获取安全校验码和合作身份者id
 * 1.用您的签约支付宝账号登录支付宝网站(www.alipay.com)
 * 2.点击“商家服务”(https://b.alipay.com/order/myorder.htm)
 * 3.点击“查询合作者身份(pid)”、“查询安全校验码(key)”
	
 * 安全校验码查看时，输入支付密码后，页面呈灰色的现象，怎么办？
 * 解决方法：
 * 1、检查浏览器配置，不让浏览器做弹框屏蔽设置
 * 2、更换浏览器或电脑，重新登录查询。
 */
//↓↓↓↓↓↓↓↓↓↓请在这里配置您的基本信息↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
//合作身份者ID，签约账号，以2088开头由16位纯数字组成的字符串，查看地址：https://b.alipay.com/order/pidAndKey.htm
$alipay_config['partner']		= _ALIPAYPARTNER_;  

//安全检验码，以数字和字母组成的32位字符
// MD5密钥，安全检验码，由数字和字母组成的32位字符串，查看地址：https://b.alipay.com/order/pidAndKey.htm
$alipay_config['key']			= _ALIPAYKEY_;
// 服务器异步通知页面路径  需http://格式的完整路径，不能加?id=123这类自定义参数，必须外网可以正常访问
//↑↑↑↑↑↑↑↑↑↑请在这里配置您的基本信息↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑

//专用APP支付
//商户的私钥（后缀是.pen）文件相对路径
$alipay_config['private_key_path']	=  dirname(dirname(__FILE__)).'/key/rsa_private_key.pem';
//支付宝公钥（后缀是.pen）文件相对路径
$alipay_config['ali_public_key_path']=  dirname(dirname(__FILE__)).'/key/alipay_public_key.pem';


//$alipay_config['alipay_return_url'] = _PAYROOT_URL_.'/pay/pc_pay/alipay/return_url.php';  //回调地址，跳转
//$alipay_config['alipay_notify_url'] = _PAYROOT_URL_.'/pay/pc_pay/alipay/notify_url.php';  //回调地址，通知页面
//$alipay_config['alipay_return_urlqr'] = _PAYROOT_URL_.'/pay/pc_pay/alipay/return_urlqr.php';  //回调地址，控制父级页面跳转
$alipay_config['goods_show_url'] = _ALIPAYGOODSSHOW_;  //商品展示页

//收款方的支付宝账号
$alipay_config['WIDseller_email'] = _ALIPAYSELLEREMIAL_;

//签名方式 不需修改
$alipay_config['sign_type']  = strtoupper('MD5');
//签名方式 不需修改  APP的签名方式
$alipay_config['app_sign_type']    = strtoupper('RSA');

$alipay_config['WIDdefaultbank'] = 'ABC'; //仅用于网银支付，默认的支付银行

//字符编码格式 目前支持 gbk 或 utf-8
$alipay_config['input_charset']= strtolower('utf-8');

//ca证书路径地址，用于curl中ssl校验
//请保证cacert.pem文件在当前文件夹目录中
$alipay_config['cacert']    = getcwd().'\\cacert.pem';

//访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
$alipay_config['transport']    = 'http';
?>