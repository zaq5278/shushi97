<?php
/**
 * Created by PhpStorm.
 * User: dodo
 * Date: 2016/8/1
 * Time: 21:18
 */

namespace App\Background\Controllers;

use App\Models\Category;
use App\Models\Franchise;
use App\Models\Goods;
use App\Models\MemberInfo;
use App\Models\Order;
use App\Models\OrderGoods;
use App\Models\PayLog;
use Plugin\Core\QSTBaseSearch;
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Mvc\View;

/**
 * Display the default index page.
 */
class StatisticsController extends BaseController
{
    public function initialize()
    {
        parent::initialize();
        $this->assets->addCss('public/static/background/css/common.css');
    }

    public function indexAction()
    {
        $this->response->redirect("admin/statistics/sale");
    }

    public function saleAction()
    {
        $where = ' (vstate = 3 or vstate = 0) and ';
        $d_start = $this->request->getPost('d_start');
        $d_end = $this->request->getPost('d_end');
        $city = $this->request->getPost('city','string','');
        $province = $this->request->getPost('province','string','');
        $cateid = $this->request->getPost('category','int',0);
        $categoryc = $this->request->getPost('categoryc','int',0);

        // 加载CSS和JS资源
        $this->assets->addCss(_LIBS_ . 'libs/js3party/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker3.min.css', false)
            ->addJs(_LIBS_ . 'libs/js3party/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js', false)
            ->addJs(_LIBS_ . 'libs/js3party/bootstrap-datepicker/1.6.4/locales/bootstrap-datepicker.zh-CN.min.js', false)
            ->addJs(_LIBS_ . 'libs/highcharts/highcharts.js', false);
//        $this->assets->addJs("public/static/background/js/thumbnail_init.js");

        $menu = $this->menu;
        $menu['statistics']['active'] = true;
        $menu['statistics']['sub_menu']['sellSta']['link_action'] = "admin/statistics/sale";
        $menu['statistics']['sub_menu']['sellSta']['active'] = true;
        $this->view->menu_root = $menu;
        $this->view->nav_menu = array_merge(array($this->view->nav_menu, array("name" => "销售统计", "link" => "admin/statistics/sale")));

        // 数据-页头
        $pageHeader["title"] = "销售统计";
        //$d_start = str_replace('年','-',str_replace('月','-',str_replace('日','',$d_start)));
        //$d_end = str_replace('年','-',str_replace('月','-',str_replace('日','',$d_end)));

        $d_start = empty($d_start) ? date('Y-m-d',strtotime('-6 day')): $d_start;
        $d_end = empty($d_end) ? date('Y-m-d'): $d_end;

        //查询后向前台表单里传数据
        $searchData = ['d_start'=> empty($d_start) ? date('Y-m-d',strtotime('-7 day')) : $d_start,'d_end'=> empty($d_end) ? date('Y-m-d') : $d_end,'province'=>$province,'city'=>$city,'categoryc'=>$categoryc,'category'=>$cateid];
        $category = Category::find();
        $categoryData = [];
        $cateIds = $categoryc;
        if(empty($categoryc) && !empty($cateid)){
            $cateIds = $cateid;
        }
        //前台分类
        foreach($category as $value){
            $categoryData[]=['id' => $value->id,'name' => $value->name ,'title' => $value->title, 'pid' => $value->pid];
        }
        if(!empty($cateid)){
            foreach($category as $value){
                $categoryData[]=['id' => $value->id,'name' => $value->name ,'title' => $value->title, 'pid' => $value->pid];
                if(empty($categoryc) && $cateid == $value->pid){
                    $cateIds .= ','.$value->id;
                }
            }
        }
        $oids = '';
        if(!empty($cateIds)){
            //根据栏目查找出来商品id
            $goods = Goods::find(' cat_id in ('. ltrim($cateIds,',') .')');
            $goodsIds = '';
            foreach($goods as $value){
                $goodsIds .= ','.$value->goods_id;
            }
            if(!empty($goodsIds)){
                //根据商品id查找订单号
                $orderGoods = OrderGoods::find('goods_id in ('.ltrim($goodsIds,',').')');
                $orderIds = [];
                foreach($orderGoods as $value){
                    $orderIds[] =$value->ordercode;
                }
                $orderIds = array_unique($orderIds);
                $oids = empty($orderIds)? '':implode('","',$orderIds);
            }else{
                $oids = ' ';
            }
        }
        if(!empty($province)){
            $where .= ' province = "'.$province.'" and ';
        }
        if(!empty($city)){
            $where .= ' city = "'.$city.'" and ';
        }
        if(!empty($oids)) {
            $where .= ' ordercode in("' . $oids . '") and ';
        }
        $stime = strtotime($d_end) - strtotime($d_start);
        $day = ($stime/(24*60*60));
        //根据结束时间来算七天，开始时间暂时没用
        $endDate = strtotime($d_start . ' 23:59:59');
        $stateDate = strtotime($d_start);

        $orderDatas = [];

        for($i = 0 ; $i <= $day ; $i++){
            $wherestr = $where . ' btime > '. $stateDate . ' and btime < ' . $endDate ;
            //echo $wherestr;
            $num = $this->getOrderNum($wherestr);
            $orderDatas[$i]=[
                'date' =>  date('Y-m-d',$stateDate),
                'count' => $num
            ];
            if($stateDate == strtotime($d_end)){
                continue;
            }
            $stateDate = strtotime('+ 1 day',$stateDate);
            $endDate = strtotime('+ 1 day',$endDate);
        }
        //exit;
        ksort($orderDatas);
        $this->view->orderDatas = json_encode($orderDatas);//图标数据
        $categoryData = json_encode($categoryData);
        $this->view->categoryDatas = $categoryData;//栏目
        $this->view->searchData = $searchData;
        // 使用普通模板
        $this->view->pick("statistics/index");
    }
    public function franchiseAction(){
        $rows_value = 10;   //一页显示多少条数据
        $page = $this->request->getQuery("page");
        $franchise_id = $this->request->getQuery("franchise_id");
        $type = $this->request->getQuery("type");
        $d_start = $this->request->getQuery('d_start');
        $d_end = $this->request->getQuery('d_end');

        // 加载CSS和JS资源
        $this->assets->addCss(_LIBS_ . 'libs/js3party/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker3.min.css', false)
            ->addJs(_LIBS_ . 'libs/js3party/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js', false)
            ->addJs(_LIBS_ . 'libs/js3party/bootstrap-datepicker/1.6.4/locales/bootstrap-datepicker.zh-CN.min.js', false)
            ->addJs(_LIBS_ . 'libs/jsapi/qst_date_init.js', false);

        $menu = $this->menu;
        $menu['statistics']['active'] = true;
        $menu['statistics']['sub_menu']['franchSta']['link_action'] = "admin/statistics/franchise";
        $menu['statistics']['sub_menu']['franchSta']['active'] = true;
        $this->view->menu_root = $menu;
        $this->view->nav_menu = array_merge(array($this->view->nav_menu, array("name" => "加盟店收入统计", "link" => "admin/statistics/franchise")));

        // 数据-页头
        $pageHeader["title"] = "加盟店收入统计";
        $this->view->page_header = $pageHeader;

        // 数据-搜索栏
        $page_search = new QSTBaseSearch("admin/statistics/franchise");
        $franchise = Franchise::find();
        $franchiseDatas = [];
        foreach($franchise as $value){
            $franchiseDatas[$value->id] = $value->title;
        }
        $page_search->addCondition([
            'type' => 1,
            'label' => '加盟店名称',
            'key' => 'franchise_id',
            'value' => $franchise_id,
            'options' => $franchiseDatas

        ]);

        $page_search->addCondition([
            "type" => 2,
            "date_type" => 1,
            "label" => "交易时间",
            "date_start" => [
                "key" => "d_start",
                "value" => empty($d_start)? '' : $d_start
            ],
            "date_end" => [
                "key" => "d_end",
                "value" => empty($d_end)? '': $d_end
            ],
        ]);

        $page_search->addCondition([
            'type' => 1,
            'label' => '交易类型',
            'key' => 'type',
            'value' => $type ,
            'options' => [1=>'扫码支付',2=>'同城购买']

        ]);
        $this->view->page_search = $page_search->toArray();

        // 定义表格字段
        $tb_ths = [];
        $tb_ths[] = array("name" => "交易类型", "width" => "100");
        $tb_ths[] = array("name" => "收款加盟店", "width" => "100");
        $tb_ths[] = array("name" => "支付人昵称", "width" => "160");
        $tb_ths[] = array("name" => "用户支付金额", "width" => "100");
        $tb_ths[] = array("name" => "返现金额", "width" => "100");
        $tb_ths[] = array("name" => "交易日期", "width" => "120");

        // 定义表格操作
        // $actions = ["0"=>["name"=>"启用"], "1"=>["name"=>"禁用"]];
        $op = array(
        );
        $stateDate = strtotime($d_start . ' 00:00:00');
        $endDate = strtotime($d_end . ' 23:59:59');

        $starNum = empty($page) || $page == 1 ? 0 : ($page-1) * $rows_value;
        $where = empty($type) ? ' (type =2 or type = 1)' : 'type = '.$type ;
        $where .= empty($franchise_id) ? '' : ' and fancheise_id = '. $franchise_id ;
        $where .= empty($d_start) ? '' : ' and addTime  >'. $stateDate ;
        $where .= empty($d_end) ? '' : ' and addTime <'. $endDate ;

        //print_r(['conditions' =>$where,'order' => 'addTime desc','limit' =>['number' => 10,'offset' =>$starNum],'group'=>'ordercode']);exit;
        $paySql = 'select id as num from  App\Models\PayLog where '.$where. ' group by ordercode ';
        $payData = $this->modelsManager->executeQuery($paySql);
        $number = 0;
        foreach($payData as $value){
            $number += 1;
        }

        $Orders = PayLog::find(['columns'=>'ordercode,type,fancheise_id,userid,totalprice,addTime,sum(totalprice) as totalprice','conditions' =>$where,'order' => 'addTime desc','limit' =>['number' => 10,'offset' =>$starNum],'group'=>'ordercode']);
        //获取列表数据
        $ret = [];
        foreach($Orders as $value)
        {
            $row['id'] = $value->ordercode;
            $row['type'] = $value->type == 1 ? '扫码支付' : '同城购买';
            $row['title'] = $this->getFranInfo($value->fancheise_id);
            $row['nick'] = $this->getUserInfo($value->userid,2);
            $row['totalprice'] = number_format($value->totalprice,2);
            $row['retprice'] = number_format($value->totalprice * 0.08,2);
            $row['addTime'] = date('Y-m-d H:i',$value->addTime);
            $ret[] = $row;
        }
        $using = array(
            ["field" => 'type'],
            ["field" => 'title'],
            ["field" => 'nick'],
            ["field" => 'totalprice'],
            ["field" => 'retprice'],
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
        // $params = $this->request->getQuery();
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
            "url" => $this->url->get("admin/statistics/franchise?" . http_build_query($queryArray)),
            "cur" => $pagination_cur,
            "total" => $total,
            "rows_value" => $rows_value,
            'data_total' => $data_count
        ];

        //计算扫码支付和同城支付金额
        $payData = PayLog::find([
            'conditions' => $where,
            'columns' => 'sum(totalprice) as totalprice,type',
            'group' => 'type'
        ]);
        $payDatas = [];
        foreach($payData as $value){
            $payDatas[$value->type] = ['totalprice'=>round($value->totalprice,2),'retprice'=>round($value->totalprice*0.08,2)];
        }
        $this->view->payDatas = $payDatas;
        $this->view->tb_page = $page_pagination;

        // 使用普通模板
        $this->view->pick("statistics/franchise");

    }
    private function getOrderNum($where){
        $num = 0;
        if(!empty($where)){
            $num = Order::count($where);
        }
        return empty($num)?0:$num;
    }
    private function getFranInfo($id){
        $str ='';
        if(!empty($id)){
            $str = Franchise::findFirst('id = ' . $id)->title;
        }
        return $str;
    }

    private function getUserInfo($userid,$type){
        $str ='';
        if(empty($userid)){
            return;
        }
        if($type == 3){
            $datas = Users::findFirst('id = ' . $userid);
            $str = $datas->account;
        }else{
            $userData = MemberInfo::findFirst('userid = ' . $userid);
            $str = $userData->nick;
        }
        return $str;
    }
}
