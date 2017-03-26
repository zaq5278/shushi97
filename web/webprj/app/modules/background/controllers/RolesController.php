<?php

namespace App\Background\Controllers;

use App\Background\Forms\RoleForm;

/**
 * Created by PhpStorm.
 * User: miaogang
 * Date: 2016/8/23
 * Time: 11:51
 */
class RolesController extends BaseController
{
    public function initialize()
    {
        parent::initialize();
        // 数据-左侧菜单
        $menu = $this->menu;
        $menu['rbac']['active'] = true;
        $menu['rbac']['sub_menu']['roles']['link_action'] = "admin/roles";
        $menu['rbac']['sub_menu']['roles']['active'] = true;
        $this->view->menu_root = $menu;
        // 数据-导航菜单
        $this->view->nav_menu = array_merge(array($this->view->nav_menu, array("name" => "角色管理", "link" => "admin/roles")));
        // 公共样式
        $this->assets->addCss('public/static/background/css/common.css');
    }

    /**
     * 查询列表页面
     */
    public function indexAction()
    {
        if($this->request->isGet()){
            $this->response->redirect('admin/roles/search');
        }
    }

    /**
     * 执行查询列表中的筛选操作
     */
    public function searchAction()
    {
        // 页面样式和脚本
        $this->addLibJs('libs/jsapi/qst_linkpost.js');
        $this->addLibJs('libs/jsapi/table.js');
        $this->assets->addJs(_LIBS_ . 'libs/js3party/bootstrap-terebentina-sco/1.0.2/js/sco.modal.js', false);
        $this->assets->addJs(_LIBS_ . 'libs/js3party/bootstrap-terebentina-sco/1.0.2/js/sco.confirm.js', false);

        // 数据-页头
        $pageHeader["title"] = "角色管理列表";
        $pageHeader["action"]["name"] = '<span class="fa fa-plus">&nbsp;&nbsp;添加角色</span>';
        $pageHeader["action"]["link"] = "admin/roles/new";
        $this->view->page_header = $pageHeader;

        // 数据-搜索栏
        $key = $this->request->getQuery("search_key");
        $value = $this->request->getQuery("search_value");
        $page_search["link"] = "admin/roles/search";
        $page_search["condition"]["keys"] = array("title" => "角色");
        $page_search["condition"]["key_default"] = $key;
        $page_search["condition"]["value_default"] = $value;
        $this->view->page_search = $page_search;

        // 数据部分
        // 定义表格字段
        $tb_ths = [];
        $tb_ths[] = array("name" => "角色名称", "class" => "text-center", "width" => "150");
        $tb_ths[] = array("name" => "描述");
        $tb_ths[] = array("name" => "创建时间", "width" => "180");
        $tb_ths[] = array("name" => "操作", "class" => "text-center", "width" => "150");
        // 定义表格操作
        $actions = ["0" => ["name" => "启用"], "1" => ["name" => "禁用"]];
        $op = array(
            ["id_pre" => "sw_", "link" => "admin/roles/enable", "method" => "post", "multiple" => $actions, "key" => "activity"],
            ["id_pre" => "et_", "name" => "编辑", "link" => "admin/roles/edit"],
            ["id_pre" => "del_", "name" => "删除", "link" => "admin/roles/delete", "method" => "post"]
        );

        // 获取角色数据
        $data = [];
        if (empty($value)) {
            $data = $this->rbac->getRoles();
        } else {
            $data = $this->rbac->getRolesWith($key, $value);
        }
        foreach($data as $key => $value)
        {
            $data[$key]['create_time'] = date('Y-m-d H:i',$value['create_time']);
        }
        // 定义使用的字段
        $using = array(
            ["field" => "title"],
            ["field" => "remarks"],
            ["field" => "create_time"]
        );

        // 定义使用数据
        $tb_trs = array(
            "data" => $data,
            "using" => $using,
            "start" => 1,
            "op" => $op
        );

        // 数据-表格列表
        $page_table["ths"] = $tb_ths;
        $page_table["trs"] = $tb_trs;
        $this->view->page_table = $page_table;

        // 使用普通模板
        $this->view->pick("public/list_normal");
    }

