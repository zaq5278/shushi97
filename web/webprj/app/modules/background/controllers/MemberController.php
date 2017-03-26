<?php
/**
 * Created by PhpStorm.
 * User: dodo
 * Date: 2016/9/2
 * Time: 16:23
 */

namespace App\Background\Controllers;

use App\Background\Forms\MemberPwdForm;
use App\Models\Member;
use App\Models\MemberInfo;
use Plugin\Core\QSTBaseSearch;
use Phalcon\Mvc\View;
use Plugin\Login\Models\Users;

class MemberController extends BaseController
{
    public function initialize()
    {
        parent::initialize();
        $menu = $this->menu;
        $menu['user']['link_action'] = "admin/member";
        $menu['user']['active'] = true;
        $this->view->menu_root = $menu;
        $this->view->nav_menu = array_merge(array($this->view->nav_menu, array("name" => "用户管理", "link" => "admin/member")));
        $this->assets->addCss('public/static/background/css/common.css');
    }

    /**
     * 首页列表，不带搜索功能
     */
    public function indexAction()
    {
        $this->response->redirect("admin/Member/search");
    }

    public function searchAction()
    {
        $rows_value = 10;   //一页显示多少条数据
        $key = $this->request->getQuery("search_key");
        $value = $this->request->getQuery("search_value");
        $page = $this->request->getQuery("page");
        $type = $this->request->getQuery("type");

        $pageHeader["title"] = "用户管理";
        $roleid = $_SESSION['auth-identity']['profile'];//获取角色权限
        if(empty($roleid)){
            $pageHeader["action"]["name"] = '<span class="fa fa-download">&nbsp;&nbsp;导出Excel</span>';
            $pageHeader["action"]["link"] = "admin/member/search?type=excel";
        }
        $this->view->page_header = $pageHeader;

        // 数据-搜索栏
        $page_search = new QSTBaseSearch("admin/member/search");

        if ($key == 'nick') {
            $where = $key.' like "%'.$value .'%"';
        }elseif($key == 'recId'){
            $uesrid = MemberInfo::findFirst('nick="'.$value.'"')->userid;
            if(!empty($uesrid)){
                $where = ' recId = '.$uesrid .'';
            }
        }

        $page_search->addCondition([
            'type' => 0,
            'key_default' => empty($key) ? '' : $key,
            'value_default' => $value,
            'keys' => ['nick' => '会员名称','recId' => '推荐人']

        ]);
        $this->view->page_search = $page_search->toArray();

        // 定义表格字段
        $tb_ths = [];
        //$tb_ths[] = array("name" => "微信号", "width" => "150");
        $tb_ths[] = array("name" => "会员名称", "width" => "200");
        $tb_ths[] = array("name" => "微信头像", "width" => "150");
        $tb_ths[] = array("name" => "推荐人", "width" => "100");
        $tb_ths[] = array("name" => "积分", "width" => "135");
        $tb_ths[] = array("name" => "分享关注人数", "width" => "250");
        $tb_ths[] = array("name" => "关注公共号时间", "width" => "250");
        // 定义表格操作
        // $actions = ["0"=>["name"=>"启用"], "1"=>["name"=>"禁用"]];
        $op = array(
        );
        $starNum = empty($page) || $page == 1 ? 0 : ($page-1) * $rows_value;
        //获取列表数据
        $ret = [];

        $wheres = empty($where) ? ' 1 = 1 ' : $where;
        //$data = Member::find(['conditions' =>$where,'order' => 'id desc','limit' =>['number' => 10,'offset' =>$starNum]]);
        $where = empty($where) ? '' : ' and mi.'.$where;
        if(!empty($type)){
            $sqlss = 'select mi.nick,mi.recId,mi.integral,mi.follow_num,mi.follow_time from qst_member m LEFT JOIN qst_member_info mi on m.id = mi.userid where mi.nick != "" '.$where.' ORDER BY mi.follow_time desc,m.id desc';
            $memberData = $this->db->query($sqlss);
            while ($memberInof = $memberData->fetch(2)) {
                $memberInof['follow_time'] = empty($memberInof['follow_time'])?'未关注':date('Y-m-d H:i',$memberInof['follow_time']);
                $memberInof['recId'] = $this->getUserInof($memberInof['recId']);
                $ret[] = $memberInof;
            }
            $title = date('Y-m-d')."会员信息";
            $titlename = ['微信昵称','推荐人','积分','分享关注人数','关注公共号时间'];
            $this->excelData($ret,$titlename,$title);
        }
        $strSQL = 'select m.id from qst_member m LEFT JOIN qst_member_info mi on m.id = mi.userid where mi.nick != "" '.$where;
        $memberNum = $this->db->query($strSQL);
        while ($memberInfo = $memberNum->fetch(2)) {
            $membersd[] = $memberInfo['id'];
        }

        $sqlss = 'select m.id,m.account,mi.headurl,mi.nick,mi.recId,mi.integral,mi.follow_num,mi.follow_time from qst_member m LEFT JOIN qst_member_info mi on m.id = mi.userid where mi.nick != "" '.$where.' ORDER BY mi.follow_time desc,m.id desc limit '.$starNum.','.$rows_value.'';
        $memberData = $this->db->query($sqlss);
        while ($memberInof = $memberData->fetch(2)) {
            $memberInof['follow_time'] = empty($memberInof['follow_time'])?'未关注':date('Y-m-d H:i',$memberInof['follow_time']);
            $memberInof['recId'] = $this->getUserInof($memberInof['recId']);
            $ret[] = $memberInof;
        }
        $using = array(
            ["field" => 'nick'],
            ["field" => 'headurl',"type"=>"image"],
            ["field" => 'recId'],
            ["field" => 'integral'],
            ["field" => 'follow_num'],
            ["field" => 'follow_time']
        );

        // 定义使用数据
        $tb_trs = array(
            "data" => $ret,
            "using" => $using,
            "op" => $op
        );

        // 数据-表格列表
        $page_table["ths"] = $tb_ths;
        $page_table["trs"] = $tb_trs;
        $this->view->page_table = $page_table;

        // 页码
//        $params = $this->request->getQuery();
        //获取总数据
        $data_count = count($membersd);
        $total = ceil($data_count/$rows_value);
        $pagination_cur = $this->request->getQuery("page", null, 1);
        $uriStr = $this->request->getURI();
        $uriArray = parse_url($uriStr);
        $queryStr = $uriArray['query'];
        parse_str($queryStr, $queryArray);
        $queryArray['page'] = "";
        $page_pagination = [
            "url" => $this->url->get("admin/member/search?" . http_build_query($queryArray)),
            "cur" => $pagination_cur,
            "total" => $total,
            "rows_value" => $rows_value,
            'data_total' => $data_count
        ];
        $this->view->tb_page = $page_pagination;

        // 使用普通模板
        $this->view->pick("member/index");
    }
    /**
     * 删除新的广告
     */
    public function deleteAction()
    {
        $type = null;
        try {
            if ($this->request->isGet()) {
                $bannerId = $this->request->getQuery("id");
                $banner = Banners::findFirstById($bannerId);
                if ($banner) {
                    $type = $banner->type;
                    $banner->delete();
                }
            }
        } catch (\Exception $e) {
            $this->flash->error($e->getMessage());
        }
        $this->response->redirect("admin/banner/index" . "?type=" . $type);
    }

