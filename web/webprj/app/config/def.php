<?php

define('LOG_OFF', '1'); //LOG 开关 0关闭，1 打开

define('_F_BASE_URL_', "/"); //工程路径（相对应apache根路径，下同）
define('_LIBS_', "/"); //骑士团静态库libs路径
/**
 * session文件存放路径，管理员重置密码时使用，用于删除已有会话
 * wamp 配成 pathToWamp/tmp/
 * zbox 配成 /opt/zbox/tmp/
 * oneinstack安装 配成 /tmp/
 */
define('_SESSION_PATH_', "/opt/zbox/tmp/php/");
//DB 配置
define('_HOST_', '114.112.94.166'); //数据库服务器地址
define('_PORT_', '3306'); //数据库端口
define('_USER_', 'root'); //数据库用户
define('_PSW_', 'qst2016!@#');  //数据库密码
define('_DB_', 'youjds'); //数据库名称
/*define('_HOST_', '127.0.0.1'); //数据库服务器地址
define('_PORT_', '3306'); //数据库端口
define('_USER_', 'root'); //数据库用户
define('_PSW_', 'root');  //数据库密码
define('_DB_', 'shushi97'); //数据库名称*/

//微信公众号开发定义
define('_APPID_', 'wx512dbbb15744e4fe');
define('_SECRET_', '823b370c6c569ce1ff414ed5115c85c9');
define('_TICKET_', '/opt/zbox/app/wxchat/update_token/jsapi_ticket.json');
define('_WXMCHID_', '1428190902');//开户邮件中的商户号
define('_WXKEY_', 'UqVwYeHxFaBiyi1s6CZINThVmV5Xb3Xg');


//阿里支付宝帐号
define('_ALIPAYPARTNER_', '2088021351438614');//2088421257507481
define('_ALIPAYKEY_', '3bcz6uek1kyy9dr9t0kdxtvpqwvqdci9');//faslsbe9vmpvemc83fqy57269awl8709
//define('_MAILCALLBACK_', 'http://192.168.111.23/webapi/');
define('_ALIPAYGOODSSHOW_', "");
define('_ALIPAYSELLEREMIAL_', 'master@knighteam.com');//WKYY_English@163.com
define('_PAYSUBJECT_','骑士团'); //支付主题


