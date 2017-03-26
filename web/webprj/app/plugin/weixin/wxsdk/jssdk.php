<?php
class JSSDK {
    private $appId;
    private $appSecret;
    public function __construct($appId, $appSecret) {
        $this->appId = $appId;
        $this->appSecret = $appSecret;
    }

    public function getSignPackage() {
        //$jsapiTicket =  file_get_contents($ticket);
        
         $jsapiTicket = $this->getJsApiTicket();
         //tracelog("getSignPackage---------------1");
         //tracelog($jsapiTicket);
        // 注意 URL 一定要动态获取，不能 hardcode.
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $timestamp = time();
        $nonceStr = $this->createNonceStr();
        // 这里参数的顺序要按照 key 值 ASCII 码升序排序
        $string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";
        $signature = sha1($string);
        $signPackage = array(
            "appId"     => $this->appId,
            "nonceStr"  => $nonceStr,
            "timestamp" => $timestamp,
            //"url"       => $url,
            "signature" => $signature,
            //"rawString" => $string
        );
        return $signPackage;
    }

    public function getSignsPackage($url) {
        $jsapiTicket = $this->getJsApiTicket();
        // 注意 URL 一定要动态获取，不能 hardcode.
        $url = $url;
        $timestamp = time();
        $nonceStr = $this->createNonceStr();

        // 这里参数的顺序要按照 key 值 ASCII 码升序排序
        $string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";
        $signature = sha1($string);
        $signPackage = array(
            "appId"     => $this->appId,
            "nonceStr"  => $nonceStr,
            "timestamp" => $timestamp,
            "url"       => $url,
            "signature" => $signature,
            "rawString" => $string
        );
        return $signPackage;
    }

    private function createNonceStr($length = 16) {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }
    private function getJsApiTicket() {
        // jsapi_ticket 应该全局存储与更新，以下代码以写入到文件中做示例
       tracelog("getJsApiTicket---------------1");
        $data = json_decode(file_get_contents(__DIR__ . "/jsapi_ticket.json"));
        if ($data->expire_time < time()) {
            $accessToken = $this->getAccessToken();
            tracelog($accessToken);
            $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=$accessToken";
            $res = $this->httpclient($url) ;
            $ticket = $res->ticket;
            if ($ticket) {
                $data->expire_time = time() + 7000;
                $data->jsapi_ticket = $ticket;
                $fp = fopen("jsapi_ticket.json", "w");
                fwrite($fp, json_encode($data));
                fclose($fp);
            }
        } else {
            $ticket = $data->jsapi_ticket;
        }
        
        return $ticket;
    }

    private function getAccessToken() {
    // access_token 应该全局存储与更新，以下代码以写入到文件中做示例
        tracelog("getAccessToken---------------1");
        $data = json_decode(file_get_contents( __DIR__ ."/access_token.json"));
        if ($data->expire_time < time()) {
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$this->appId&secret=$this->appSecret";
            $res = $this->httpclient($url);
            $access_token = $res->access_token;
            if ($access_token) {
                $data->expire_time = time() + 7000;
                $data->access_token = $access_token;
                $fp = fopen("access_token.json", "w");
                fwrite($fp, json_encode($data));
                fclose($fp);
           
            }
        } else {
            $access_token = $data->access_token;
        }
        return $access_token;
    }

     private function httpclient($url){
       $ch = curl_init();
       curl_setopt($ch, CURLOPT_URL, $url);
       curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
       curl_setopt($ch, CURLOPT_HEADER, 0);
       curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
       $output = curl_exec($ch);
       tracelog("httpclient---------------");
       tracelog($output);
       curl_close($ch);
       $arr_out = json_decode($output);
       return $arr_out;
    }

