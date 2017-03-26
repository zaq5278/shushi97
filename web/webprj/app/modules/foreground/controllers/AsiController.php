<?php

namespace App\Foreground\Controllers;
use Plugin\Core\QSTBaseController;
use Plugin\Core\QSTBaseModel;

class AsiController extends QSTBaseController{
    public function indexAction(){
    }
    /*
  功能描述: 首页数据接口查询、
    URL: http://【123.57.223.193/tuzhuan】/Asi/home
  入参: 
      vtype	　	   广告类型：0.首页轮播,1.本地商家轮播
      longitude 用户的经度
      latitude  用户的纬度
   出参: 
      advert[
            uuid        广告记录id
            picurl      广告位展示的图片
            paramtype   参数类型  1.名片设计，2.传单印刷,3商家详情参数
            param       查看详情的参数如: uuid
      ]
      business[
        userid      商家用户id
        vname       商家名称
        mainsale   主营
        thumbnail  缩略图
        iscashback 商家是否返现: 0.非返现商家,1返现商家
        distanc    用户里商家额距离
 
      ]
      status   0.成功, 1002.没有数据 9999.数据异常
     */
    public function homeAction(){
        $body = $this->request->getRawBody();
        $body = json_decode($body,true);
        $advert=array();
        $business=array();
        $resp = $this->execsql($body, "p_advert_query");
        if($resp['status']==0){
            $advert['advert'] =$resp['data'];
        }
        $resp = $this->execsql($body, "p_business_cover_query");
        if($resp['status']==0){
            $business['business'] =$resp['data'];
        }
        $ret['business']= $business['business'];
        $ret['advert']=  $advert['advert'];
        $ret['status']=0;
        return $this->responseJson($ret,$ret['status']);
    }
}
