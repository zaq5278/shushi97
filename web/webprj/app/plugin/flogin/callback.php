<?php

require_once("apiuseropt.php");
$platform = getSessonUserData('thirdtype'); //记录平台 3QQ  4微信  5微博
$jumpurl = _JUMPURL_;
//1登录  2绑定
if(getSessonUserData('logintype') =='2'){
    $jumpurl = _BINDJUMP_;
}
    
switch ($platform){
   case 3:
        qq_call($jumpurl,$platform);
        break;
    case 4:
        wx_call($jumpurl,$platform);
        break;
    case 5:
        wb_call($jumpurl,$platform);
        break;
    default :
        tracelog('platform:'.$platform);
        break;
}
/* QQ登录
 * 返回值：array('access_token'=>token,'openid'=>openid,'nick'=>nickname)
 */
function qq_call($jumpurl,$platform){
    require_once(dirname(__FILE__)."/qqlogin/index.php");
    $resp  = qq_callback();
    tracelog('qq_call');
    tracelog($resp);
    $resp['thirdtype'] = $platform;
    $ret=userLogin($resp);
    if ($ret['status']==0){
        header("Location:" . $jumpurl);
    }
}
/* WX登录
 * 返回值 
 */
function wx_call($jumpurl,$platform){
    require_once(dirname(__FILE__)."/wxlogin/index.php");
    $resp  = wx_callback(_WXAPPID_,_WXSECRET_);  
    tracelog('wx_call');
    tracelog($resp);
    $resp['thirdtype'] = $platform;
    $ret=userLogin($resp);
    if ($ret['status']==0){
        header("Location:" . $jumpurl);
    }
    //jumpopdb($resp);
}
/* WB登录
 * 返回值  array('access_token'=>token,'openid'=>openid,'nick'=>nickname)
 */
function wb_call($jumpurl,$platform){
    require_once(dirname(__FILE__)."/wblogin/index.php");
    $resp=wb_callback(_WBAKEY_,_WBSKEY_,_CALLBACK_URL_);
    tracelog('wb_call');
    tracelog($resp);
    $resp['thirdtype'] = $platform;
    $ret=userLogin($resp);
    if ($ret['status']==0){
        header("Location:" . $jumpurl);
    }
  
}