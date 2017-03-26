<?php
function qq_login($appid,$appkey,$callbackurl){
    require_once(dirname(__FILE__)."/API/qqConnectAPI.php");
    $config ='{"appid":"'. $appid .'","appkey":"'.$appkey .'","callback":"'. $callbackurl .'","scope":"<?=$val?>,<?=$val?>,<?=$val?>,<?=$val?>,<?=$val?>,<?=$val?>,<?=$val?>,<?=$val?>,<?=$val?>,<?=$val?>,<?=$val?>,<?=$val?>,<?=$val?>,<?=$val?>,<?=$val?>,<?=$val?>,<?=$val?>,<?=$val?>,<?=$val?>,<?=$val?>","errorReport":true,"storageType":"file","host":"localhost","user":"root","password":"root","database":"test"}';
    $qc = new QC($config);
    return $qc->qq_login();
}

function qq_callback() {
    $cb_arr = array();
    $qc = new QC();
    $access_token = $qc->qq_callback();//DE89B12F418C136D96F62898CBC4705E
    $openid = $qc->get_openid();//B8C02925F80EE3B4462B86E7868DAC1D
    $qc1 = new QC($access_token, $openid);
    $resp = $qc1->get_user_info();
    tracelog('qq_callback');
    tracelog($resp);
    $cb_arr = array('access_token'=>$access_token,'openid'=>$openid,'nick'=>$resp["nickname"]);
    return $cb_arr;
}