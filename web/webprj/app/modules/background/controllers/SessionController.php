<?php
/**
 * Created by PhpStorm.
 * User: dodo
 * Date: 2016/8/31
 * Time: 11:10
 */

namespace App\Background\Controllers;

use App\Models\Depot;
use App\Models\Franchise;
use App\Models\Message;
use App\Models\MessageText;
use Plugin\Core\QSTBaseController;
use App\Background\Forms\LoginForm;
use App\Background\Forms\ModifyPwdForm;
use Phalcon\Mvc\View;
use Plugin\Login\Models\Users;

class SessionController extends QSTBaseController
{
    public function indexAction()
    {
        return $this->response->redirect('admin/session/login');
    }

    public function loginAction()
    {
        $form = new LoginForm();
        try {
            if (!$this->request->isPost()) {//GET 请求，显示登陆界面
                if(false != $this->login->getIdentity()){
                    return $this->response->redirect("admin/");
                }
            } else {
                $this->log($this->request->getPost());
                if (/*!$this->security->checkToken() || */$form->isValid($this->request->getPost()) == false) {
                    foreach ($form->getMessages() as $message) {
                        $this->log($message);
                        $this->flash->error($message);
                        break;
                    }
                } else {
                    $this->login->check(array(
                        'account' => $this->request->getPost('account'),
                        'password' => $this->request->getPost('password'),
                        'remember' => $this->request->getPost('remember')
                    ));
                    $roleid = $this->session->get('auth-identity')['profile'];
                    $id = $this->session->get('auth-identity')['id'];
                    $url = '';
                    if($roleid == 1 || empty($roleid)){
                        $url = 'admin/Member/search';
                    }elseif($roleid == 3){
                        $url = 'admin/franchise/franchisem';
                    }elseif($roleid == 2){
                        $url = 'admin/depot/depotm';
                    }
                    //获取未读信息
                    $data = MessageText::find(['conditions' =>' is_delete = 0','order' => 'id desc']);
                    foreach($data as $value)
                    {
                        $messagedata = Message::findFirst('recid = '. $id .'  and messageid = '.$value->id);
                        if(empty($messagedata->id) && $value->roleid == $roleid){
                            $messages = new Message();
                            $messages->sendid = $value->userid;
                            $messages->recid = $id;
                            $messages->messageid = $value->id;
                            $messages->addTime = $value->addTime;
                            $messages->addTime = $value->addTime;
                            $messages->statue = 0;
                            $messages->save();
                        }
                    }
                    return $this->response->redirect($url);
                }
            }
        } catch (\Exception $e) {
            $this->flash->error($e->getMessage());
        }
        $this->view->setRenderLevel(View::LEVEL_ACTION_VIEW);
        $this->view->title = "骑士团开发后台管理系统";
        $this->view->form = $form;
        //var_dump($form);exit;
    }
    //修改密码
    public function modifyPwdAction(){
        $form = new ModifyPwdForm();
        try {
            if (!$this->request->isPost()) {//GET 请求，显示登陆界面
                if(false != $this->login->getIdentity()){
                    return $this->response->redirect("admin/");
                }
            } else {
                    $moblie = $this->request->getPost('moblie');
                    $newPwd = $this->request->getPost('newPwd');
                    $type = $this->request->getPost('type');
                    $isNewPwd = $this->request->getPost('isNewPwd');
                    if($newPwd != $isNewPwd){
                        return $this->response->redirect('admin/session');
                    }
                    $conditons = 'tel = :tel: and role_id = :role_id:';
                    $parameters = [
                        'tel' => $moblie,
                        'role_id' => $type
                    ];
                    $userdata = Users::findFirst([
                        $conditons,
                        'bind' => $parameters,
                    ]);
                    if(!empty($userdata->id)){
                        $this->login->setPassword($userdata->id,$newPwd);
                    }
                    return $this->response->redirect('admin/session');
            }
        } catch (\Exception $e) {
            $this->flash->error($e->getMessage());
        }
        $this->view->setRenderLevel(View::LEVEL_ACTION_VIEW);
        $this->view->title = "修改密码！";
        $this->view->form = $form;
    }
    //发送短信验证码
    public function sendCodeAction(){
        if ($this->request->isPost()) {
            $tel = $this->request->getPost('tel');
            $types = $this->request->getPost('types');
            $ValidNum = $this->request->getPost('ValidNums');

            if(empty($ValidNum)){
                $conditons = 'tel = :tel: and role_id = :role_id:';
                $parameters = [
                    'tel' => $tel,
                    'role_id' => $types,
                ];
                $userdata = Users::findFirst([
                    $conditons,
                    'bind' => $parameters,
                ]);
                if(empty($userdata->id)){
                    return json_encode(['state'=> 1 ,'mess'=>'账号不存在！']);
                }
                $data = [
                    'account' => $tel
                ];
                $datas = $this->sms->sms_general($data);
                print_r($datas);exit;
                return json_encode(['status'=> 0 ,'desc'=>'发送成功！']);
            }else{


            }
        }
    }
    /**
     * @return mixed
     * 退出登录
     */
    public function logoutAction()
    {
        $this->login->remove();
        $this->session->destroy();
        return $this->response->redirect('admin');
    }
}