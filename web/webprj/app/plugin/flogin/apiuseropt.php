<?php


/*
功能描述:用户注册
      包括手机号码注册;邮箱账号注册;第3方登录必须绑定,如果是第3方登录,必须填写手机号或邮箱账号时，需要做一次第3方绑定操作;
入参: 
    openid        第三方标识
    account       手机号
    passwd        密码
    nick          昵称
    type          注册账号类型:1.邮箱,2.手机
    thirdtype     绑定第3方账号类型: 3.QQ,4.微信,5.微博
出参: 
    userid   用户的id
    vo_res   0.成功, 1007.参数错误,1032.邮箱已存在,1033.账号已注册,1034.手机号已被绑定,1036.邮箱已注册但未激活，9999.数据异常
 */

function userRegistration($body){
   $openid =getSessonUserData("openid") ;
   //$openid 不为空时，表示在第3方登录接口时，已经在session中记录了第3方登录账号的openid
    if($openid != ""){
        $body["openid"]=$openid;
        $body["thirdtype"] =getSessonUserData("thirdtype") ;
        //如果第3方登录，昵称为空时，用替换注册账号的中间4位作为昵称显示，这个功能可选
        if(getSessonUserData("nick") ==""){
            $body['nick'] = substr_replace($body['account'], '****', 4, 4);
        }
    }
    //当account 为手机号码时,需要用验证短信验证码的合法性,在此之前一定执行了 sendsmsAction接口
    if($body['type']=='2'){
        $resp = exec_procedure($body, 'p_check_code');
        if($resp['status'] != 0){
            die_err_code($resp['status'], __LINE__);
        }
    }
    //执行用户注册
    $resp = exec_procedure($body, 'p_user_registration');
    if($resp['status'] != 0){
        die_err_code($resp['status'], __LINE__);
    }
    $body['userid'] = $resp['userid'];
    //一般业务场景都是注册后立即登录,如果不需立即登录，下面登录代码注释即可
    $body['token'] = md5(time().rand(10000, 99999).rand(10000, 99999));
    $resp = exec_procedure($body, 'p_user_login');
    if($resp['status']==0){
        setSessonUserData('userid',$resp['out_data']['userid']);
        setSessonUserData('account',$resp['out_data']['account']);
        setSessonUserData('token',$resp['out_data']['token']);
        tracelog($resp); 
    }
    return $resp;
}

/*功能描述: 用户账号登录或第3方账号登录
     入参: 
           account       登录账号(系统账号或第3方openid)
           passwd        密码
           token         登录令牌
           type	  	 1.邮箱账号登录,2.手机账号登录,3:QQ登录,4.微信账号登录,5.微博账号登录

     出参: 
           userid        用户的id
           token         登录令牌
           isbind        第3方登录是否已绑定：0.未绑定, 1.绑定
           vo_res        0.成功,1001.账号不存在,1061.账号失效,1008.密码错误,9999.数据库异常
 */
function userLogin($body) {
    $resp = execsql($body, "p_user_login");
    tracelog($resp); 
    return $resp;
}

function exec_procedure(){

}


function user3PLogin($body) {
    setSessonUserData('logintype', $body['type']); 
    setSessonUserData('thirdtype', $body['thirdtype']);
    require_once(dirname(__FILE__)."/conf.php");
    switch($body['thirdtype']){
    case 3://QQ登录
        require_once(dirname(__FILE__)."/qqlogin/index.php");
        return qq_login(_QQAPPID_,_QQAPPKEY_,_CALLBACK_URL_);
    case 4://微信登录
        require_once(dirname(__FILE__)."/wxlogin/index.php");
        return wx_login(_WXAPPID_ ,_CALLBACK_URL_);
    case 5://微博登录
        require_once(dirname(__FILE__)."/wblogin/index.php");
        return wb_login(_WBAKEY_,_WBSKEY_,_CALLBACK_URL_);
    }
}

/*
功能描述: 找回密码--设置新密码
 入参: 
       account       手机号
       passwd           密码
       type          1.邮箱,2.手机
 出参:
       vo_res        0 成功 ,1001.账号不存在,1007.参数错误,其他表示异常
 */