    /**
     * POST 请求
     * @param string $url
     * @param array $param
     * @param boolean $post_file 是否文件上传
     * @return string content
     */
    private function http_post($url,$param,$post_file=false){
        $oCurl = curl_init();
        if(stripos($url,"https://")!==FALSE){
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($oCurl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
        }
        if (is_string($param) || $post_file) {
            $strPOST = $param;
        } else {
            $aPOST = array();
            foreach($param as $key=>$val){
                $aPOST[] = $key."=".urlencode($val);
            }
            $strPOST =  join("&", $aPOST);
        }
        curl_setopt($oCurl, CURLOPT_URL, $url);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt($oCurl, CURLOPT_POST,true);
        curl_setopt($oCurl, CURLOPT_POSTFIELDS,$strPOST);
        $sContent = curl_exec($oCurl);
        $aStatus = curl_getinfo($oCurl);
        curl_close($oCurl);
        if(intval($aStatus["http_code"])==200){
            return $sContent;
        }else{
            return false;
        }
    }
    /**
     * 获取微信授权码
     * @param type $calbackUrl   重定向的URL 地址
     * @param type $isScope     SCOPE 模式,1snsapi_userinfo ,0 表示snsapi_base
     * @param type $wxState     用户自定义state参数
     */
    public function wxAuthorization($calbackUrl){
       
        $isScope = 1;
        $wxState = 5;
        $REDIRECT_URI=$calbackUrl ;//$URL.'/index2.php';
        $scope="";
         if($isScope ==  1){
              $scope='snsapi_userinfo';//需要授权
         }
         else {
             $scope='snsapi_base';
         }
        $url='https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$this->appId;
        $url=$url.'&redirect_uri='.urlencode($REDIRECT_URI);
        $url=$url.'&response_type=code&scope='.$scope;
        $url=$url.'&state='.$wxState.'#wechat_redirect';
        return $url;
     }
 
     /**
      * 获取微信用户的 openid,nickname 昵称, headimgurl 头像
      * @return JSON
      */
    public function wxLogin($code){
       $url= "https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$this->appId."&secret=".$this->appSecret."&code=$code&grant_type=authorization_code";
       $arr = $this->httpclient($url);
       
       tracelog('token:'.$arr->access_token.' openid:'.$arr->openid);
       $url="https://api.weixin.qq.com/sns/userinfo?access_token=$arr->access_token&openid=$arr->openid&lang=zh_CN";
       $arr1 = $this->httpclient($url);

       return $arr1 ;
    }

    /**
     * 获取微信用户的 详细信息判断是否关注
     * @return JSON
     */
    public function wxUserInfo($openid){
        $access_token = $this->getAccessToken();
        $url = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$access_token.'&openid='.$openid.'&lang=zh_CN';
        $arr1 = $this->httpclient($url);
        return $arr1 ;
    }

    /**
     * 根据公共号建的模板发送消息
     * @return JSON
     */
    public function setTMIndustry($id1,$id2=''){
        if ($id1) $data['industry_id1'] = $id1;
        if ($id2) $data['industry_id2'] = $id2;
        $access_token = $this->getAccessToken();
        $url = 'https://api.weixin.qq.com/cgi-bin/template/api_set_industry?access_token='.$access_token;
        $arr1 = $this->httpclient($url);
        return $arr1 ;
    }

    /**
     * 根据模板id发送消息通知
     * @param $data
     * @return bool|mixed
     */
    public function sendTemplateMessage($data){
        $access_token = $this->getAccessToken();
        $url = 'https://api.weixin.qq.com/cgi-bin/message/template/send?access_token='.$access_token;
        $result = $this->http_post($url,self::json_encode($data));
        if($result){
            $json = json_decode($result,true);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return false;
            }
            return $json;
        }
        return false;
    }

    /**
     * 微信api不支持中文转义的json结构
     * @param array $arr
     */
    static function json_encode($arr) {
        if (count($arr) == 0) return "[]";
        $parts = array ();
        $is_list = false;
        //Find out if the given array is a numerical array
        $keys = array_keys ( $arr );
        $max_length = count ( $arr ) - 1;
        if (($keys [0] === 0) && ($keys [$max_length] === $max_length )) { //See if the first key is 0 and last key is length - 1
            $is_list = true;
            for($i = 0; $i < count ( $keys ); $i ++) { //See if each key correspondes to its position
                if ($i != $keys [$i]) { //A key fails at position check.
                    $is_list = false; //It is an associative array.
                    break;
                }
            }
        }
        foreach ( $arr as $key => $value ) {
            if (is_array ( $value )) { //Custom handling for arrays
                if ($is_list)
                    $parts [] = self::json_encode ( $value ); /* :RECURSION: */
                else
                    $parts [] = '"' . $key . '":' . self::json_encode ( $value ); /* :RECURSION: */
            } else {
                $str = '';
                if (! $is_list)
                    $str = '"' . $key . '":';
                //Custom handling for multiple data types
                if (!is_string ( $value ) && is_numeric ( $value ) && $value<2000000000)
                    $str .= $value; //Numbers
                elseif ($value === false)
                    $str .= 'false'; //The booleans
                elseif ($value === true)
                    $str .= 'true';
                else
                    $str .= '"' . addslashes ( $value ) . '"'; //All other things
                // :TODO: Is there any more datatype we should be in the lookout for? (Object?)
                $parts [] = $str;
            }
        }
        $json = implode ( ',', $parts );
        if ($is_list)
            return '[' . $json . ']'; //Return numerical JSON
        return '{' . $json . '}'; //Return associative JSON
    }
    /* 下载媒体素材接口
     * @param {String} 		url   	  
     *  "http://file.api.weixin.qq.com/cgi-bin/media/get?access_token="  图片素材下载
     *  "http://api.weixin.qq.com/cgi-bin/media/get?access_token="       视频/音频临时素材下载
     * @param {String} 		$type 	        
     * @param {String} 		mediaId             下载媒体素材id
     * @param {String} 		savePath            存储媒体素材的文件名,包括绝对路径
     * @returns｛bool｝          result              true 成功，false 失败
    */