    /**
     * 开始编辑
     */
    public function editAction()
    {
        $type = null;
        // 初始化轮播图编辑页面
        $this->view->action_url = "admin/banner/edit";
        $paramtype =array();
        $paramtype['ptvalue']['1']='活动详情';
        $paramtype['ptvalue']['2']='商家详情';
        //$paramtype =null;
        $formBanner = new BannerForm(null, $paramtype);
        if ($this->request->isPost()) {
            $dataBanner = $this->request->getPost();
            $banner = Banners::findFirstById($dataBanner['id']);
            $type = $banner->type;
            if (!$formBanner->isValid($dataBanner, $banner)) {
                foreach ($formBanner->getMessages() as $message) {
                    $this->flash->error($message);
                    break;
                }
            } else {
                if ($this->request->hasFiles()) {
                    $fileUrl = "";
                    // Print the real file names and sizes
                    foreach ($this->request->getUploadedFiles() as $file) {
                        if (!empty($file->getName()) && $file->getSize() > 0) {
                            $fileUrl = $this->upload->upload($file, false);
                        }
                        break;
                    }
                    if (!empty($fileUrl)) {
                        // delete file
                        $this->upload->deleteFile($banner->image_ur);
                        $banner->image_url = $fileUrl;
                    }
                }
                $banner->save();
                $formBanner->clear();
                // success redirect(admin/banner/index)
                $this->response->redirect("admin/banner/index" . "?type=" . $type);
            }
            $this->response->redirect("admin/banner/index" . "?type=" . $type);
        } else {
            $this->view->edit_id = $this->request->getQuery("id");
            $banner = Banners::findFirstById($this->view->edit_id);
            if ($banner) {
                $this->view->form_edit = true;
                $formBanner->setEntity($banner);

                $paramtype['edit']='true';
                $formBanner = new BannerForm($banner,$paramtype);
                $type = $banner->type;
            }
        }

        $this->view->cancel_url = "admin/banner/index" . "?type=" . $type;
        // 初始化轮播图数据
        $banners = Banners::findByType($type);
        $this->view->banners = $banners;
        $this->view->form = $formBanner;
        $this->view->pick('banner/index');
    }

