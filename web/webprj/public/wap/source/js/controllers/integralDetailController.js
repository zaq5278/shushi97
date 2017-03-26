/**
 * Created by chaoshen on 2016/12/16.
 */
angular.module('cftApp.integralGoodsDetail',[]).config(['$stateProvider',function ($stateProvider) {
    $stateProvider.state('tabs.igDetail',{
        url:'/igDetail/:is_integral/:goods_id',
        views: {
            'tabs-integralStore':{
                templateUrl:'goodsDetail.html',
                controller:'integralDetailController'
            }
        }
    }).state('tabs.igDetail_personal',{
        url:'/igDetail_personal/:is_integral/:goods_id',
        views: {
            'tabs-personal':{
                templateUrl:'goodsDetail.html',
                controller:'integralDetailController'
            }
        }
    });
}]).controller('integralDetailController',['$scope','$location','$ionicScrollDelegate','$state','$stateParams','$ionicModal','HttpFactory','$rootScope','$ionicLoading',function ($scope,$location,$ionicScrollDelegate,$state,$stateParams,$ionicModal,HttpFactory,$rootScope,$ionicLoading) {
    $scope.goodsObj = {
        //是否是积分商品 0 普通商品 1 积分商品
        is_integral: "1",
        //是否已售罄
        isSellOut_ig:false,
        //收藏标签名
        collectName: "收藏",
        //是否收藏
        isCollect: false,
        //是否选中商品信息
        isInfoActive: true,
        //是否选中商品参数
        isParamActive: false,
        //是否选中商品评价
        isAssessActive: false,
        //视图切换
        selection: 'goodsInfo',
        //商品详情数据
        goodsData: {},
        //轮播图数据
        slideData: {
            bannerData: [],
            ishome: 2 //这里用于区分首页和积分首页的0 和 1，用于标示不能被点击
        },
        //评论数据
        assessData: [],
        //是否加载更多评论，false 能加载更多，true 不能加载更多
        moredata: false,
        //收藏
        collectOption: collectionOption,
        //商品数量
        changeGoodsNums: changeGoodsNums,
        //选中 商品详情
        selectInfo: selectInfo,
        //选中 商品参数
        selectParam: selectParam,
        // 选中 商品评价
        selectAssess: selectAssess,
        //返回 首页
        backHome: backHome,
        //立即兑换
        convertOption: convertOption,
        freight:0,
        //加载更多
        loadMore: loadMore,
        isShow: 0,
        dragup: dragup,
        dataIsNull: 0
        
    };
    
    function dragup() {
        if ($scope.goodsObj.moredata && $scope.goodsObj.assessData.length > 0){
            // $scope.homeObj.noneOfMoreData = true;
            $scope.goodsObj.noneMsg = "没有更多评论...";
        }
    }
    var params = {
        integral: $scope.goodsObj.is_integral,
        id: $stateParams.goods_id
    };
    var currentPage = 1;
    
    $scope.$on('$ionicView.beforeEnter', function () {
        $rootScope.hideTabs = true;
    });
    //当我们销毁controller时，清除它！
    $scope.$on('$destroy', function() {
        $scope.loadingOrPopTipsHide();
    });
    var assessParams = {
        goods_id:$stateParams.goods_id,
        page: 1,
        total: perPageCount
    };
    
    function loadMore() {
        // loadMore();
        console.log('loadMore');
        console.log(currentPage);
        assessParams.page = currentPage;
        
        HttpFactory.getData("/api/assess",assessParams).then(function (result) {
            
            if (result.status == 0) {
                
                if (result["assessData"].length < perPageCount){
                    if (result["assessData"].length == 0 && $scope.goodsObj.assessData.length <= 0){
                        $scope.goodsObj.noneOfMoreData = false;
                        $scope.goodsObj.dataIsNull = 1;
                        $scope.goodsObj.noneMsg = "";
                    }else {
                        $scope.goodsObj.dataIsNull = 0;
                        $scope.goodsObj.noneOfMoreData = true;
                        
                    }
                }else {
                    $scope.goodsObj.dataIsNull = 0;
                }
                $scope.goodsObj.moredata = result["assessData"].length < perPageCount
                
                for(var i = 0; i < result.assessData.length; i++){
                    result.assessData[i].rednums = [];
                    result.assessData[i].graynums = [];
                    result.assessData[i].rednums.length = result.assessData[i].num;
                    result.assessData[i].graynums.length = 5-result.assessData[i].num;

                    result.assessData[i].addTime = new Date(parseInt(result.assessData[i].addTime) * 1000).toLocaleString();
                }
                if (currentPage == 1 && $scope.goodsObj.assessData.length > 0){
                    $scope.goodsObj.assessData = [];
                    $scope.goodsObj.assessData = result.assessData;
                }else {
                    $scope.goodsObj.assessData = $scope.goodsObj.assessData.concat(result.assessData);
                }
                
                currentPage ++;
                console.log(currentPage);
                $scope.$broadcast('scroll.infiniteScrollComplete');
            }

        },function (error) {
            console.log(error);
        });
    }
    $scope.loadingShow();
    setTimeout(function () {
        
        HttpFactory.getData("/api/getGoods",params).then(function (result) {
            $scope.loadingOrPopTipsHide();
            if (result.status == 0){
                //获取评论数据
               // currentPage = 1;
            }
            if (result.status === 10001){
                $scope.popTipsShow("抱歉,该商品未上架!");
                setTimeout(function () {
                    window.history.back();
                },1000)
            }
            if (result.goods_number == 0){
                $scope.goodsObj.isSellOut_ig = true;
            }
            $scope.goodsObj.slideData.bannerData = result["goods_introduction"];
            $scope.goodsObj.goodsData = result;
            $scope.goodsObj.isShow = 1;
            //获取商品运费
            if ($scope.goodsObj.goodsData.is_coll == 1){
                $scope.goodsObj.isCollect = true;
                $scope.goodsObj.collectName = "已收藏";
            }
        },function (err) {
            throw new Error("enter goods detail error: "+err);
        });
    },500);
    
    function collectionOption(goods_id) {
        $scope.goodsObj.isCollect = !$scope.goodsObj.isCollect;
        if ($scope.goodsObj.isCollect){
            
            $scope.goodsObj.collectName = "已收藏";
            var collectParams = {
                goods_id: goods_id,
                is_integral: "1",
                sessid:SESSID
            };
            HttpFactory.getData("/api/ucollection",collectParams,"POST")
                .then(function (result) {
                    if (result.status == 0) {
                        $scope.popTipsShow("收藏成功");
                    }
                },function (err) {
                    throw new Error("enter goods detail error: "+err);
                });
        }else {
            
            $scope.goodsObj.collectName = "收藏";
            var noCollectParams = {
                goods_id: goods_id,
                is_integral: "1",
                sessid:SESSID
            };
            HttpFactory.getData("/api/ucollection",noCollectParams,"DELETE")
                .then(function (result) {

                    if (result.status == 0) {
                        $scope.popTipsShow("已取消收藏");
                    }else {
                        $scope.popTipsShow(result.desc)
                    }
                },function (err) {
                    throw new Error("enter goods detail error: "+err);
                });
        }
    }

    function backHome() {
        
        localStorage.isFromIntegralToHomePage = true;
        $state.go('tabs.homePage');
    }
    function changeGoodsNums() {
        
    }
    
    $scope.isInteg = $stateParams.isInteg;
    var slideLine = document.getElementById('slideLine');
    
    function selectInfo() {
        $ionicScrollDelegate.resize();
        $scope.goodsObj.isInfoActive = true;
        $scope.goodsObj.isParamActive = false;
        $scope.goodsObj.isAssessActive = false;
        slideLine.style.left = "4.7%";
        $scope.goodsObj.selection='goodsInfo';
        $scope.goodsObj.moredata = true;
    }
    function selectParam() {
        $ionicScrollDelegate.resize();
        $scope.goodsObj.isInfoActive = false;
        $scope.goodsObj.isAssessActive = false;
        $scope.goodsObj.isParamActive = true;
        $scope.goodsObj.moredata = true;
        slideLine.style.left = "38%";
        $scope.goodsObj.selection='goodsParam';
        setTimeout(function () {
            $ionicScrollDelegate.resize();
        },200);
    }
    function selectAssess() {
        $ionicScrollDelegate.resize();
        $scope.goodsObj.isInfoActive = false;
        $scope.goodsObj.isParamActive = false;
        $scope.goodsObj.isAssessActive = true;
        $scope.goodsObj.moredata = false;
        slideLine.style.left = "71.4%";
        $scope.goodsObj.selection='goodsAssess';
        // doRefresh();
        currentPage = 1;
    }
    
    function putinShoppingCar() {
        $scope.openModal();
    }
    
    
    
    $scope.goodsIcons = ['img1'];

    //立即兑换
    function convertOption() {
        if ($location.path().indexOf("igDetail_personal") > -1){
            $state.go('tabs.confirmOrder_personal',{goodsArray: JSON.stringify([$scope.goodsObj.goodsData])});
        }else {
            $state.go('tabs.confirmOrder_IG',{goodsArray: JSON.stringify([$scope.goodsObj.goodsData])});
        }
    }
    
}]);