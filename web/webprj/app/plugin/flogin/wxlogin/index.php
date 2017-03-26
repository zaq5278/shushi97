<?php
require_once(dirname(__FILE__).'/wxapi.class.php');

function wx_login($wxappid ,$callbackurl) {
    //$state 该参数可用于防止csrf攻击（跨站请求伪造攻击），建议第三方带上该参数，可设置为简单的随机数加session进行校验
    $state = 'f3602be4bbb74f808372322233da98fc';
    //$url = "https://open.weixin.qq.com/connect/qrconnect?". "appid=" . $wxappid."&scope=snsapi_login&response_type=code". "&redirect_uri=". urlencode($callbackurl) .'&state='. $state;
    $url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$wxappid.'&redirect_uri='.urlencode($callbackurl).'&response_type=code&scope=snsapi_base&state='.$state.'#wechat_redirect';
    return $url;
}

function wx_callback($wxappid,$wxsecret) {
    $code = $_REQUEST['code'];
    if ($code) { //用户授权登录
        //$tokenurl = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=" . _WXAPPID_ . "&secret=" . _WXSECRET_ . "&code=" . $code . "&grant_type=authorization_code";
        $baseurl = "https://api.weixin.qq.com/sns/oauth2/access_token";
        $wxob = new wxapi();
        //通过code请求access_token
        $parameters = array(
            'appid'=>$wxappid,
            'secret'=>$wxsecret,
            'code'=>$code,
            'grant_type'=>authorization_code
        );
        $tokenurl = $baseurl . '?' . http_build_query($parameters);
        $tokenret = $wxob->http($tokenurl, 'GET');
        //print_r("token:".$tokenret.'<br>');
        $tokenret = json_decode($tokenret,true);
        $token = isset($tokenret['access_token'])?$tokenret['access_token']:''; 
        $openid = isset($tokenret['openid'])?$tokenret['openid']:'';
        tracelog('wx_callback');
        tracelog($tokenret);
        if ($token) {
            $baseinfourl =  "https://api.weixin.qq.com/sns/userinfo";
            $userinfo = array(
                'access_token'=>$token,
                'openid'=>$openid
            );
            $infourl = $baseinfourl.'?'. http_build_query($userinfo);
            $inforet = $wxob->http($infourl, 'GET');
            //有以下信息会返回

            $inforet = json_decode($inforet,true);
            $nick = isset( $inforet['nickname'])? $inforet['nickname']:'';
            $cb_arr = array('access_token' => $token, 'openid' => $openid, 'nick' =>$nick);
            return $cb_arr;
        }
    } else {
        //用户未授权登录，可做跳转，目前不做操作，停留在当前页，用户还可继续扫描
    }
}