    public function saveAction()
    {
        if ($this->request->isPost()) {
            // 1. get the permission base the id
            $oldPwd = $this->request->getPost("oldPwd");
            $newPwd = $this->request->getPost("newPwd");
            $comPwd = $this->request->getPost("comPwd");

            $account = $this->session->get('auth-identity')['account'];

            $user = Users::findFirstByAccount($account);
            if ($user == false) {
                return $this->response->redirect('admin/member/modifyPwd');
            }
            // Check the password
            if (!$this->security->checkHash($oldPwd, $user->password)) {
                return $this->response->redirect('admin/member/modifyPwd');
            }
            if($newPwd != $comPwd){
                return $this->response->redirect('admin/member/modifyPwd');
            }
            $this->login->setPassword($user->id,$newPwd);
            return $this->response->redirect('admin/member/modifyPwd');
            //return $this->ajax_return(0, "success");
        }
        return $this->ajax_return(1, "error");
    }

    public function modifyPwdAction(){
        $this->addLibJs("libs/jsapi/editor_init.js");
        $this->addLibJs("libs/jsapi/qst_fileinput_init.js");

        $menu = $this->menu;
        $menu['rbac']['active'] = true;
        $menu['rbac']['sub_menu']['mangers']['link_action'] = "admin/member/modifyPwd";
        $menu['rbac']['sub_menu']['mangers']['active'] = true;
        $this->view->menu_root = $menu;

        // 数据-页头
        $pageHeader["title"] = "修改密码";
        $pageHeader["action"]["name"] = "客户列表";
        $pageHeader["action"]["link"] = "admin/member/save";
        $this->view->page_header = $pageHeader;

        // 导航菜单
        $this->view->nav_menu = array_merge($this->view->nav_menu, array(array("name" => $pageHeader["title"])));

        // TODO 数据关联ID
        $id = $this->request->getQuery("id");

        // 表单数据
        $form = new MemberPwdForm();
        $form->setAction("admin/member/save");
        $this->view->form = $form;
        $this->view->pick("partials/tp_page_form");
    }

    private function getUserInof($userid){
        $nick = '';
        if(!empty($userid)){
            $nick = MemberInfo::findFirst('userid='.$userid)->nick;
        }
        return $nick;
    }

    /*
    *处理Excel导出
    *@param $datas array 设置表格数据
    *@param $titlename string 设置head
    *@param $title string 设置表头
    */
    public function excelData($datas,$titlename,$filename){
        $this->Excel->getExcel($datas,$titlename,$filename);
        exit;
    }
}