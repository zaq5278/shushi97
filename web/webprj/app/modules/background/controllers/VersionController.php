<?php
/**
 * Created by PhpStorm.
 * User: dodo
 * Date: 2016/11/4
 * Time: 11:13
 */

namespace App\Background\Controllers;

use App\Models\Versions;
use App\Background\Forms\VersionForm;

class VersionController extends BaseController
{
    public function initialize()
    {
        parent::initialize();
        $menu = $this->menu;
        $menu['version']['active'] = true;
        $this->view->menu_root = $menu;
        $this->view->nav_menu = array_merge(array($this->view->nav_menu, array("name" => "版本管理列表")));
        $this->assets->addCss('public/static/background/css/common.css');
    }

    /**
     * 客户端历史版本
     */
    public function indexAction()
    {
        // 数据-页头
        $pageHeader["title"] = "客户端版本列表";
        $pageHeader["action"]["name"] = '<span class="fa fa-plus">&nbsp;&nbsp;新增版本</span>';
        $pageHeader["action"]["link"] = "admin/version/new";
        $this->view->page_header = $pageHeader;

        // 数据-搜索栏
        $page_search["link"] = "admin/version/search";
        $condition_select["type"] = 1;
        $condition_select["label"] = "选择型条件";
        $condition_select["key"] = "condition_select";
        $condition_select["options"] = [0 => "条件1", 1 => "条件2", 2 => "条件3"];
        $condition_select["value"] = 1;
        $page_search["conditions"][] = $condition_select;
        $this->view->page_search = $page_search;

        // 定义表格标题
        $tb_ths = [];
        $tb_ths[] = array("name" => "版本名称");
        $tb_ths[] = array("name" => "版本号");
        $tb_ths[] = array("name" => "版本描述");
        $tb_ths[] = array("name" => "发布日期");
        $tb_ths[] = array("name" => "操作员");
        $tb_ths[] = array("name" => "操作", "class" => "text-center", "width" => "150");

        // 查询所有版本
        $data = Versions::find(["order" => "id DESC"])->toArray();

        // 定义字段
        $using = [
            ["field" => "name"],
            ["field" => "number"],
            ["field" => "desc"],
            ["field" => "time"],
            ["field" => "operator"]
        ];

        // 定义操作
        $op = array(
            ["id_pre" => "look_", "name" => "查看", "link" => "admin/version/look"]
        );

        // 定义表格内容部分
        $tb_trs = array(
            "data" => $data,
            "using" => $using,
            "op" => $op
        );

        $page_table["ths"] = $tb_ths;
        $page_table["trs"] = $tb_trs;
        $this->view->page_table = $page_table;
        // 页码
        $pagination_cur = $this->request->getQuery("page", null, 1);
        $uriStr = $this->request->getURI();
        $uriArray = parse_url($uriStr);
        $queryStr = $uriArray['query'];
        parse_str($queryStr, $queryArray);
        $queryArray['page'] = "";
        $page_pagination = [
            "url" => $this->url->get("admin/version/search?" . http_build_query($queryArray)),
            "cur" => $pagination_cur,
            "total" => 10,
            "rows_value" => 10,
        ];
        $this->view->tb_page = $page_pagination;
        $this->view->pick("public/list_pagination_ex");
    }

    /**
     * 新增版本
     */
    public function newAction()
    {
        $this->assets->addJs(_LIBS_ . "libs/jsapi/qst_fileinput_init.js", false);
        // 数据-页头
        $pageHeader["title"] = "版本信息";
        $pageHeader["action"]["name"] = '返回版本列表';
        $pageHeader["action"]["link"] = "admin/version";
        $this->view->page_header = $pageHeader;

        $form = new VersionForm();
        try {
            if ($this->request->isPost()) {
                $version = new Versions();
                if ($form->isValid($this->request->getPost(), $version)) {
                    $version->operator = $this->login->getName();
                    $version->save();
                    return $this->response->redirect("admin/version");
                }
                // TODO 页面提示，不符合输入设计要求
            }
        } catch (\Exception $e) {
            $this->log($e->getMessage());
            // TODO 页面提示，不符合数据库设计要求
        }

        $form->setAction("admin/version/new");
        $this->view->form = $form;
        $this->view->pick("partials/tp_page_form");
    }

    /**
     * 检测版本升级
     */
    public function checkAction()
    {
        $reqData = $this->getJsonArrayBody();
        $versionString = $reqData["ver"];
        $versionInfo = Versions::checkVersion($versionString);
        if ($versionInfo) {
            $urlArray = json_decode($versionInfo->url);
            $data = [
                "ver" => $versionInfo->number,
                "state" => "1",
                "hint" => $versionInfo->desc,
                "url" => $urlArray[0]
            ];
        } else {
            $data = [
                "ver" => $versionString,
                "state" => "0"
            ];
        }

        return $this->responseJson(["data" => $data], 0);
    }

    public function lookAction()
    {
        $this->assets->addJs(_LIBS_ . "libs/jsapi/qst_fileinput_init.js", false);
        // 数据-页头
        $pageHeader["title"] = "历史版本信息";
        $pageHeader["action"]["name"] = '返回版本列表';
        $pageHeader["action"]["link"] = "admin/version";
        $this->view->page_header = $pageHeader;

        $id = $this->request->getQuery("id");
        $version = Versions::findFirstById($id);
        if ($version) {
            $form = new VersionForm($version, ["read_only" => true]);
            $this->view->form = $form;
            $this->view->pick("partials/tp_page_form");
        } else {
            $this->response->redirect("admin/version");
        }
    }
}
