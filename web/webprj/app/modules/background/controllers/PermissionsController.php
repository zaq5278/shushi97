<?php

namespace App\Background\Controllers;

/**
 * Created by PhpStorm.
 * User: miaogang
 * Date: 2016/8/23
 * Time: 11:51
 */
class PermissionsController extends BaseController
{
    public function initialize()
    {
        parent::initialize();
        $menu = $this->menu;
        $menu['rbac']['active'] = true;
        $menu['rbac']['sub_menu']['permissions']['link_action'] = "";
        $menu['rbac']['sub_menu']['permissions']['active'] = true;
        $this->view->menu_root = $menu;
        $this->view->nav_menu = array($this->view->nav_menu, array("name" => "权限管理", "link" => "admin/permissions"));
        $this->assets->addCss('public/static/background/css/common.css');
    }

    /**
     * 查询列表页面
     */
    public function indexAction()
    {
        $this->addLibCss('libs/js3party/zTree/css/zTreeStyle/zTreeStyle.css');
        $this->addLibJs('libs/js3party/zTree/js/jquery.ztree.core.min.js');
        if($this->request->isGet()){
            $rbac = $this->getDI()->get("rbac");
            $permissions = $rbac->getPermissions();
            if(!isset($permissions[0])){// no permissions from menu.
                $ret = $rbac->buildPermissionsFromMenu($this->getDI()->get('router')->getModuleName(), include __DIR__."/../config/menu.php");
                if(false == $ret){
                    return $this->response->setStatusCode(500);
                }
            }
            $permissions = $rbac->getPermissions();
            $this->view->setVar("permissions", $permissions);

        }else{
            return $this->response->setStatusCode(403);
        }
    }

    public function resourceAction()
    {
        if($this->request->isPost()){
            $reqData = $this->getJsonArrayBody();
            if(isset($reqData['del'])){
                foreach ($reqData['del'] as $permission){
                    $this->rbac->deletePermission($permission);
                }
            }
            if(isset($reqData['modify'])){
                foreach ($reqData['modify'] as $permission){
                    if(!isset($permission['id'])){
                        $this->log("id is required for modification");
                        continue;
                    }
                    $this->rbac->updatePermission($permission['id'], $permission['name'],
                        $permission['controller'], '*', 'ALL', $permission['activity']);
                }
            }
            if(isset($reqData['add'])){
                foreach ($reqData['add'] as $permission){
                    $this->rbac->createPermission($permission['name'], $permission['controller'],  '*', 'ALL', 0, $permission['activity']);
                }
            }
            return $this->responseJson();
        }else{
            return $this->response->setContent(403);
        }
    }

    public function accessAction()
    {
        if($this->request->isPost()){
            $reqData = $this->getJsonArrayBody();
            $this->log($reqData);
            if(isset($reqData['del'])){
                foreach ($reqData['del'] as $permission){
                    $this->rbac->deletePermission($permission);
                }
            }
            if(isset($reqData['modify'])){
                foreach ($reqData['modify'] as $permission){
                    if(!isset($permission['id'])){
                        $this->log("id is required for modification");
                        continue;
                    }
                    $this->rbac->updatePermission($permission['id'], $permission['name'],
                        $permission['controller'], $permission['action'], $permission['method'], $permission['activity']);
                }
            }
            if(isset($reqData['add'])){
                foreach ($reqData['add'] as $permission){
                    $this->rbac->createPermission($permission['name'], $permission['controller'],  $permission['action'],
                        $permission['method'], $permission['pid'], $permission['activity']);
                }
            }
            return $this->responseJson();
        }else{
            return $this->response->setContent(403);
        }
    }
}