//融云客服
define('_RONGYUNKEY_', 'cpj2xarlc4k5n');//2088421257507481
define('_RONGYUNSECRET_', 'XVOxOOdyfy');//faslsbe9vmpvemc83fqy57269awl8709
define('_CUSTOMERSERVICEID_','KEFU148850667067064');
/*
//微信支付
if (!defined('_WXAPPID_'))      {define('_WXAPPID_', 'wxf92e622f0985ef8a');}  //开户邮件中的（公众账号APPID或者应用APPID）
if (!defined('_WXAPPSECRET_'))  {define('_WXAPPSECRET_', 'e256d3510fe202a01ad764f2020e7e16');}
if (!defined('_WXAPPURL_'))     {define('_WXAPPURL_', '');}
if (!defined('_WXNOTIFYURL_'))  {define('_WXNOTIFYURL_', 'http://192.168.111.23/b3+/web/webapi/');}
if (!defined('_WXCERTURL_'))    {define('_WXCERTURL_', dirname(dirname(__FILE__)) . '/pay/alipaysdk/wxpay/cert/apiclient_cert.pem');}
if (!defined('_WXKEYURL_'))     {define('_WXKEYURL_', dirname(dirname(__FILE__)) . '/pay/alipaysdk/wxpay/cert/apiclient_key.pem');}


//发送邮件相关
if(!defined('_MAILCALLBACK_'))      define ('_MAILCALLBACK_','http://192.168.111.23/webapi/');
if(!defined('_MAILHOST_'))          define ('_MAILHOST_','smtp.exmail.qq.com');
if(!defined('_MAILUSERNAME_'))      define ('_MAILUSERNAME_','service@3brush.com');
if(!defined('_MAILPASSWORD_'))      define ('_MAILPASSWORD_','3Brush21');
if(!defined('_MAILSUBJECT_'))       define ('_MAILSUBJECT_','骑士团');




//阿里支付宝帐号
if(!defined('_ALIPAYPARTNER_'))     define ('_ALIPAYPARTNER_','2088121931536830');  //小鸡会会 B端应用
if(!defined('_ALIPAYKEY_'))         define ('_ALIPAYKEY_','2a3901cefeb546878559e9f83c6bccc1');   //小鸡会会 B端应用
//    if(!defined('_ALIPAYPARTNER_'))     define ('_ALIPAYPARTNER_','2088121931536830');  //比三家家具 C端
//    if(!defined('_ALIPAYKEY_'))         define ('_ALIPAYKEY_','ea63422966114d0791cb737a835d858e');  //比三家家具 C端
if(!defined('_ALIPAYRTURL_'))       define ('_ALIPAYRTURL_',_MAILCALLBACK_.'pay/pc_pay/alipay/return_url.php');
if(!defined('_ALIPAYNTURL_'))       define ('_ALIPAYNTURL_',_MAILCALLBACK_.'pay/pc_pay/alipay/notify_url.php');
if(!defined('_ALIPAYGOODURL_'))     define ('_ALIPAYGOODURL_',_MAILCALLBACK_);
if(!defined('_ALIPAYROOTURL_'))     define ('_ALIPAYROOTURL_',_MAILCALLBACK_);
if(!defined('_ALIPAYSELLEREMIAL_')) define ('_ALIPAYSELLEREMIAL_','b3ja@sina.com.cn'); //收款方的支付宝账号  三把刷子
//阿里支付宝帐号  银行端口
if(!defined('_ALIPAYBANKRTURL_'))   define ('_ALIPAYBANKRTURL_',_MAILCALLBACK_.'pay/pc_pay/alipaybank/return_url.php');
if(!defined('_ALIPAYBANKNTURL_'))   define ('_ALIPAYBANKNTURL_',_MAILCALLBACK_.'pay/pc_pay/alipaybank/notify_url.php');
//阿里web移动支付
if(!defined('_ALIPAYWEBRTURL_')) define ('_ALIPAYWEBRTURL_',_MAILCALLBACK_.'pay/mobilepay/alipayweb/return_url.php');
if(!defined('_ALIPAYWEBNTURL_')) define ('_ALIPAYWEBNTURL_',_MAILCALLBACK_.'pay/mobilepay/alipayweb/notify_url.php');
//阿里app移动支付
if(!defined('_ALIPAYAPPNTURL_')) define ('_ALIPAYAPPNTURL_',_MAILCALLBACK_.'pay/mobilepay/alipaysdk/notify_url.php');

//支付主体
if(!defined('_PAYSUBJECT_'))        define ('_PAYSUBJECT_','支付主题骑士团'); //支付主题
if(!defined('_PAYSUCCESSURL_'))     define ('_PAYSUCCESSURL_',_MAILCALLBACK_); //支付成功后的跳转地址


//容联参数定义
if(!defined('_CCP_REST_HOST_'))           define ('_CCP_REST_HOST_', 'app.cloopen.com');    //容联服务域名，不需要加https://
if(!defined('_CCP_REST_PORT_'))           define ('_CCP_REST_PORT_', '8883');   //容联服务端口
if(!defined('_CCP_REST_ACCOUNTSID_'))     define ('_CCP_REST_ACCOUNTSID_', '8a48b551529c7d790152a01650270441'); //主账号ID
if(!defined('_CCP_REST_ACCOUNTTOKEN_'))   define ('_CCP_REST_ACCOUNTTOKEN_', '56fc69f8f993416a9ae31929bcdce3b3');   //主账号TOKEN
if(!defined('_CCP_REST_VERSION_'))        define ('_CCP_REST_VERSION_', '2013-12-26');  //api版本
if(!defined('_CCP_REST_APPID_'))          define ('_CCP_REST_APPID_','8a48b55153404cc3015345d51c3409cd');  //c端appid
if(!defined('_CCP_REST_APPIDB_'))         define ('_CCP_REST_APPIDB_','aaf98f8953403fcf015345dc61970a35');  //B端appid
if(!defined('_CCP_REST_APPTOKENB_'))      define ('_CCP_REST_APPTOKENB_','7c66c42c2bf2b74dbc77f47c709befb4');  //B端appToken 初始服务端推送账号使用
if(!defined('_CCP_REST_DISPLAYNUM_'))     define ('_CCP_REST_DISPLAYNUM_','333'); //外呼通知的来显号码，注，现在不起作用，指定来显需要容联开通
if(!defined('_CCP_REST_PUSHUSER_'))       define ('_CCP_REST_PUSHUSER_','10001'); //服务端消息推送时用的用户ID，注，必须为该应用在容联平台注册的用户id

if(!defined('_HT_INQUIRY_DAYS_'))       define ('_HT_INQUIRY_DAYS_',15); //后台接口 一次询价有效期
if(!defined('_HT_WALLET_COUNT_'))       define ('_HT_WALLET_COUNT_',10); //B 端接口 一次查询几条数据
if(!defined('_HT_INQUIRY_COUNT_'))       define ('_HT_INQUIRY_COUNT_',15); //后台接口  一次查询，几条数据
if(!defined('__FFMPEG_CMD__'))  define('__FFMPEG_CMD__', dirname(dirname(__FILE__)).'/mediasupport/ffmpeg') //ffmpeg 路径
?>
*/