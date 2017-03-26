<?php
/**
 * Created by PhpStorm.
 * User: dodo
 * Date: 2016/8/1
 * Time: 21:18
 */

namespace App\Background\Controllers;

use App\Models\Goods;
use App\Models\Order;
use Plugin\Core\QSTBaseSearch;
use Phalcon\Mvc\View;

/**
 * Display the default index page.
 */
class FranchiseOrderController extends BaseController
{
    public function initialize()
    {
        parent::initialize(); // TODO: Change the autogenerated stub
        $menu = $this->menu;
        $menu['depotorder']['link_action'] = "admin/FranchiseOrder";
        $menu['depotorder']['active'] = true;
        $this->view->menu_root = $menu;
        $this->view->nav_menu = array_merge(array($this->view->nav_menu, array("name" => "订单管理", "link" => "admin/FranchiseOrder")));
        $this->assets->addCss('public/static/background/css/common.css');
    }

    public function indexAction()
    {
        $this->response->redirect("admin/franchiseorder/search");
    }

    public function searchAction()
    {
        $where = ' 1';  //查询条件
        $rows_value = 10;   //一页显示多少条数据
        $key = $this->request->getQuery("search_key");
        $value = $this->request->getQuery("search_value");
        $page = $this->request->getQuery("page");

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
        $page_search = new QSTBaseSearch("admin/order/search");

        $page_search->addCondition([
            'type' => 0,
            'key_default' => empty($key) ? 'ordercode' : $key,
            'value_default' => $value,
            'keys' => ['goods_name' => '订单号']

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
                "key" => "d_start",
                "value" => "1982-07-16"
            ],
            "data_end" => [
                "key" => "d_end",
                "value" => "1982-07-17"
            ],
        ]);

        $this->view->page_search = $page_search->toArray();

        $where = $page_search->toWhereEx(function($key) {
            if ($key == "condition_select") {

            }
            // 默认名称等于字段名称
            return $key;
        }, null, function($key, $value) {
            $ret = null;
            if ($key == "ordercode") {
                $ret = $key.' like "%'.$value .'%"';
            }
            return $ret;
        });

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
        $tb_ths[] = array("name" => "所属仓库", "width" => "135");
        $tb_ths[] = array("name" => "购买时间", "width" => "200");
        $tb_ths[] = array("name" => "操作", "class" => "text-center", "width" => "175");

        // 定义表格操作
        // $actions = ["0"=>["name"=>"启用"], "1"=>["name"=>"禁用"]];
        $op = array(
            ["id_pre" => "et_", "name" => "退款审核", "link" => "admin/order/edit"]
        );
        $starNum = empty($page) || $page == 1 ? 0 : ($page-1) * $rows_value;
        //获取列表数据
        $ret = [];

        $number = Order::count($where);
        $data = Order::find(['conditions' =>$where,'order' => 'btime desc','limit' =>['number' => 10,'offset' =>$starNum]]);

        foreach($data as $value)
        {
            $row['id'] = $value->goods_id;
            $row['ordercode'] = $value->goods_name;
            $row['ordercode'] = $value->goods_name;
            $row['ordercode'] = $value->goods_name;
            $row['ordercode'] = $value->goods_name;
            $row['ordercode'] = $value->goods_name;
            $row['ordercode'] = $value->goods_name;
            $row['ordercode'] = $value->goods_name;
            $row['ordercode'] = $value->goods_name;
            $row['ordercode'] = $value->goods_name;
            $row['ordercode'] = $value->goods_name;
            $row['addTime'] = date('Y-m-d H:i',$value->btime);
            $ret[] = $row;
        }
        $using = array(
            ["field" => 'ordercode'],
            ["field" => 'ordercode'],
            ["field" => 'ordercode'],
            ["field" => 'ordercode'],
            ["field" => 'ordercode'],
            ["field" => 'ordercode'],
            ["field" => 'ordercode'],
            ["field" => 'ordercode'],
            ["field" => 'ordercode'],
            ["field" => 'ordercode'],
            ["field" => 'btime']
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
            "url" => $this->url->get("admin/order/search?" . http_build_query($queryArray)),
            "cur" => $pagination_cur,
            "total" => $total,
            "rows_value" => $rows_value,
            'data_total' => $data_count
        ];
        $this->view->tb_page = $page_pagination;

        // 使用普通模板
        $this->view->pick("public/list_pagination_ex");
    }
}