    function downloadMedia($type, $mediaId, $savePath){
        $access_token=$this->GetToken();
        if($type==self::MSGTYPE_IMAGE)
        {
            $url="http://file.api.weixin.qq.com/cgi-bin/media/get?access_token=";
        }
        elseif($type==self::MSGTYPE_VOICE or $type==self::MSGTYPE_VIDEO)
        {
            $url="http://api.weixin.qq.com/cgi-bin/media/get?access_token=";
        }
        else 
        {
            $this->DebugLog('the format is not support,type='.$type, __METHOD__);
            return false;
        }
        $ch = curl_init();
        $surl =  $url.$access_token . "&media_id=" . $mediaId;
        curl_setopt($ch, CURLOPT_URL, $surl);		
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        $response = curl_exec($ch);
        $result = false;
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE) ;
        if ($status == '200') {
            if('' != $response){
                if(file_exists($savePath)){
                        unlink($savePath);
                }
                $file = fopen($savePath, "a");
                fwrite($file, $response);
                fclose($file);
                $result = true;
            }	
        }
        curl_close($ch);
        return $result;
    }

    /**
     * @param type $url
     * https://api.weixin.qq.com/cgi-bin/media/upload?access_token=   临时素材上传接口
     * @param type $access_token                                     微信访问接口使用的token值
     * @param type $type                                             分别有图片（image）、语音（voice）、视频（video）和缩略图（thumb）
     * @param type $filename                                         媒体素材文件名 @D:/wamp/www/webapi/weixin/voice.amr
     * return   JSON 数据 如：  "status":"200","data":"{\"errcode\":40001,\"errmsg\":\"invalid credential, access_token is invalid or not latest hint: [41.K9a0306ent2]\"}"}
     *     status 200 表示HTTP 成功 其他值HTTP 失败，文件是否上传成功 看errcode  
     */
     function uploadMedia($type,$filename){
        $post_string = array(
            'file1'=>'@'.$filename
            ); 
        $url='https://api.weixin.qq.com/cgi-bin/media/upload?access_token=';
        $access_token=$this->GetToken();
        $surl =  $url.$access_token ."&type=" . $type;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $surl);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if($type==self::MSGTYPE_IMAGE)
        {
            curl_setopt($ch, CURLINFO_CONTENT_TYPE , 'image/jpeg');
        }
        $response = curl_exec($ch);  
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE) ;
        curl_close($ch);
        $result=array();
        if ($status == '200') {
            if('' != $response){
               $result=array("status"=>"200","data"=>json_decode($response,true));
               $this->DebugLog('respose='.json_encode($result), __METHOD__);
               return $result ;

            }	
        }
        $result=array("status"=>"1","data"=>"上传失败");
        $this->DebugLog('upload file('.$filename.') type='.$type.' respose='.json_encode($result), __METHOD__);
        return $result;
    }
    /*
    function TestdownloadMedia(){
        $access_token = "kP6fuJ9eB3QF0Nsl9eN1ZOqGnkRwJc-ooYpfuKCZSnBC1NPLacdt3OxV_BT8yuYlbT9SC6Igy03wUoiEchaEpdZAmsHSsOHabgoIneu_smoHGIdAFAUTV";
        $storeName ="./voice.amr";
        $mediaid="VtiQK7GY_GtLGZFXv1rCC-Fa5nKfp0FP2BfkJ04f-fakeYl5v1eyElZHyZa4BInv";
        $url="http://api.weixin.qq.com/cgi-bin/media/get?access_token=";  
        $ret = downloadMedia($url,$access_token,$mediaid, $storeName);
        if(false == $ret){
                $resp = array('status' => 'failed');
        }else{
                $resp = array('status' => 'success', 'url' => $storeName);
        }
        echo json_encode($resp);
    }

    function TestUploadMedia(){

        $access_token = "kP6fuJ9eB3QF0Nsl9eN1ZOqGnkRwJc-ooYpfuKCZSnBC1NPLacdt3OxV_BT8yuYlbT9SC6Igy03wUoiEchaEpdZAmsHSsOHabgoIneu_smoHGIdAFAUTV";
        $type="voice";
        $url="https://api.weixin.qq.com/cgi-bin/media/upload?access_token=";  
        $filename ="D:/wamp/www/webapi/weixin/voice.amr";
        $ret = uploadMedia($url,$access_token,$type,$filename);
        if(false == $ret){
                $resp = array('status' => 'failed');
        }else{
                $resp = array('status' => 'success', 'url' => $type);
        }
        echo json_encode($resp);
    }
    TestUploadMedia();
    */
}
