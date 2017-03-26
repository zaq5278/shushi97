<?php
namespace Plugin\SMS;
// 定义使用的短信平台("ronglianyun", "luosimao","chuanglan")
define('SMS_PLAT', "ronglianyun");
define('SMS_CONTENT1','【拼拼赚】您的验证码为');
define('SMS_CONTENT2','，请于1分钟内正确输入，如非本人操作，请忽略此短信。');
class SMS
{
      public function __construct() {
      
      }

        /*
     功能描述:获取短信验证码
     入参: 
        account         手机号
        length	　	验证码长度.不传默认为6位
        deadminutes     验证码有效时长默认30分钟
        repeatminutes   允许重发时间默认1分钟
    出参: 
        code            验证码
        deadminutes     剩余的过期时间
        leftsecond      禁止重发，查出剩余时间
        status          0.成功，1002.禁止重发，查出剩余时间，1007.参数错误 ,1021.发送短信失败
     */
    function sms_general($body_arr) {
        //判断账号的类型
        $type = 0;
        $body['account']=$body_arr['account'];
        if(preg_match("/^1[34578]\d{9}$/", $body['account'])){
             $type = 2;
        }
        else {
            //if(checkemail($body['account'])){ $type = 1;}
        }
        $body["length"] = 4;          //验证码长度,默认为6位
        $body['deadminutes'] = 20;      //验证码有效时长,默认30分钟
        $body['repeatminutes'] = 1;     //允许重发时间,默认1分钟
        $body["tempId"] = 152210;      //
        $resp = \Plugin\Core\QSTBaseModel::execsql($body, 'p_make_smscode');
        if ($resp["status"] != 0) {
            return $resp;
        }
        //tracelog("sms_general" . $body["account"]." code ".$resp['data'][0]["code"]);
        require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.SMS_PLAT.DIRECTORY_SEPARATOR."smsclass.php");

        // 读取平台配置参数
        tracelog( dirname(__FILE__).DIRECTORY_SEPARATOR."config.php");
        $sms_config = include (dirname(__FILE__).DIRECTORY_SEPARATOR."config.php");
        // 短信平台配置参数
        $param = $sms_config[SMS_PLAT];

        // 接收短信手机号
        $param['phone'] = $body["account"];

        // 短信内容参数-容联云
        $param['tempId'] = $body["tempId"];
        $param['datas'] = array($resp['data'][0]["code"],$resp['data'][0]["deadminutes"]);
       // 短信内容参数-螺丝帽,容联云易模板中陪的内容
        $param['content'] = SMS_CONTENT1.$resp['data'][0]["code"].SMS_CONTENT2;
        tracelog( "param--->" . json_encode($param));
        $smsObject = new \SMSClass($param);
        if($smsObject->send()){
           $resp['status'] =0;
        }
        else{
           $resp['status'] =1021;
        }
        $ret['status']= $resp['status'];
        $ret['code']= $resp['data'][0]['code'];
        $ret['deadminutes']= $resp['data'][0]['deadminutes'];
        $ret['leftsecond']= $resp['data'][0]['leftsecond'];
        return $ret;
    }
    
       /*
     功能描述:获取短信验证码
     入参: 
        phone           手机号
        tempId          tempid
        datas[
            param1	　	模板消息中的变量参数名
            param2          模板消息中的变量参数名
        ]
    出参: 
        status          0.成功，1021.发送短信失败
     */
    function sendsms($body) {
        //判断账号的类型
        tracelog("sendsms------------");
        tracelog($body);
        require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.SMS_PLAT.DIRECTORY_SEPARATOR."smsclass.php");

        // 读取平台配置参数
        tracelog( dirname(__FILE__).DIRECTORY_SEPARATOR."config.php");
        $sms_config = include (dirname(__FILE__).DIRECTORY_SEPARATOR."config.php");
        // 短信平台配置参数
        $param = $sms_config[SMS_PLAT];

        // 接收短信手机号
        $param['phone'] = $body["account"];

        // 短信内容参数-容联云
        $param['tempId'] = $body["tempId"];
        $param['datas'] = $body['datas'];

        // 短信内容参数-螺丝帽,容联云易模板中陪的内容
        tracelog( "param--->" . json_encode($param));
        $smsObject = new \SMSClass($param);
        if($smsObject->send()){
           $resp['status'] =0;
        }
        else{
           $resp['status'] =1021;
        }
        return $resp;
    }
 /*
 功能描述: 校验用户输入验证码的合法性
  入参: account       注册账号:手机号码或邮箱
        code          验证码
        type          1:邮箱2:手机
  出参:
        vo_res        0.成功,1003.验证码不对,1007.参数不对,9999.数据库异常
  */
    function checksmscode($body) {
        return  \Plugin\Core\QSTBaseModel::execsql($body, 'p_check_code');

    }
}