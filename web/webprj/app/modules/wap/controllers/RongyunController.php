<?php

namespace App\Wap\Controllers;

use Plugin\Core\QSTBaseController;
use Phalcon\Mvc\View;
use Phalcon\Http\Request;
use Phalcon\Mvc\Model\Query;


/**
 * Display the default index page.
 */
class RongyunController extends QSTBaseController{

    private  $userid;

    public function initialize()
    {
        parent::initialize();
        $this->userid = $this->request->getQuery('sessid');
        if (empty($this->userid)) {
            $this->userid = $this->session->get('userid');
        }
        if (empty($this->userid)) {
            echo json_encode(['status' => 10014, 'desc' => '访问异常！']);
            exit;
        }
    }

    public function ceshiAction(){
        header("Access-Control-Allow-Origin: *");
        header('Access-Control-Allow-Methods: GET, POST, DELETE');

        //if($this->request->isPost()){
        $userid = empty($this->session->get('userid')) ? 1 :$this->session->get('userid') ;
        $nickname = empty($this->session->get('nickname')) ? '游客' : $this->session->get('nickname') ;
        $headimgurl = empty($this->session->get('headimgurl')) ? 'http://www.rongcloud.cn/images/logo.png' : $this->session->get('headimgurl') ;

        $result = $this->rongyun->user()->getToken($userid, $nickname, $headimgurl);
        $result = json_decode($result);
        $result->appkey = _RONGYUNKEY_;
        $result->customerServiceId = _CUSTOMERSERVICEID_;
        echo json_encode($result);exit;
        //}
    }

    public function indexAction(){
        header("Access-Control-Allow-Origin: *");
        header('Access-Control-Allow-Methods: GET, POST, DELETE');

        //if($this->request->isPost()){
        $userid = empty($this->session->get('userid')) ? 1 :$this->session->get('userid') ;
        $nickname = empty($this->session->get('nickname')) ? '游客' : $this->session->get('nickname') ;
        $headimgurl = empty($this->session->get('headimgurl')) ? 'http://www.rongcloud.cn/images/logo.png' : $this->session->get('headimgurl') ;

        $result = $this->rongyun->user()->getToken($userid, $nickname, $headimgurl);
        $result = json_decode($result);
        $result->appkey = _RONGYUNKEY_;
        $result->customerServiceId = _CUSTOMERSERVICEID_;
        echo json_encode($result);exit;
        //}
    }

    public function ceshisAction(){
        print_r($_COOKIE);
        exit;
    }

}


