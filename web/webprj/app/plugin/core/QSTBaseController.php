<?php

/**
 * Phalcon控制器扩展
 * @author MiaoGang
 */

namespace Plugin\Core;
use Phalcon\Http\Response;
use Phalcon\Exception;
use Plugin\Misc\ErrorDescriptions as ErrDesc;
use Plugin\Core\QSTBaseModel;

class QSTBaseController extends \Phalcon\Mvc\Controller {

    public function initialize() {
        $this->view->home_root = _F_BASE_URL_;
    }
    
    /*
     微信JSSDK 调用
     1.现在需要执行的VIEW 对应的controller 中执行 wxjsload 函数
     2.在view的 phtml中引用  {{ partial("api/wxjssdk") }}
     3.参照 wxDemo.js 的示例代码进行调用
     */
    public function wxjssdkload(){
        /*微信授权接口*/
        //$this->log('wxjssdkinit----------------------');
        $signPackage = $this->weixin->jssdk()->getSignPackage();
        $this->view->signPackage= $signPackage;
        //wxjssdk.js 为JSSDK 函数封装的一层接口
        $this->assets->addJs(_LIBS_.'libs/js3party/weixin/wxjssdk.js');
        //wxDemo.js 调用的实例代码,为了测试需要，正式执行需要将 wxDemo.js 引用注释掉
        //$this->assets->addJs(_LIBS_.'libs/js3party/weixin/wxDemo.js');
    }

    /**
     * ajax request
     */
    protected function ajax_return($status, $desc, $out_data=array(), $data=array()){
        $result = array(
            'status' => $status,
            'desc' => $desc,
            'out_data' => $out_data,
            'data' => $data
        );
        echo json_encode($result);
        $this->view->disable();
        $this->log("response:" . json_encode($result));
    }

    /**
    * log with session
    */
    protected function log($msg, $level = \Phalcon\Logger::DEBUG) {
        if (!is_string($msg)) {
            $msg = $this->logger->Array2Json_chn($msg);
        }
        $idSession = $this->session->getId();
        $msg = (isset($idSession) ? "[session:" . $idSession . "] " : "") . $msg;
        $this->logger->log($msg, $level);
    }

    /**
     * log with session
     */
    protected function refundLog($msg, $level = \Phalcon\Logger::DEBUG) {
        if (!is_string($msg)) {
            $msg = $this->logger->Array2Json_chn($msg);
        }
        $idSession = $this->session->getId();
        $msg = (isset($idSession) ? "[session:" . $idSession . "] " : "") . $msg;
        $this->refundLogger->log($msg, $level);
    }

    protected function apiLog($log, $level = \Phalcon\Logger::DEBUG) {
        if (!is_string($log)) {
            $log = json_encode($log);
        }
        $this->logger->log("[".$this->request->getMethod()."][". $this->request->getURI()."] ==> ". $log,$level);
    }
    
    public function responseJson($data = null, $status = 0){
       
        $response = $this->response;
        $content = null;
        if(is_array($data) || is_null($data)){
            $respArr = Array(
                "status" => $status,
                "desc" => ErrDesc::getErrorDesc($status)
            );
            if($data){
                $respArr = array_merge($respArr, $data);
            }
           $this->log($respArr);
            try{
                $content = json_encode($respArr);
            }catch(Exception $e){
                $this->log("error occured while encoding to json string, Exception: ". $e->getMessage());
                $response->setStatusCode(500, "myscoop server error");
            }
        }else{
            $this->log("response unknown data type");
            $response->setStatusCode(500, "myscoop server error");
        }
     
        
        if($content){
            //统一替换
            $content = str_replace("null",'""',$content);
            $response->setContent($content);
        }
        return $response;
    }

    /**
     * @param int $code
     * @return Response
     */
    public function reject($code = 403)
    {
        static $desc = Array(
            401=>'Unauthorized',
            403=>"Forbidden",
        );
        $response = new Response();
        $response->setStatusCode($code, $desc[$code]);
        return $response;
    }

   /*
    * 统一的处理GET请求入参的接口
    */
    public  function getBodyGET($paramsList){
        if(isset($paramsList)){
            $ret = array();
            foreach ($paramsList as $key=>$value){
                $info = isset($_GET[$value]);
                if($info){
                    $ret[$value] =  $_GET[$value];
                }
                else{
                    return $value;
                }
            }
        }
        else{
            return '';
        }
        return $ret;
    }


    /**
     * get decoded json array from http body.
     * @param null $paramsList Array expected parameters list, optional
     * @return int|mixed|null decoded json array on success, else return error code.
     * @throws Exception
     */
    public function getJsonArrayBody($paramsList = null){
        if(isset($paramsList) && !is_array($paramsList)){
            throw new \Phalcon\Exception("JsonArrayBody paramsList must be array");
        }
        $body = $this->request->getRawBody();
        if($body){
            $bodyArr = null;
            try{
                $bodyArr = json_decode($body, true);
                if(is_null($bodyArr)){
                    $this->apiLog("decode json body error, body: ".$body);
                    return 2001;
                }
                if($paramsList){
                    foreach ($paramsList as $key) {
                        if(!isset($bodyArr[$key])){
                            $this->apiLog("Missing parameter: ". $key);
                            return 2002;
                        }
                    }
                }
                return $bodyArr;
            }catch(Exception $e){
                $this->apiLog("decode json body error, Exception: ". $e->getMessage());
                return 2001;
            }
        }else{
            return 2003;
        }
    }
    public function execsql($body, $sql, $type=1, $dbconn=0){
       return \Plugin\Core\QSTBaseModel::execsql($body, $sql,$type,$dbconn);
    }

    public function addLibJs($url){
        $this->assets->addJs(_LIBS_.$url, false);
    }

    public function addLibCss($url){
        $this->assets->addCss(_LIBS_.$url, false);
    }
}