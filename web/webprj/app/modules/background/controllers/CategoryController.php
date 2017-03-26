<?php
/**
 * Created by PhpStorm.
 * User: dodo
 * Date: 2016/8/1
 * Time: 21:18
 */

namespace App\Background\Controllers;

use App\Models\Goods;
use Plugin\Core\QSTBaseSearch;
use App\Background\Forms\CategoryForm;
use App\Models\Category;
use Phalcon\Mvc\View;

/**
 * Display the default index page.
 */
class CategoryController extends BaseController
{
    public function initialize()
    {
        parent::initialize(); // TODO: Change the autogenerated stub
        $menu = $this->menu;
        $menu['goods']['active'] = true;
        $menu['goods']['sub_menu']['category']['link_action'] = "admin/category";
        $menu['goods']['sub_menu']['category']['active'] = true;
        $this->view->menu_root = $menu;
        $this->view->nav_menu = array_merge(array($this->view->nav_menu, array("name" => "商品分类管理", "link" => "admin/category")));
        $this->assets->addCss('public/static/background/css/common.css');
    }

    public function indexAction()
    {
        $this->response->redirect("admin/category/search");
    }

    public function searchAction()
    {
        $where = ' 1 = 1';  //查询条件
        $rows_value = 20;   //一页显示多少条数据
        $id = $this->request->getQuery("id"); //获取当前栏目id，为判断是否是子栏目做准备
        $key = $this->request->getQuery("search_key");
        $svalue = $this->request->getQuery("search_value");
        $page = $this->request->getQuery("page");

        if($this->request->isPost()){
            $this->log($this->request->getPost());
        }

        // 加载CSS和JS资源
        $this->addLibJs("libs/jsapi/qst_linkpost.js");
        $this->addLibJs("libs/jsapi/table.js");
        $this->addLibJs("libs/js3party/bootstrap-terebentina-sco/1.0.2/js/sco.modal.js");
        $this->addLibJs("libs/js3party/bootstrap-terebentina-sco/1.0.2/js/sco.confirm.js");
        $this->assets->addCss(_LIBS_ . 'libs/js3party/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker3.min.css', false)
            ->addJs(_LIBS_ . 'libs/js3party/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js', false)
            ->addJs(_LIBS_ . 'libs/js3party/bootstrap-datepicker/1.6.4/locales/bootstrap-datepicker.zh-CN.min.js', false)
            ->addJs(_LIBS_ . 'libs/jsapi/qst_date_init.js', false);
//        $this->assets->addJs("public/static/background/js/thumbnail_init.js");
        // 数据-页头
        $pageHeader["title"] = "商品分类管理";
        $pageHeader["action"]["name"] = '<span class="fa fa-plus">&nbsp;&nbsp;添加分类</span>';;
        $pageHeader["action"]["link"] = "admin/category/new";
        $this->view->page_header = $pageHeader;

        if(!empty($id)){
            $pageHeader["action"]["names"] = '<span class="fa fa-align-justify">&nbsp;&nbsp;返回列表</span>';;
            $pageHeader["action"]["links"] = 'admin/category';
            $this->view->page_header = $pageHeader;
            $where = 'pid ='.$id;
        }

        // 数据-搜索栏
        $page_search = new QSTBaseSearch("admin/category/search");

        if(!empty($svalue)){
            if($key == 'cat_id1'){
                $where .= ' and name like "%'.$svalue.'%"';
            }elseif($key == 'cat_id2'){
                $where .= ' and pid != 0 and title like "%'.$svalue.'%"';
            }else{
                $where .= ' and name like "%'.$svalue.'%" or title like "%'.$svalue.'%"';
            }
        }
        $page_search->addCondition([
            'type' => 0,
            'key_default' => empty($key) ? 'name' : $key,
            'value_default' => $svalue,
            'keys' => ['cat_id1' => '一级分类','cat_id2'=>'二级分类']

        ]);
        $this->view->page_search = $page_search->toArray();

        // 定义表格字段
        $tb_ths = [];
        $tb_ths[] = array("name" => "一级分类", "width" => "150");
        $tb_ths[] = array("name" => "二级分类", "width" => "150");
        $tb_ths[] = array("name" => "分类排序", "width" => "150");
        $tb_ths[] = array("name" => "创建时间", "width" => "100");
        $tb_ths[] = array("name" => "操作", "class" => "text-center", "width" => "150");

        // 定义表格操作
        // $actions = ["0"=>["name"=>"启用"], "1"=>["name"=>"禁用"]];
        $op = array(
            //["id_pre" => "et_", "name" => "查看子栏目", "link" => "admin/Category/search"],
            ["id_pre" => "et_", "name" => "编辑", "link" => "admin/category/edit"],
            ["id_pre" => "del_", "name" => "删除", "link" => "admin/category/delete", "method" => "post"]
        );
        $starNum = empty($page) || $page == 1 ? 0 : ($page-1) * $rows_value;
        //echo $where;exit;
        //获取列表数据
        $ret = [];
        $number = Category::count($where);
        $data = Category::find(['conditions' =>$where,'order' => 'sort_order asc,addTime asc','limit' =>['number' => $rows_value,'offset' =>$starNum]]);

        //获取顶级分类
        $catDatas = Category::find('pid = 0');
        $cateData = [];
        foreach($catDatas as $value){
            if(empty($value->pid)){
                $cateData[$value->id] = $value->name;
            }
        }
        //根据顶级分类展示
        foreach($data as $value)
        {
            $row['id'] = $value->id;
            $row['name'] = $value->name;
            $row['title'] = !empty($value->pid)? $value->title : '';
            $row['sort_order'] = $value->sort_order;
            $row['activity'] = true;
            $row['pid'] = $value->pid;
            $row['addTime'] = date('Y-m-d H:i',$value->addTime);
            $ret[] = $row;
        }
        //当查询是一级分类时子分类也要显示
        $using = array(
            ["field" => 'name'],
            ["field" => 'title'],
            ["field" => 'sort_order'],
            ["field" => 'addTime']
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
        $data_count = $number;
        $total = ceil($data_count/$rows_value);
        $pagination_cur = $this->request->getQuery("page", null, 1);
        $uriStr = $this->request->getURI();
        $uriArray = parse_url($uriStr);
        $queryStr = $uriArray['query'];
        parse_str($queryStr, $queryArray);
        $queryArray['page'] = "";
        $page_pagination = [
            "url" => $this->url->get("admin/Category/search?" . http_build_query($queryArray)),
            "cur" => $pagination_cur,
            "total" => $total,
            "rows_value" => $rows_value,
            'data_total' => $data_count
        ];
        $this->view->tb_page = $page_pagination;

        // 使用普通模板
        $this->view->pick("public/list_pagination_ex");
    }

    public function newAction()
    {
        // 数据-页头
        $pageHeader["title"] = "添加分类";
        $pageHeader["action"]["name"] = "分类列表";
        $pageHeader["action"]["link"] = "admin/category";
        $this->view->page_header = $pageHeader;

        // 导航菜单
        $this->view->nav_menu = array_merge($this->view->nav_menu, array(array("name" => $pageHeader["title"])));

        $cateObj = Category::find('pid = 0');
        $cateData = [];
        foreach ($cateObj as $row) {
            $cateData[$row->id] = $row->name;

        }
        $cateData = [
            'pidData' => $cateData,
            'is_show' => true
        ];
        // 表单数据
        $form = new CategoryForm($cateData);
        //print_r($form);exit;
        $form->setAction("admin/category/create");
        $this->view->form = $form;
        $this->view->pick("partials/tp_page_form");
    }

    public function createAction()
    {
        if ($this->request->isPost()) {
            // 1. 创建一个新的数据
            $pid = $this->request->getPost("pid",'int',0);
            $category = new Category();
            if(!empty($pid)){
                $name = Category::findFirst('id = '.$pid)->name;
                $category->name = $name;
                $category->title = $this->request->getPost("name");
            }else{
                $category->name = $this->request->getPost("name");
                $category->title = '';
            }
            $category->keywords = $this->request->getPost("keywords");
            $category->desc = $this->request->getPost("desc");
            $category->pid = empty($pid) ? 0 : $pid;
            $category->sort_order = $this->request->getPost("sort_order");
            $category->is_show = $this->request->getPost("is_show");
            $category->addTime = time();
            if ($category->save()) {
                $this->response->redirect('admin/category/search');
                //return $this->ajax_return(0, "success");
            }
        }
        $this->response->redirect('admin/category/search');
    }

    public function saveAction()
    {
        if ($this->request->isPost()) {
            // 1. get the permission base the id
            $categoryData = $this->request->getPost();

            $categoryId = $categoryData['id'];
            $pid = $categoryData['pid'];
            $is_show = $categoryData['is_show'];
            $categoryData['is_show'] = empty($is_show) ? 0 : 1;

            if(!empty($pid)){
                $name = Category::findFirst('id = '.$pid)->name;
                $categoryData['title'] = $categoryData['name'];
                $categoryData['name'] = $name;
            }else{
                $categoryData['title'] = '';
            }

            $category = Category::findFirst('id = '.$categoryId);
            if (!$category) {
                $status = 1;
                $desc = "failed. the permission is not exist";
            } else {
                $category->save($categoryData);
                $this->response->redirect('admin/category/search');
            }
            $this->response->redirect('admin/category/search');
            //return $this->ajax_return(0, "success");
        }
        return $this->ajax_return(1, "error");
    }

    /**
     * 列表页面执行删除操作
     */
    public function deleteAction()
    {
        $cateId = $this->request->getQuery("id");
        $category = Category::find('id = '.$cateId);
        $goodCount = Goods::count('cat_id = '.$cateId.' and is_on_sale = 1');
        if($goodCount){
            return $this->ajax_return(1, "error");
        }
        if ($category && empty($goodCount)) {
            if($category->delete()== false){
                return $this->ajax_return(1, "error");
            }
            return $this->ajax_return(0, "success");
        }
        return $this->ajax_return(1, "error");
    }

    public function editAction()
    {
        $pageHeader["title"] = "编辑分类";
        $pageHeader["action"]["name"] = "分类列表";
        $pageHeader["action"]["link"] = "admin/Category";
        $this->view->page_header = $pageHeader;

        // 导航菜单
        $this->view->nav_menu = array_merge($this->view->nav_menu, array(array("name" => $pageHeader["title"])));

        // TODO 数据关联ID
        $id = $this->request->getQuery("id");

        // TODO 根据关联ID获取数据
        $cateOne = Category::findFirst('id = '.$id);

        $cateObj = Category::find('pid = 0');
        $cateData[] = '一级分类';
        foreach ($cateObj as $row) {
            $cateData[$row->id] = $row->name;

        }
        $name = $cateOne -> name;
        if(!empty($cateOne -> pid)){
            $name = $cateOne -> title;
        }
        $cateData = [
            'id' => $id,
            'pidData' => $cateData,
            'pid' => $cateOne -> pid,
            'name' => $name,
            'keywords' => $cateOne -> keywords,
            'desc' => $cateOne -> desc,
            'sort_order' => $cateOne -> sort_order,
            'is_show' => empty($cateOne -> is_show)? false :true,
        ];

        // 表单数据
        $form = new CategoryForm($cateData);
        $form->setAction("admin/Category/save");
        $this->view->form = $form;
        $this->view->pick("partials/tp_page_form");
    }

    //获取子分类
    private function getchildCate($id,$title){
        $catDatas = Category::find('pid = ').$id;
        $cateData = [];
        foreach($catDatas as $value){
            $row['id'] = $value->id;
            $row['name'] = $title;
            $row['ername'] = !empty($value->pid)? $value->name : '';
            $row['sort_order'] = $value->sort_order;
            $row['activity'] = true;
            $row['pid'] = $value->pid;
            $row['addTime'] = date('Y-m-d H:i',$value->addTime);
            $cateData[] = $row;
        }
        return $cateData;
    }
}