    /**
     * 执行查询列表页面中的新增操作
     */
    public function newAction()
    {
        if($this->request->isGet()){
            $this->addLibCss('libs/js3party/switchery/0.8.1/dist/switchery.min.css');
            $this->addLibJs('libs/js3party/switchery/0.8.1/dist/switchery.min.js');
            $this->addLibJs('libs/js3party/jquery-form/3.46.0/jquery.form.min.js');
            $this->addLibJs('libs/jsapi/switch_init.js');
            $this->addLibJs('libs/jsapi/form_new.js');
            $this->addLibJs('libs/js3party/bootstrap-terebentina-sco/1.0.2/css/sco.message.css');
            $this->addLibJs('libs/js3party/bootstrap-terebentina-sco/1.0.2/js/sco.message.js');
            $this->addLibJs('libs/jsapi/roles/roles-editor.js');

            $pageHeader["title"] = "添加角色";
            $pageHeader["action"]["name"] = '角色列表';
            $pageHeader["action"]["link"] = "admin/roles";
            $this->view->page_header = $pageHeader;

            $this->view->nav_menu = array_merge($this->view->nav_menu, array(array("name" => $pageHeader["title"])));
            $permissions = $this->rbac->getPermissions(array('conditions' => "activity=1", "columns"=>"id, pid, title"));
            $roles = $this->rbac->getRoles(array('conditions' => "activity=1", "columns"=>"id, title"));
            $pRoles = [];
            $pRoles[$this->rbac->getSupperRoleId()] = $this->rbac->getSupperRoleName();
            foreach ($roles as $role){
                $pRoles[$role['id']] = $role['title'];
            }
            $pmFormData = [];
            foreach ($permissions as $item) {
                $itemData = [];
                $itemData['id'] = $item['id'];
                $itemData['label'] = $item['title'];
                $itemData['extData'] = ['pid' => $item['pid']];
                $itemData['create_time'] = $item['create_time'];
                $pmFormData[] = $itemData;
            }
            $form = new RoleForm(['activity'=>1, "parent"=>"0"], ["permissions" => $pmFormData, "parentRoles"=>["roles" => $pRoles]]);
            $form->setAction("admin/roles/create");
            $this->view->form = $form;
        }else{
            return $this->response->setStatusCode(403);
        }
    }

    public function createAction()
    {
        if($this->request->isPost()){
            $this->log($this->request->getPost());
            $activity = $this->request->getPost("activity");
            $active = 0;
            if(isset($activity)){
                $active = 1;
            }
            $ret = $this->rbac->createRole($this->request->getPost('parent'), $this->request->getPost('title'),
                $active, $this->request->getPost("remarks"));
            if($ret < 0){
                if(-1 == $ret){
                    return $this->responseJson(null, "1007");
                }else if(-2 == $ret){//角色名重复
                    return $this->responseJson(null, "1017");
                }else{//数据库操作失败或未知错误
                    return $this->response->setContent(500);
                }
            }
            $permissions = $this->request->getPost('permissions');
            if(is_array($permissions)){
                $this->rbac->updateRolePermissions($ret, $permissions);
            }
            return $this->responseJson();
        }else{
            return $this->response->setStatusCode(403);
        }
    }

