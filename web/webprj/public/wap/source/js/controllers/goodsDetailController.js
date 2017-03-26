/**
 * Created by chaoshen on 2016/12/26.
 */

angular.module('cftApp.goodsDetail',[]).config(['$stateProvider',function ($stateProvider) {
    $stateProvider
        .state('tabs.goodsDetail',{
            url:'/goodsDetail/:is_integral/:goods_id/:goods_icon',
            cache:false,
            views: {
                'tabs-homePage':{
                    templateUrl:'goodsDetail.html',
                    controller:'goodsDetailController'
                }
    
            }
        })
        .state('tabs.goodsDetail_collection',{
            url:'/goodsDetail_collection/:is_integral/:goods_id/:goods_icon',
            cahce:false,
            views: {
                'tabs-personal':{
                    templateUrl:'goodsDetail.html',
                    controller:'goodsDetailController'
                }
            }
        })
        .state('tabs.goodsDetail_orderDetail',{
            url:'/goodsDetail_orderDetail/:is_integral/:goods_id/:goods_icon',
            cahce:false,
            views: {
                'tabs-personal':{
                    templateUrl:'goodsDetail.html',
                    controller:'goodsDetailController'
                }
            }
        })
}]).controller('goodsDetailController',['$scope','$ionicScrollDelegate','$location','$stateParams','$state','$ionicViewSwitcher','$ionicModal','HttpFactory','$rootScope','$timeout','CftStore',function ($scope,$ionicScrollDelegate,$location,$stateParams,$state,$ionicViewSwitcher,$ionicModal,HttpFactory,$rootScope,$timeout,CftStore) {
    
    $scope.goodsObj = {
        //是否是积分商品 0 普通商品，1 积分商品
        is_integral: 0,
        //商品id
        goods_id: $stateParams.goods_id,
        //是否售罄
        isSellOut:false,
        collectName: "收藏",
        //是否收藏
        isCollect: false,
        //详情是否激活
        isInfoActive: true,
        //参数是否激活
        isParamActive: false,
        //评论是否激活
        isAssessActive: false,
        //图片根地址
        IconRootURL: '',
        //视图切换
        selection: 'goodsInfo',
        //商品数据
        goodsData: {},
        //轮播视图数据
        slideData: {
            bannerData: [],
            ishome: 2 //这里用于区分首页和积分首页的0 和 1，用于标示不能被点击
        },
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
        // 选中 购物车
        goShoppingCar: goShoppingCar,
        // 选中 加入购物车
        putinShoppingCar: putinShoppingCar,
        // 选中 立即购买
        buyNow: buyNowOption,
        //返回 首页
        backHome: backHome,
        freight:0,
        //加载更多
        loadMore: loadMore,
        noneOfMoreData: false,
        noneMsg: '',
        isShow: 0,
        dragup: dragup,
        dataIsNull: 0
    };
    $scope.$on('$ionicView.beforeEnter', function () {
        $rootScope.hideTabs = true;
    });
    function dragup() {
        if ($scope.goodsObj.moredata && $scope.goodsObj.assessData.length > 0){
            // $scope.homeObj.noneOfMoreData = true;
            $scope.goodsObj.noneMsg = "没有更多评论...";
        }
    }
    var currentPage = 1;
    var assessParams = {
        goods_id:$stateParams.goods_id,
        page: currentPage,
        total: perPageCount
    };
    function loadMore() {
        assessParams.page = currentPage;
        // console.log(currentPage);
        HttpFactory.getData("/api/assess",assessParams).then(function (result) {
            console.log(result);
            if (result.status == 0) {
                
                if (result["assessData"].length < perPageCount){
                    if (result["assessData"].length == 0 && $scope.goodsObj.assessData.length == 0){
                        $scope.goodsObj.noneOfMoreData = false;
                        $scope.goodsObj.noneMsg = "";
                        $scope.goodsObj.dataIsNull = 1;
                    }else {
                        $scope.goodsObj.dataIsNull = 0;
                        $scope.goodsObj.noneOfMoreData = true;
                        // $scope.goodsObj.noneMsg = "没有更多评论...";
                    }
                }else {
                    $scope.goodsObj.dataIsNull = 0;
                }
                
                $scope.goodsObj.moredata = result["assessData"].length < perPageCount;
                
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
                $scope.$broadcast('scroll.infiniteScrollComplete');
            }
            // if (result.status === 10001){
            //     $timeout(function () {
            //         $scope.popTipsShow("抱歉,该商品不存在!");
            //         $timeout(function () {
            //             window.history.back();
            //         },1000);
            //     },500);
            //
            // }
            
        },function (error) {
            console.log(error);
        });
    }
    //设置购物车徽章
    requestCartNums();
    function requestCartNums() {
        HttpFactory.getData("/api/ushoppingCart", {sessid:SESSID}).then(function (result) {
            if (result.status == 0){
                user_car_num = result.shoppingCart.length;
                $scope.user_Car_Num = user_car_num;
            }
        },function (err) {
        
        });
    }
    
    //获取商品运费
    function getGoodsFreight(goodsId) {
        goodsId = "[" + goodsId + "]";
        //获取运费
        HttpFactory.getData('/api/getAvgPrice',{sessid:SESSID,goods_id:JSON.stringify(goodsId)}).then(function (result) {
            if (result.status == 0){
                $scope.goodsObj.freight = parseFloat(result.freight);
            }
        });
    }
    //拉取商品详情信息的方法
    setTimeout(function () {
        var params = {
            integral: '0',
            id: $scope.goodsObj.goods_id
        };
        //显示加载动画
        $scope.loadingShow();
        HttpFactory.getData("/api/getGoods",params).then(function (result) {
            currentPage = 2;
            console.log(result);
            $scope.loadingOrPopTipsHide();
            if (result.status === 10001){
                $scope.popTipsShow("抱歉,该商品未上架!");
                $timeout(function () {
                    window.history.back();
                },1000)
            }
            if (result.goods_number == 0){
                $scope.goodsObj.isSellOut = true;
            }
            $scope.goodsObj.slideData.bannerData = result["goods_introduction"];
            $scope.goodsObj.goodsData = result;
            $scope.goodsObj.isShow = 1;
            //获取商品运费
            getGoodsFreight($scope.goodsObj.goods_id);
            if ($scope.goodsObj.goodsData.is_coll == 1){
                $scope.goodsObj.isCollect = true;
                $scope.goodsObj.collectName = "已收藏";
            }
        },function (err) {
            throw new Error("enter goods detail error: "+err);
        });
    },500);

    //点击收藏按钮执行的方法
    function collectionOption(goods_id) {
        $scope.goodsObj.isCollect = !$scope.goodsObj.isCollect;
        if ($scope.goodsObj.isCollect){
            
            $scope.goodsObj.collectName = "已收藏";
            var collectParams = {
                goods_id: goods_id,
                is_integral: "0",
                sessid:SESSID
            };
            HttpFactory.getData("/api/ucollection",collectParams,"POST").then(function (result) {
            
                if (result.status == 0) {
                    $scope.popTipsShow("收藏成功");
                }
                if (result.status !== 0) {
                    $scope.popTipsShow(result.desc);
                }
            },function (err) {
                throw new Error("enter goods detail error: "+err);
            });
        }else {
            
            $scope.goodsObj.collectName = "收藏";
            var noCollectParams = {
                goods_id: goods_id,
                sessid:SESSID
            };
            HttpFactory.getData("/api/ucollection",noCollectParams,"DELETE").then(function (result) {
            
                if (result.status == 0) {
                    $scope.popTipsShow("已取消收藏");
                }
                if (result.status !== 0) {
                    $scope.popTipsShow(result.desc);
                }
            },function (err) {
                throw new Error("enter goods detail error: "+err);
            });
        }
    }
    function backHome() {
        
        $state.go('tabs.homePage');
        
    }
    function changeGoodsNums() {
        $scope.openModal();
    }
    
    var slideLine = document.getElementById('slideLine');
    
    function selectInfo() {
        
        $scope.goodsObj.isInfoActive = true;
        $scope.goodsObj.isParamActive = false;
        $scope.goodsObj.isAssessActive = false;
        slideLine.style.left = "4.7%";
        $scope.goodsObj.selection='goodsInfo';
        $ionicScrollDelegate.resize();
        $scope.goodsObj.moredata = true;
    }
    function selectParam() {
        
        $scope.goodsObj.isInfoActive = false;
        $scope.goodsObj.isAssessActive = false;
        $scope.goodsObj.isParamActive = true;
        slideLine.style.left = "38%";
        $scope.goodsObj.selection='goodsParam';
        $ionicScrollDelegate.resize();
        $scope.goodsObj.moredata = true;
        setTimeout(function () {
        
            $ionicScrollDelegate.resize();
        },200);

    }
    function selectAssess() {
        
        $scope.goodsObj.isInfoActive = false;
        $scope.goodsObj.isParamActive = false;
        $scope.goodsObj.isAssessActive = true;
        slideLine.style.left = "71.4%";
        $scope.goodsObj.selection='goodsAssess';
        $ionicScrollDelegate.resize();
        $scope.goodsObj.moredata = false;
        // $scope.goodsObj.assessData = [];
        currentPage = 1;
        
    }

    //点击底部的购物车按钮
    function goShoppingCar() {
        
        if ($state.current.name == 'tabs.goodsDetail'){
            
            $state.go('tabs.shoppingCart_fromDetail');
            $ionicViewSwitcher.nextDirection('forward');
        }else {
            $state.go('tabs.shoppingCart');
            $ionicViewSwitcher.nextDirection('forward');
        }
    }
    $ionicModal.fromTemplateUrl('shopCarModal.html', {
        scope: $scope,
        animation: 'slide-in-up'
    }).then(function(modal) {
        $scope.modal = modal;
    });
    $scope.openModal = function() {
        $scope.modal.show();
    };
    
    //当我们用到模型时，清除它！
    $scope.$on('$destroy', function() {
        $scope.loadingOrPopTipsHide();
        $scope.modal.remove();
    });
    function putinShoppingCar() {
        $scope.collect.val = 1;
        $scope.openModal();
    }
    //底部的立即购买
    function buyNowOption() {
        $scope.goodsObj.goodsData.goodsNum = $scope.collect.val;
        if ($location.path().indexOf("goodsDetail_collection") > -1){
            $state.go("tabs.confirmOrder_personal",{goodsArray:JSON.stringify([$scope.goodsObj.goodsData])});
        }else {
            $state.go("tabs.confirmOrder",{goodsArray:JSON.stringify([$scope.goodsObj.goodsData])});
        }
    }
    //购物车模态窗口相关操作
    $scope.collect = {
        val : 1,
        reduce:function () {
            if($scope.collect.val > 1){
                $scope.collect.val--;
            }
        },
        add:function () {
            
            if($scope.collect.val < parseInt($scope.goodsObj.goodsData.goods_number)){
                $scope.collect.val ++;
            }else {
                $scope.popTipsShow("抱歉,您添加的商品数量大于库存量");
            }
        }
    };
    //点击数量打开加入购物车和立即购买的modal
    $ionicModal.fromTemplateUrl('shopCarModal.html', {
        scope: $scope,
        animation: 'slide-in-up'
    }).then(function(modal) {
        $scope.modal = modal;
        
    });
    $scope.openModal = function() {
        $scope.modal.show();
        $scope.modal.goodsData = $scope.goodsObj.goodsData;
        $scope.modal.IconRootURL = IconROOT_URL;
    };
    //点击模态窗口的加入购物车触发的方法
    $scope.addToShoppingCar = function () {
        
        if($scope.goodsObj.isSellOut){
            $scope.popTipsShow("抱歉,该商品没有库存了,加入购物车失败!");
            $scope.modal.hide();
            return;
        }
        var params = {
            goods_id: $scope.modal.goodsData.goods_id,
            num:$scope.collect.val,
            sessid:SESSID
        };
        HttpFactory.getData("/api/ushoppingCart",params,"POST").then(function (result) {
            
            if (result.status == 0) {
                requestCartNums();
                // user_car_num += 1;
                $scope.user_Car_Num = user_car_num;
                $scope.modal.hide();
                $scope.popTipsShow("加入购物车成功");
            }else {
                
                $scope.popTipsShow("加入购物车失败");
            }
        },function (err) {
            
        });
    };
    //模态窗口的立即购买
    $scope.goToConfirmOrder = function () {
        $scope.modal.hide();
        if($scope.goodsObj.isSellOut){
            $scope.popTipsShow("抱歉,该商品没有库存了!");
            return;
        }
        $scope.goodsObj.goodsData.goodsNum = $scope.collect.val;
        if ($location.path().indexOf('goodsDetail_collection') > -1){
            $state.go("tabs.confirmOrder_personal",{goodsArray:JSON.stringify([$scope.goodsObj.goodsData])});
        }else {
            $state.go("tabs.confirmOrder",{goodsArray:JSON.stringify([$scope.goodsObj.goodsData])});
        }

    };


    $scope.rednums = [];
    $scope.graynums = [];
    (function showStarNums(nums) {
        $scope.rednums.length = nums;
        $scope.graynums.length = 5-nums;
    }(5));
    $scope.goodsIcons = ['img1'];
}]);