function passwdreset($body){
    $resp = exec_procedure($body, 'p_passwd_reset');
    if($resp['status'] != 0){
        die_err_code($resp['status'], __LINE__);
    }
    tracelog($resp);
    $body['token'] = md5(time().rand(10000, 99999).rand(10000, 99999));
    $resp=userLogin($body);
    if($resp['status'] != 0){
        die_err_code($resp['status'], __LINE__);
    }
    return $resp;
}

/*
  功能描述: 修改密码
  入参: 
    userid       手机号
    passwd       新密码
    oldpasswd    老密码
  出参:
    vo_res        0 成功 ,1001.账号不存在,1007.参数错误,1008.密码错误,其他表示异常
 */
function passwdmodify($body){
    tracelog("passwdmodify"); 
    $resp = exec_procedure($body, 'p_passwd_modify');
    if($resp['status'] != 0){
        die_err_code($resp['status'], __LINE__);
    }
    tracelog($resp);
    return $resp;
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
function userinfo_modify($body){
    
    tracelog("userinfo_modify"); 
    $resp = exec_procedure($body, 'p_userinfo_modify');
    if($resp['status'] != 0){
        die_err_code($resp['status'], __LINE__);
    }
    return $resp;
}

function userinfo_add($body){
    
    tracelog("userinfo_add"); 
    $resp = exec_procedure($body, 'p_userinfo_add');
    if($resp['status'] != 0){
        die_err_code($resp['status'], __LINE__);
    }
    return $resp;
}

/*功能描述: 用户系统中退出*/

function logout(){
    setSessonUserData('userid',"");
    setSessonUserData('account',"");
    setSessonUserData('token',"");
    session_destroy();
    $resp_arr = array('status'=>0);
    return $resp_arr;
}
/*
功能描述:账号绑定(系统账号绑第3方账号,这种场景是系统账号登录后,做第3方账号绑定;第3方账号绑系统账号,这种场景是第3方登录后后要求用户填写手机号码)
  入参: 
        openid	　	   第三方标识
        account       系统账号
        thirdtype	  	绑定第3方账号类型: 3.QQ,4.微信,5.微博
  出参: 
        vo_res   0.成功, 1001.账号不存在,1007.参数错误,9999.数据异常
 */
function bind3account($body) {
    tracelog("bind3account"); 
    $resp = exec_procedure($body, 'p_bind3account');
    if($resp['status'] != 0){
        die_err_code($resp['status'], __LINE__);
    }
    return $resp;

}

/*
产生图片验证码
注意：下面参数的默认值，在php接口里
必填项：
imgid：需要产生验证码的图片id
选填项：（从左到右）
fontSize 验证码字体大小   默认36
length： 验证码位数       默认4个字
useNoise：是否添加杂点    默认1
useCurve：是否画混淆曲线  默认0
 */
function getpicCode($body) {
    require_once(dirname(__FILE__)."/Verify.class.php");
    $fontSize = isset($body['fontSize'])&&$body['fontSize']!='undefined'?$body['fontSize']:36;
    $length = isset($body['length'])&&$body['length']!='undefined'?$body['length']:4;
    $useNoise = isset($body['useNoise'])&&$body['useNoise']!='undefined'?$body['useNoise']:1;
    $useCurve = isset($body['useCurve'])&&$body['useCurve']!='undefined'?$body['useCurve']:0;
    $config =    array(
        'fontSize'    =>    $fontSize,      // 验证码字体大小
        'length'      =>    $length,        // 验证码位数
        'useNoise'    =>    $useNoise,      // 关闭验证码杂点
        'useCurve'    =>    $useCurve,
    );
    if(!isset($_SESSION)){
        session_start();
    }
    $Verify = new Verify($config);
    $id = $Verify->entry();
    echo $id;
}
function checkpiccode($body) {
   if (!isset($_SESSION)) {
        session_start();
    }
    $config = array(
        'fontSize' => $body['fontSize'], // 验证码字体大小
        'length' =>$body['length'] , // 验证码位数
        'useNoise' => $body['useNoise'] , // 关闭验证码杂点
        'useCurve' => $body['useCurve']
    );
    $code = $body['code'];
    $Verify = new Verify($config);
    if ($Verify->authcode(strtoupper($code)) == $_SESSION['verify_code']) {
        $resp['status'] = 0;
    } else {
        $resp['status'] = 1003;
    }
    return $resp;
}
function setSessonUserData($field){
    if (!isset($_SESSION)) {
        session_start();
    }

}



