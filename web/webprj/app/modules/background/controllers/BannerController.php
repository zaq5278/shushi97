<?php
/**
 * Created by PhpStorm.
 * User: dodo
 * Date: 2016/9/2
 * Time: 16:23
 */

namespace App\Background\Controllers;

use App\Models\Banners;
use App\Background\Forms\BannerForm;
use App\Background\Forms\BannerAddForm;
use App\Models\Goods;
use App\Models\Integral;

class BannerController extends BaseController
{
    public function initialize()
    {
        parent::initialize();
        $menu = $this->menu;
        $menu['indeSet']['active'] = true;
        $menu['indeSet']['sub_menu']['banner']['link_action'] = "admin/banner";
        $menu['indeSet']['sub_menu']['banner']['active'] = true;
        $this->view->menu_root = $menu;
        $this->view->nav_menu = array_merge(array($this->view->nav_menu, array("name" => "轮播图管理", "link" => "admin/banner")));

        $pageHeader["title"] = "轮播图管理";
        $this->view->page_header = $pageHeader;

        $this->assets->addCss('public/static/background/css/common.css');
        $this->assets->addCss('public/static/background/css/banner/index.css');
        $this->assets->addCss(_LIBS_ . "libs/js3party/cropper/cropper.min.css", false)
            ->addJs(_LIBS_ . "libs/js3party/AdminEx/js/modernizr.min.js", false)
            ->addJs(_LIBS_ . "libs/js3party/jquery-form/3.46.0/jquery.form.min.js", false)
            ->addJs(_LIBS_ . "libs/js3party/cropper/cropper.min.js", false)
            ->addJs(_LIBS_ . "libs/jsapi/base.js", false)
            ->addJs(_LIBS_ . "libs/jsapi/image_cropper.js", false)
            ->addJs(_LIBS_ . "libs/jsapi/banner.js", false);
    }
    //轮播图首页
    public function indexAction()
    {
        $rows_value = 10;   //一页显示多少条数据
        // 数据-页头
        $pageHeader["title"] = "轮播图管理";
        $pageHeader["action"]["name"] = '<span class="fa fa-plus">&nbsp;&nbsp;新增轮播图</span>';;
        $pageHeader["action"]["link"] = "admin/banner/new";
        $this->view->page_header = $pageHeader;
        $page = $this->request->getQuery("page");

        $this->addLibJs("libs/jsapi/qst_linkpost.js");
        $this->addLibJs("libs/jsapi/table.js");
        $this->addLibJs("libs/js3party/bootstrap-terebentina-sco/1.0.2/js/sco.modal.js");
        $this->addLibJs("libs/js3party/bootstrap-terebentina-sco/1.0.2/js/sco.confirm.js");
        // 定义表格字段
        $tb_ths = [];
        $tb_ths[] = array("name" => "轮播图位置", "width" => "100");
        $tb_ths[] = array("name" => "轮播图", "width" => "100");
        $tb_ths[] = array("name" => "商品编号", "width" => "100");
        $tb_ths[] = array("name" => "排序", "width" => "70");
        $tb_ths[] = array("name" => "新增日期", "width" => "70");
        $tb_ths[] = array("name" => "操作", "class" => "text-center", "width" => "150");

        // 定义表格操作
        // $actions = ["0"=>["name"=>"启用"], "1"=>["name"=>"禁用"]];
        $op = array(
            ["id_pre" => "et_", "name" => "编辑", "link" => "admin/banner/edit"],
            ["id_pre" => "del_", "name" => "删除", "link" => "admin/banner/delete","method" => "post"]
        );
        $starNum = empty($page) || $page == 1 ? 0 : ($page-1) * $rows_value;
        //获取列表数据
        $ret = [];
        $number = Banners::count();
        $data = Banners::find(['order' => 'sort asc','limit' =>['number' => $rows_value,'offset' =>$starNum]]);

        foreach($data as $value)
        {
            $row['id'] = $value->id;
            $row['name'] = empty($value->is_integral)?'普通商品':'积分商品';
            $row['image_url'] = $value->image_url;
            $row['sort'] = $value->sort;
            $row['param'] = $value->param;
            $row['num'] = '<a href="/sunny/public/wap/#?/tabs/goodsDetail/0/'.$value->param.'/"'>$value->id.'</a>';
            $row['addTime'] = date('Y-m-d H:i',$value->addTime);
            $ret[] = $row;
        }

        $using = array(
            ["field" => 'name'],
            ["field" => 'image_url','type' => 'image','width'=>100,'height'=>100],
            ["field" => 'param'],
            ["field" => 'sort'],
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
        $pagination_cur = $page;
        $uriStr = $this->request->getURI();
        $uriArray = parse_url($uriStr);
        $queryStr = $uriArray['query'];
        parse_str($queryStr, $queryArray);
        $queryArray['page'] = "";
        $page_pagination = [
            "url" => $this->url->get("admin/banner?" . http_build_query($queryArray)),
            "cur" => $pagination_cur,
            "total" => $total,
            "rows_value" => $rows_value,
            'data_total' => $data_count
        ];
        $this->view->tb_page = $page_pagination;

        // 使用普通模板
        $this->view->pick("banner/index");
    }
    //添加轮播图
    public function newAction()
    {
        if($this->request->isGet()){
            // 数据-页头
            $pageHeader["title"] = "添加轮播图";
            $pageHeader["action"]["name"] = "轮播图列表";
            $pageHeader["action"]["link"] = "admin/banner";
            $this->view->page_header = $pageHeader;

            $goodsData = (object)[];
            //添加普通商品id
            $intrData = Goods::find('is_integral = 0  and is_delete =0 and is_on_sale = 1');
            $intrDatas = [];
            foreach($intrData as $value){
                $intrDatas[$value->goods_id]=$value->goods_id;
            }
            $goodsData->intrDatas = [$intrDatas];
            //添加积分商品id
            $goods_Data = Goods::find('is_integral = 1 and is_delete =0 and is_on_sale = 1');
            $goods_Datas = [];
            foreach($goods_Data as $value){
                $goods_Datas[$value->goods_id]=$value->goods_id;
            }
            $goodsData->goods_Datas = [$goods_Datas];
            // 导航菜单
            $this->view->nav_menu = array_merge($this->view->nav_menu, array(array("name" => $pageHeader["title"])));

            // 表单数据
            //print_r($goodsData);exit;
            $form = new BannerAddForm($goodsData);
            $form->setAction("admin/banner/create");
            $this->view->action_url = 'admin/banner/create';
            $this->view->paramStr = 'param1';
            $this->view->form = $form;
            $this->view->pick("banner/add");
        }
    }
    public function createAction(){
        if ($this->request->isPost()) {
            $formBanner = new BannerForm(null, []);
            $data = null;
            $status = 0;
            try {
                $dataBanner = $this->request->getPost();
                $banner = new Banners();

                if (!$formBanner->isValid($dataBanner, $banner)) {
                    $status = -1;
                    foreach ($formBanner->getMessages() as $message) {
                        $this->flash->error($message);
                        break;
                    }
                } else {
                    $fileUrl = "";
                    if ($this->request->hasFiles()) {
                        // Print the real file names and sizes
                        foreach ($this->request->getUploadedFiles() as $file) {
                            $fileUrl = $this->upload->upload($file, false);
                            break;
                        }
                    }
                    $banner->image_url = $fileUrl;
                    $banner->type = 2;
                    $whereStr = '';
                    if(empty($dataBanner['pid'])){
                        $banner->param = $dataBanner['param2'];
                        $banner->is_integral = 1;
                        $whereStr = ' is_integral = 1';
                    }else{
                        $banner->param = $dataBanner['param1'];
                        $banner->is_integral = 0;
                        $whereStr = ' is_integral = 0';
                    }
                    $num = Banners::count($whereStr);
                    if($num >= 5){
                        echo json_encode(['state'=>$num]);
                        exit;
                    }

                    $banner->addTime = time();
                    $banner->save();
                    echo json_encode(['state'=>0]);
                    exit;
                    //$this->response->redirect("admin/banner");
                }
            } catch (\Exception $e) {
                //$this->flash->error($e->getMessage());
                $status = -1;
                echo json_encode(['state'=>$status]);
                exit;
            }
        }
        //return $this->response->redirect("admin/banner");
    }

    /**
     * 删除新的广告
     */
    public function deleteAction()
    {
        $bannerId = $this->request->getQuery("id");
        $banner = Banners::findFirstById($bannerId);
        if ($banner) {
            if($banner->delete() == false){
                return $this->ajax_return(1, "error");
            }
        }
        return $this->ajax_return(0, "success");
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
//        $paramtype['ptvalue']['1']='活动详情';
//        $paramtype['ptvalue']['2']='商家详情';
        $paramtype['ptvalue']['1']='商品id';
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
                if(!empty($fileUrl)){
                    $banner->image_url = $fileUrl;
                }
                $banner->type = 2;
                if(empty($dataBanner['pid'])){
                    $banner->param = $dataBanner['param2'];
                    $banner->is_integral = 1;
                }else{
                    $banner->param = $dataBanner['param1'];
                    $banner->is_integral = 0;
                }
                $banner->addTime = time();
                $banner->save();
                $formBanner->clear();
                // success redirect(admin/banner/index)
                echo json_encode(['state'=>0]);
                exit;
            }
            echo json_encode(['state'=>0]);
            exit;
        } else {
            $this->view->edit_id = $this->request->getQuery("id");
            $banner = Banners::findFirstById($this->view->edit_id);
            if ($banner) {
                $this->view->form_edit = true;
                $formBanner->setEntity($banner);
                
                $paramtype['edit']='true';
                //判断位置
                $pid = 1;
                $paramStr = 'param2';//前端注释积分
                $banner->pid = 1;
                if($banner->is_integral == 1){
                    $pid = 0;
                    $paramStr = 'param1';//前端注释有机
                    $banner->pid = 0;
                }

                //添加普通商品id
                $intrData = Goods::find('is_integral = 0  and is_delete =0 and is_on_sale = 1');
                $intrDatas = [];
                foreach($intrData as $value){
                    $intrDatas[$value->goods_id]=$value->goods_id;
                }
                $banner->intrDatas = $intrDatas;

                //添加积分商品id
                $goods_Data = Goods::find('is_integral = 1 and is_delete =0 and is_on_sale = 1');
                $goods_Datas = [];
                foreach($goods_Data as $value){
                    $goods_Datas[$value->goods_id]=$value->goods_id;
                }
                $banner->goods_Datas = $goods_Datas;
                $datass = [
                    'intrDatas' =>[$intrDatas,array('useEmpty' => true, 'emptyText' => '商品编号', "required"=>"required"),$banner->param],
                    'goods_Datas' =>[$goods_Datas,array('useEmpty' => true, 'emptyText' => '商品编号', "required"=>"required"),$banner->param]
                ];
                $banner->name = $datass;
                $formBanner = new BannerForm($banner,$paramtype);
                $type = $banner->type;
            }
        }

        $this->view->cancel_url = "admin/banner/index" . "?type=" . $type;
        // 初始化轮播图数据
        $banners = Banners::findByType($type);
        $this->view->banners = $banners;
        $this->view->form = $formBanner;
        $this->view->paramStr = $paramStr;
        $this->view->pick('banner/add');
    }
}