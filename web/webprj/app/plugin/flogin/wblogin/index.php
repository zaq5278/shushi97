<?php
function wb_login($wbkey,$wbskey,$callbackurl){
    require_once( dirname(__FILE__).'/saetv2.ex.class.php' );
    $o = new SaeTOAuthV2( $wbkey , $wbskey);
    $code_url = $o->getAuthorizeURL($callbackurl);
    return $code_url;
}


function wb_callback($wbkey,$wbskey,$callbackurl) {
    require_once( dirname(__FILE__).'/saetv2.ex.class.php' );
    $o = new SaeTOAuthV2($wbkey, $wbskey);
    if (isset($_REQUEST['code'])) {
        $keys = array();
        $keys['code'] = $_REQUEST['code'];
        $keys['redirect_uri'] = $callbackurl; //.'?login_type='.$login_type.'_'.$userid.'_'.$headpic.'_'.$emailnum;
        try {
            $token = $o->getAccessToken('code', $keys);
        } catch (OAuthException $e) {
        }
    }
    if ($token) {
        tracelog('wb_callback');
        tracelog($token);
        $_SESSION['token'] = $token;
        $c1 = new SaeTClientV2($wbkey, $wbskey, $token['access_token']);
        $userinfo = $c1->show_user_by_id($token[uid]);
        setcookie('weibojs_' . $o->client_id, http_build_query($token));
        $cb_arr = array('access_token' => $token['access_token'], 'openid' => $token[uid], 'nick' => $userinfo['name']);
        return $cb_arr;
    }
}