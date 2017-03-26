<?php
/**
 * Created by PhpStorm.
 * User: dodo
 * Date: 2016/8/1
 * Time: 21:18
 */

namespace App\Background\Controllers;

use App\Models\Depot;
use App\Models\Franchise;
use App\Models\Goods;
use App\Models\Order;
use App\Models\OrderGoods;
use Plugin\Core\QSTBaseSearch;
use Plugin\Upload\Upload;
use App\Background\Forms\DepotForm;
use App\Background\Forms\SendGoodsForm;
use Phalcon\Mvc\View;

/**
 * Display the default index page.
 */
class DepotController extends BaseController
{
    public function initialize()
    {
        parent::initialize(); // TODO: Change the autogenerated stub
        $menu = $this->menu;
        $menu['depot']['active'] = true;
        $menu['depot']['link_action'] = "admin/depot";
        $this->view->menu_root = $menu;
        $this->view->nav_menu = array_merge(array($this->view->nav_menu, array("name" => "仓库管理", "link" => "admin/depot")));
        $this->assets->addCss('public/static/background/css/common.css');
    }

    public function indexAction()
    {
        $this->response->redirect("admin/depot/search");
    }

    public function searchAction()
    {
        $where = ' is_show = 1';
        $rows_value = 10;   //一页显示多少条数据
        $key = $this->request->getQuery("search_key");
        $value = $this->request->getQuery("search_value");
        $page = $this->request->getQuery("page");

        // 数据-页头
        $pageHeader["title"] = "仓库管理";
        $pageHeader["action"]["name"] = '<span class="fa fa-plus">&nbsp;&nbsp;仓库</span>';;
        $pageHeader["action"]["link"] = "admin/depot/new";
        $this->view->page_header = $pageHeader;

        $this->addLibJs("libs/jsapi/qst_linkpost.js");
        $this->addLibJs("libs/jsapi/table.js");
        $this->addLibJs("libs/js3party/bootstrap-terebentina-sco/1.0.2/js/sco.modal.js");
        $this->addLibJs("libs/js3party/bootstrap-terebentina-sco/1.0.2/js/sco.confirm.js");

        // 数据-搜索栏
        $page_search = new QSTBaseSearch("admin/depot/search");

        if($key == 'id'){
            $where .= empty($value) ? '' : ' and id = '.($value - 10000);
        }elseif($key == 'title'){
            $where .= empty($value) ? '' : ' and title like "%'.$value.'%"';
        }else{
            $value = '';
        }

        $page_search->addCondition([
            'type' => 0,
            'key_default' => $key,
            'value_default' => $value,
            'keys' => ['id'=>'仓库编号','title' => '仓库名称']

        ]);

        $this->view->page_search = $page_search->toArray();
        // 定义表格字段
        $tb_ths = [];
        $tb_ths[] = array("name" => "仓库名称", "width" => "100");
        $tb_ths[] = array("name" => "仓库地址", "width" => "100");
        $tb_ths[] = array("name" => "所在城市", "width" => "100");
        $tb_ths[] = array("name" => "仓库负责人", "width" => "70");
        $tb_ths[] = array("name" => "联系方式", "width" => "70");
        $tb_ths[] = array("name" => "操作", "class" => "text-center", "width" => "150");

        // 定义表格操作
        // $actions = ["0"=>["name"=>"启用"], "1"=>["name"=>"禁用"]];
        $op = array(
            ["id_pre" => "et_", "name" => "编辑", "link" => "admin/depot/edit"],
            ["id_pre" => "del_", "name" => "删除", "link" => "admin/depot/delete", "method" => "post"]
        );
        $starNum = empty($page) || $page == 1 ? 0 : ($page-1) * $rows_value;
        //获取列表数据
        $ret = [];
        $data = Depot::find(['conditions' =>$where,'order' => 'id desc','limit' =>['number' => $rows_value,'offset' =>$starNum]]);


        foreach($data as $value)
        {
            $row['id'] = $value->id;
            $row['ids'] = 10000 + $value->id;
            $row['title'] = mb_substr($value->title,0,11,'utf-8');
            $row['address'] = mb_substr($value->address,0,11,'utf-8');
            $row['city'] = $value->province.'--'.$value->city;
            $row['name'] = $value->name;
            $row['mobile'] = $value->mobile;
            $ret[] = $row;
        }
        $using = array(
            ["field" => 'title'],
            ["field" => 'address'],
            ["field" => 'city'],
            ["field" => 'name'],
            ["field" => 'mobile']
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
        $data_count = Depot::count($where);
        $total = ceil($data_count/$rows_value);
        $pagination_cur = $this->request->getQuery("page", null, 1);
        $uriStr = $this->request->getURI();
        $uriArray = parse_url($uriStr);
        $queryStr = $uriArray['query'];
        parse_str($queryStr, $queryArray);
        $queryArray['page'] = "";
        $page_pagination = [
            "url" => $this->url->get("admin/depot/search?" . http_build_query($queryArray)),
            "cur" => $pagination_cur,
            "total" => $total,
            "rows_value" => $rows_value,
            'data_total' => $data_count
        ];
        $this->view->tb_page = $page_pagination;

        // 使用普通模板
        $this->view->pick("depot/list_pagination_depot");
    }

    public function newAction()
    {
        $this->assets->addJs(_LIBS_ . 'libs/jsapi/base.js', false);
        $this->assets->addJs(_LIBS_ . 'libs/jsapi/distpicker.data.js', false);
        $this->assets->addJs(_LIBS_ . 'libs/jsapi/distpicker.js', false);
        // 数据-页头
        $pageHeader["title"] = "添加仓库";
        $pageHeader["action"]["name"] = "仓库列表";
        $pageHeader["action"]["link"] = "admin/depot";
        $this->view->page_header = $pageHeader;

        // 导航菜单
        $this->view->nav_menu = array_merge($this->view->nav_menu, array(array("name" => $pageHeader["title"])));

        // 表单数据
        $form = new depotForm();
        $form->setAction("admin/depot/create");
        $this->view->form = $form;
        $this->view->pick("partials/tp_page_form");
    }

    public function createAction()
    {
        if ($this->request->isPost()) {
            // 1. 创建一个新的数据
            $depots = new Depot();
            $depots->title = $this->request->getPost("title");
            $depots->province = $this->request->getPost('province');
            $depots->city = $this->request->getPost('city');
            $depots->address = $this->request->getPost("address");
            $depots->name = $this->request->getPost('name');
            $depots->mobile = $this->request->getPost("mobile");
            $depots->freight = $this->request->getPost("freight");
            $depots->brand = $this->request->getPost("brand");
            $depots->sort_order = $this->request->getPost("sort_order",'int',0);
            $depots->is_show = 1;
            $depots->addTime = time();
            if ($depots->save()) {
                $this->response->redirect('admin/depot/search');
                //return $this->ajax_return(0, "success");
            }
        }
        $this->response->redirect('admin/depot/search');
        //return $this->ajax_return(1, "error");
    }

    public function saveAction()
    {
        if ($this->request->isPost()) {
            // 1. get the permission base the id
            $depotData = $this->request->getPost();

            $depotId = $depotData['id'];
            $depot = Depot::findFirst('id = '.$depotId);
            if (!$depot) {
                $status = 1;
                $desc = "failed. the permission is not exist";
            } else {
                $depot->save($depotData);
                $this->response->redirect('admin/depot/search');
            }
            $this->response->redirect('admin/depot/search');
            //return $this->ajax_return(0, "success");
        }
        return $this->ajax_return(1, "error");
    }

    /**
     * 列表页面执行删除操作
     */
    public function deleteAction()
    {
        if ($this->request->isPost()) {
            $depotId = $this->request->getQuery("id");
            //检查仓库是否存在商品
            $goodsNum = Goods::count(' is_delete = 0 and depot_id = ' . $depotId);
            if($goodsNum){
                return $this->ajax_return(1, "仓库中存在商品！");
            }
            $depot = Depot::findFirstById($depotId);
            if ($depot) {
                if($depot->delete() == false){
                    return $this->ajax_return(1, "删除失败！");
                }
             }
        }
        return $this->ajax_return(0, "success");
    }

    public function editAction()
    {
        $this->assets->addJs(_LIBS_ . 'libs/jsapi/base.js', false);
        $this->assets->addJs(_LIBS_ . 'libs/jsapi/distpicker.data.js', false);
        $this->assets->addJs(_LIBS_ . 'libs/jsapi/distpicker.js', false);

        $pageHeader["title"] = "编辑仓库";
        $pageHeader["action"]["name"] = "仓库列表";
        $pageHeader["action"]["link"] = "admin/depot";
        $this->view->page_header = $pageHeader;

        // 导航菜单
        $this->view->nav_menu = array_merge($this->view->nav_menu, array(array("name" => $pageHeader["title"])));

        // TODO 数据关联ID
        $id = $this->request->getQuery("id");

        // TODO 根据关联ID获取数据
        $frandOne = Depot::findFirst('id = '.$id);

        $frandData = [
            'id' => $id,
            'title' => $frandOne->title,
            'province' => $frandOne->province,
            'city' => $frandOne->city,
            'address' => $frandOne->address,
            'name' => $frandOne->name,
            'mobile' => $frandOne->mobile,
            'freight' => $frandOne->freight,
            'brand' => $frandOne->brand,
            'static' => '<div id="distpicker5"><select name="province" class="form-control err" data-province="'.$frandOne->province.'"></select>&nbsp;&nbsp;<select name="city"  class="form-control err" data-city="'.$frandOne->city.'"></select></div>',
            'sort_order' => $frandOne -> sort_order,
            'is_show' => empty( $frandOne -> is_show )? true : false
        ];
        // 表单数据
        $form = new DepotForm($frandData);
        $form->setAction("admin/depot/save");
        $this->view->form = $form;
        $this->view->pick("partials/tp_page_form");
    }

    public function depotmAction()
    {
        $depotid = $this->session->get('auth-identity')['ispd'];
        if(empty($depotid)){
            echo '仓库id异常！';exit;
        }
        $menu = $this->menu;
        $menu['depot']['active'] = true;
        $menu['depot']['link_action'] = "admin/depot/depotm";
        $this->view->menu_root = $menu;

        $depotData = Depot::findFirst('id = '.$depotid);
        $depotrow['id'] = $depotData->id;
        $depotrow['title'] = $depotData->title;
        $depotrow['province'] = $depotData->province;
        $depotrow['city'] = $depotData->city;
        $depotrow['address'] = $depotData->address;

        $phql = "SELECT count(id) as num FROM App\Models\Order  where vstate = 1 and depotid = ".$depotid;
        $depotOrder = $this->modelsManager->executeQuery($phql);
        $num = 0;
        foreach($depotOrder as $value) {
            $num = $value->num;
        }
        $this->view->depotrow = $depotrow;
        $this->view->num = empty($num)? 0 : $num;
        $this->view->pick("depot/index");
    }
    public function depotorderAction(){
        $depotid = $this->session->get('auth-identity')['ispd'];
        if(empty($depotid)){
            echo '仓库id异常！';exit;
        }
        $menu = $this->menu;
        $menu['depotorder']['active'] = true;
        $menu['depotorder']['link_action'] = "admin/depot/depotorder";
        $this->view->menu_root = $menu;
        $where = ' depotid = '.$depotid;  //查询条件
        $rows_value = 10;   //一页显示多少条数据

        $page = $this->request->getQuery("page");
        $oid = $this->request->getQuery('oid');
        $moblie = $this->request->getQuery('moblie');
        $state = $this->request->getQuery('state');
        $d_start = $this->request->getQuery('mg_date_start');
        $d_end = $this->request->getQuery('mg_date_end');

        $stateDate = strtotime($d_start . ' 00:00:00');
        $endDate = strtotime($d_end . ' 23:59:59');

        if($state == -1){
            $where .= ' and vstate = 0' ;
        }else{
            $where .= empty($state)? '' : ' and vstate = '.$state ;
        }
        $where .= empty($oid) ? '' : ' and ordercode ="'.$oid.'" or masterorder = "'.$oid.'"' ;
        $where .= empty($d_start) ? '' : ' and btime  >'. $stateDate ;
        $where .= empty($d_end) ? '' : ' and btime <'. $endDate ;
        $where .= empty($tel) ? '' : ' and tel ="'. $tel .'"' ;

        if($this->request->isPost()){
            $this->log($this->request->getPost());
        }
        // 加载CSS和JS资源
        $this->assets->addCss(_LIBS_ . 'libs/js3party/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker3.min.css', false)
            ->addJs(_LIBS_ . 'libs/js3party/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js', false)
            ->addJs(_LIBS_ . 'libs/js3party/bootstrap-datepicker/1.6.4/locales/bootstrap-datepicker.zh-CN.min.js', false)
            ->addJs(_LIBS_ . 'libs/jsapi/qst_date_init.js', false);

        // 数据-页头
        $pageHeader["title"] = "订单管理";
        $this->view->page_header = $pageHeader;

        // 数据-搜索栏
        $page_search = new QSTBaseSearch("admin/depot/depotorder");

        $page_search->addCondition([
            'type' => 4,
            'label' => '订单号',
            'value' => $oid,
            'key' => 'oid'
        ]);

        $page_search->addCondition([
            'type' => 4,
            'label' => '收货人联系方式',
            'value' => $moblie,
            'key' => 'moblie'
        ]);

        $page_search->addCondition([
            'type' => 1,
            'label' => '订单状态',
            'key' => 'state',
            'value' => $state,
            'options' => [-1 => '待支付',1=>'待发货','待收货','交易成功','退款中','已退款','交易关闭']

        ]);
//        $page_search->addCondition([
//            'type' => 1,
//            'key_default' => empty($key) ? 'ordercode' : $key,
//            'value_default' => $value,
//            'keys' => ['goods_name' => '订单号']
//
//        ]);
        $page_search->addCondition([
            "type" => 2,
            "date_type" => 1,
            "label" => "订单提交时间",
            "data_start" => [
                "key" => "mg_date_start",
                "value" => $d_start
            ],
            "data_end" => [
                "key" => "mg_date_end",
                "value" => $d_end
            ],
        ]);

        $this->view->page_search = $page_search->toArray();

        // 定义表格字段
        $tb_ths = [];
        $tb_ths[] = array("name" => "订单号", "width" => "150");
        $tb_ths[] = array("name" => "提交人", "width" => "100");
        $tb_ths[] = array("name" => "收货人", "width" => "100");
        $tb_ths[] = array("name" => "收货人电话", "width" => "160");
        $tb_ths[] = array("name" => "销售量", "width" => "100");
        $tb_ths[] = array("name" => "订单总金额", "width" => "100");
        $tb_ths[] = array("name" => "订单积分", "width" => "70");
        $tb_ths[] = array("name" => "订单状态", "width" => "120");
        $tb_ths[] = array("name" => "购买时间", "width" => "200");
        $tb_ths[] = array("name" => "操作", "class" => "text-center", "width" => "175");

        // 定义表格操作
        // $actions = ["0"=>["name"=>"启用"], "1"=>["name"=>"禁用"]];
        $op = array(
            ["id_pre" => "fh_",'name'=>'发货','key'=>'ordercode',"link" => "admin/depot/sendGoods"],
            ["id_pre" => "wl_", "name" => "修改物流", "link" => "admin/depot/logistics"]
        );
        $starNum = empty($page) || $page == 1 ? 0 : ($page-1) * $rows_value;
        //获取列表数据
        $ret = [];
        $number = Order::count($where);
        $data = Order::find(['conditions' =>$where,'order' => 'btime desc','limit' =>['number' => 10,'offset' =>$starNum]]);

        foreach($data as $value)
        {
            $row['id'] = $value->id;
            $row['username'] = $value->memberinfo->nick;
            $row['ordercode'] = $value->ordercode;
            $row['vname'] = $value->vname;
            $row['tel'] = $value->tel;
            $row['num'] = $this->getNum('"'.$value->masterorder . '","' . $value->ordercode.'"');
            $row['total'] = $value->totalPrice + $value->disPrice;
            $row['integral'] = $value->integral;
            $row['state'] = $this->getState($value->vstate);
            $row['vstate'] = $value->vstate;
            $row['addTime'] = date('Y-m-d H:i',$value->btime);
            $ret[] = $row;
        }
        $using = array(
            ["field" => 'ordercode'],
            ['field' => 'username'],
            ["field" => 'vname'],
            ["field" => 'tel'],
            ["field" => 'num'],
            ["field" => 'total'],
            ["field" => 'integral'],
            ["field" => 'state'],
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
            "url" => $this->url->get("admin/depot/depotorder?" . http_build_query($queryArray)),
            "cur" => $pagination_cur,
            "total" => $total,
            "rows_value" => $rows_value,
            'data_total' => $data_count
        ];
        $this->view->tb_page = $page_pagination;

        // 使用普通模板
        $this->view->pick("order/list_pagination_depot");
    }
    public function logisticsAction(){
        $menu = $this->menu;
        $menu['depotorder']['active'] = true;
        $menu['depotorder']['link_action'] = "admin/depot/depotorder";
        $this->view->menu_root = $menu;

        $id = $this->request->getQuery('id');
        $sedData = [
            'id' => $id
        ];
        $orderData = Order::findFirstById($id);
        $sedData = [
            'id' => $id,
            'wl_name' => $orderData->distribution,
            'wl_code' => $orderData->logisticsnum
        ];
        // 表单数据
        $form = new SendGoodsForm($sedData);
        $form->setAction("admin/depot/sendsave");
        $this->view->form = $form;
        $this->view->pick("partials/tp_page_form");
    }

    public function sendGoodsAction(){
        $menu = $this->menu;
        $menu['depotorder']['active'] = true;
        $menu['depotorder']['link_action'] = "admin/depot/depotorder";
        $this->view->menu_root = $menu;
        $id = $this->request->getQuery('id');
        $sedData = [
            'id' => $id
        ];
        // 表单数据
        $form = new SendGoodsForm($sedData);
        $form->setAction("admin/depot/sendsave");
        $this->view->form = $form;
        $this->view->pick("partials/tp_page_form");
    }
    public function sendsaveAction(){
        $id = $this->request->getPost('id');
        $wl_name = $this->request->getPost('wl_name');
        $wl_code = $this->request->getPost('wl_code');
        $orderData = Order::findFirst($id);
        if(!empty($orderData->id) && ($orderData->vstate == 1)){
            $orderData->distribution = $wl_name;
            $orderData->logisticsnum = $wl_code;
            $orderData->vstate = 2;
            $orderData->update();

            $intOrderLogSql = 'insert into App\Models\OrderLog (ordercode,state,btime,mess) VALUES ("'.$orderData->ordercode.'",2,'.time().',"")';
            $this->modelsManager->executeQuery($intOrderLogSql);
        }else{
            $orderData->distribution = $wl_name;
            $orderData->logisticsnum = $wl_code;
            $orderData->update();
        }
        $this->response->redirect("admin/depot/depotorder");
    }
    private function getState($sate){
        $strState = '待付款';
        switch($sate){
            case 1:
                $strState = '待发货';
                break;
            case 2:
                $strState = '待收货';
                break;
            case 3:
                $strState = '交易完成';
                break;
            case 4:
                $strState = '退款中';
                break;
            case 5:
                $strState = '已退款';
                break;
            case 6:
                $strState = '交易关闭';
                break;
        }
        return $strState;
    }

    private function getNum($oid){
        $num = 0;
        $dataNum = OrderGoods::sum([
            "column"     => "num",
            "conditions" => 'ordercode in ('.$oid.')'
        ]);
        return $dataNum;
    }
}