    /**
     * 执行查询列表中的编辑操作
     */
    public function editAction()
    {
        if($this->request->isGet()){
            $this->addLibCss('libs/js3party/switchery/0.8.1/dist/switchery.min.css');
            $this->addLibJs('libs/js3party/switchery/0.8.1/dist/switchery.min.js');
            $this->addLibJs('libs/jsapi/switch_init.js');
            $this->addLibJs('libs/js3party/jquery-form/3.46.0/jquery.form.min.js');
            $this->addLibJs('libs/jsapi/form_edit.js');
            $this->addLibCss('libs/js3party/bootstrap-terebentina-sco/1.0.2/css/sco.message.css');
            $this->addLibJs('libs/js3party/bootstrap-terebentina-sco/1.0.2/js/sco.message.js');
            $this->addLibJs('libs/jsapi/roles/roles-editor.js');

            $pageHeader["title"] = "编辑角色";
            $pageHeader["action"]["name"] = '角色列表';
            $pageHeader["action"]["link"] = "admin/roles";
            $this->view->page_header = $pageHeader;
            $this->view->nav_menu = array_merge($this->view->nav_menu, array(array("name" => $pageHeader["title"])));

            $roleId = $this->request->getQuery('id');
            $rolePermissions = $this->rbac->getRolesPermissions($roleId);
            $permissions = $this->rbac->getPermissions(array('conditions' => "activity=1", "columns"=>"id, pid, title"));
            $roles = $this->rbac->getRoles(array('conditions' => "activity=1", "columns"=>"id, title"));
            $pRoles = [];
            $pRoles[$this->rbac->getSupperRoleId()] = $this->rbac->getSupperRoleName();
            foreach ($roles as $role){
                if($role['id'] == $roleId){//不能将父权限修改为自身
                    continue;
                }
                $pRoles[$role['id']] = $role['title'];
            }

            foreach ($permissions as $index => $permission){
                foreach ($rolePermissions as $rolePermission){
                    if($rolePermission['permission_id'] == $permission['id']){
                        $permissions[$index]['check'] = true;
                        break;
                    }
                }
            }

            $pmFormData = [];
            foreach ($permissions as $item) {
                $itemData = [];
                $itemData['id'] = $item['id'];
                $itemData['label'] = $item['title'];
                $itemData['extData'] = ['pid' => $item['pid']];
                if (isset($item['check'])) {
                    $itemData['check'] = "checked";
                }
                $pmFormData[] = $itemData;
            }
            $role = $this->rbac->getRoleById($roleId)->toArray();
            $form = new RoleForm($role, ["permissions" => $pmFormData,
                "parentRoles"=>["roles" => $pRoles, "attributes"=>['value'=>$role['parent_id'], "disabled"=>"disabled"]]
            ]);
            $form->setAction("admin/roles/save");
            $this->view->form = $form;
            $this->view->pick("partials/tp_page_form");
        }else{
            return $this->response->setStatusCode(403);
        }
    }

    /**
     * 编辑页面执行保存操作
     */
    public function saveAction()
    {
        if($this->request->isPost()){
            $this->log($this->request->getPost());
            $activity = $this->request->getPost("activity");
            $active = 0;
            if(isset($activity)){
                $active = 1;
            }
            $ret = $this->rbac->updateRole($this->request->getPost('id'), $this->request->getPost('parent'),
                $this->request->getPost('title'), $active, $this->request->getPost("remarks"));
            if($ret < 0){
                if(-1 == $ret){
                    return $this->responseJson(null, "1007");
                }else if(-2 == $ret){//角色不存在
                    return $this->responseJson(null, "1013");
                }else{//数据库操作失败或未知错误
                    return $this->response->setContent(500);
                }
            }
            $permissions = $this->request->getPost('permissions');
            if(!is_array($permissions)){
                $permissions = [];
            }
            $this->rbac->updateRolePermissions($ret, $permissions);
            return $this->responseJson();
        }else{
            return $this->response->setStatusCode(403);
        }
    }

    /**
     * 列表页面执行删除操作
     */
    public function deleteAction()
    {
        $ret = $this->rbac->removeRole($this->request->getQuery('id'));
        if(-1 == $ret){
            return $this->responseJson(null, '1019');
        }
        return $this->responseJson();
    }

    /**
     * 角色启用、禁用
     */
    public function enableAction()
    {
        $roleId = $this->request->getQuery("id");
        $roleActivity = $this->request->getQuery("activity");
        $roleActivity = boolval(intval($roleActivity));
        $roleActivity = !$roleActivity;
        $result = $this->rbac->enableRole($roleId, $roleActivity);
        $activity = $result ? $roleActivity : !$roleActivity;
        $data = array(
            "text" => !empty($activity) ? "禁用" : "启用",
            "href" => $this->url->get("admin/roles/enable", ["id" => $roleId, "activity" => (empty($activity) ? 0 : 1)])
        );
        return $this->responseJson();
    }

    public function permissionsAction()
    {
        if($this->request->isGet()){
            $roleId = $this->request->getQuery("role");
            if(!isset($roleId)){
                $this->log("role id is required for get role permissions");
                return $this->responseJson(null, "1007");
            }
            $permissions = $this->rbac->getRolesPermissions($roleId);
            $permissionsArray = [];
            foreach ($permissions as $permission){
                $permissionsArray[] = $permission['permission_id'];
            }
            return $this->responseJson(array('data' => $permissionsArray));
        }else{
            return $this->response->setContent(403);
        }
    }
}