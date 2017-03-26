/**
 * Created by qingyun on 16/11/30.
 */
angular.module('cftApp.integralStore',['ctfApp.searchBar','cftApp.goodsDetail']).config(['$stateProvider',function ($stateProvider) {
    $stateProvider.state('tabs.integralStore',{
        
        url:'/integralStore',
        views:{
            'tabs-integralStore':{
                
                templateUrl:'integralStore.html',
                controller:'integralStoreController'
            }
        }
    });
}]).controller('integralStoreController',['$scope','$rootScope','$ionicPopup','HttpFactory','$ionicSideMenuDelegate','$state','$ionicNavBarDelegate','$ionicViewSwitcher','$timeout','CftStore','$window',function ($scope,$rootScope,$ionicPopup,HttpFactory,$ionicSideMenuDelegate,$state,$ionicNavBarDelegate,$ionicViewSwitcher,$timeout,CftStore,$window) {
    
    $scope.integralObj = {
        //积分商品列表数据
        goodsData:[],
        //轮播图数据
        slideData: {
            bannerData: [],
            ishome: 1
        },
        //判断是否还有更多数据
        noneOfMoreData: false,
        //通过tab 获得侧边栏数据
        cateData: {},
        //是否加载更多
        moredata: false,
        //当前页数
        currentpage: 1,
        //切换侧边栏
        toggleRight: toggleRight,
        //进入商品详情页
        goDetail: goDetail,
        //下拉刷新
        doRefresh: doRefresh,
        //加载更多
        loadMore: loadMore,
        //进行搜索
        goSearch: goSearch,
        //兑换
        convertOption: convertOption,
        onScroll: onScroll,
        dragup: dragup,
        msgsIsNull: 0,
        emptyLogMsg: ''
        
    };
    $scope.integralObj.totoalGoodsData = [];
    var isExit= false;
    var currentPage = 1;
    function dragup() {
        
        if ($scope.integralObj.moredata && $scope.integralObj.totoalGoodsData.length > 0){
            $scope.integralObj.noneOfMoreData = true;
        }
    }
    
    // 跳转 积分分类
    $scope.$on('integral_sortedView',function (event,data) {
        isExit = false;
        setTimeout(function () {
            $state.go("tabs.sortedIntegral",{searchStr:'',cate_id: data});
            // $ionicViewSwitcher.nextDirection('forward');
        },300);
    });
    var inputView = document.getElementById('cft-textField_ig');
    function onScroll() {
        inputView.blur();
    }
    $scope.$on('$ionicView.enter', function () {
        if ($ionicSideMenuDelegate.isOpen()){
            $ionicSideMenuDelegate.toggleRight();
        }
        inputView = document.getElementById('cft-textField_ig');
        var u = navigator.userAgent;
        var isAndroid = u.indexOf('Android') > -1 || u.indexOf('Adr') > -1; //android终端
        var isiOS = !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/); //ios终端
        if (isAndroid) {
            var windowHeight = document.body.clientHeight;
            var minHeight = document.body.clientHeight;
            var tabs = document.getElementsByClassName("tabs")[0];
            
            var isShow = false;
            inputView.onfocus = function () {
                tabs.style.opacity = 0;
                $rootScope.hideTabs = true;
                if (!isShow) {
                    $timeout(function () {
                        isShow = true;
                        // document.body.scrollTop();
                        // document.documentElement.scrollTop();
                        document.body.style.position = "absolute";
                        document.body.style.top = document.body.offsetTop + '1px';
                        document.body.style.position = "static";
                        // window.scrollTop(100);
                    },30);
                }
            
            };
            inputView.onblur = function () {
                isShow = false;
                tabs.style.opacity = 1;
                setTimeout(function () {
                    $rootScope.hideTabs = false;
                },40);
            };
            window.onresize = function () {
                if (document.body.clientHeight < windowHeight) {
                    if (minHeight > document.body.clientHeight){
                        minHeight = document.body.clientHeight;
                        $rootScope.hideTabs = true;
                    }else {
                        // $rootScope.hideTabs = true;
                    }
                
                }
                if (document.body.clientHeight > minHeight) {
                    
                    inputView.blur();
                    $rootScope.hideTabs = false;
                
                }
                
            };
        }
        
    });
    $scope.$on('$ionicView.beforeEnter', function () {
        $rootScope.hideTabs = false;
        $scope.searchValue = '';
        
    });
    
    // $scope.$on('$ionicView.beforeLeave', function () {
    //     setTimeout(function () {
    //         if ($ionicSideMenuDelegate.isOpen){
    //             $ionicSideMenuDelegate.toggleRight();
    //         }
    //     },500);
    //
    // });
    var params = {
        total: perPageCount, //每页多少条数据
        page: currentPage, // 当前页
        integral: 1, //integral
        bannum: 5,//默认5条
        is_recom: 1,
        sortKey: 'on_saleTime'
    };
    
    //搜索
    function goSearch(searchStr) {
        isExit = false;
        inputView.blur();
        $state.go('tabs.sortedIntegral',{searchStr: searchStr});
        $rootScope.hideTabs = true;
        $ionicViewSwitcher.nextDirection('forward');
    }
    //侧栏菜单按钮
    function toggleRight() {
        //打开侧边栏时的一些默认数据，赋值 tab
        $scope.sideMenuObj.sideMenuOnOpened(1,0,$scope.integralObj.cateData);
        $ionicSideMenuDelegate.toggleRight();
        
    }
    
    //进入详情
    function goDetail(item) {
        isExit = false;
        CftStore.set("goodsName",item.goods_name);
        $rootScope.hideTabs = true;
        $state.go('tabs.igDetail',{is_integral: "1", goods_id: item.goods_id});
        // $ionicViewSwitcher.nextDirection('forward');
    }
    
    //下拉刷新
    function doRefresh() {
        console.log($scope.integralObj.totoalGoodsData);
        $scope.integralObj.noneOfMoreData = false;
        currentPage = 1;
        params.page = currentPage;
        
        var getData = {
            success: function (result) {
                
                
                if (result.status == 0){
                    
                    $scope.$broadcast("homeSlideData",$scope.integralObj.slideData);
                    $scope.integralObj.totoalGoodsData = [];
                    $scope.integralObj.totoalGoodsData = result["goodsData"];
                    console.log($scope.integralObj.totoalGoodsData);
                    $scope.integralObj.cateData = result["cateData"];
                    $scope.$broadcast('scroll.refreshComplete');
                    
                    if (result["goodsData"].length >= 10){
                        $scope.integralObj.moredata = false;
                    }else {
                        // $scope.integralObj.moredata = true;
                        if (result["goodsData"].length == 0) {
                            $scope.integralObj.msgsIsNull = 1;
                            $scope.integralObj.emptyLogMsg = "兑换商品为空O(∩_∩)O~";
                        }else {
                            $scope.integralObj.msgsIsNull = 0;
                            $scope.integralObj.emptyLogMsg = "";
                        }
                    }
                    
                    currentPage = 2;
                    // $scope.integralObj.currentpage++;
                }else {
                    $scope.popTipsShow(result.desc);
                }
            },
            error: function (err) {
                console.log(err);
            }
        };
        HttpFactory.getData("/api/getGoods",params)
            .then(
                getData.success,
                getData.error
            );
        
    }
    //加载更多
    function loadMore() {
        console.log("loadMore"+currentPage);
        console.log($scope.integralObj.totoalGoodsData);
        var getData = {
            success: function (result) {
                console.log("请求内部");
                console.log($scope.integralObj.totoalGoodsData);
                if (result.status == 0) {
                    if (result["goodsData"].length < perPageCount) {
                        
                        $scope.integralObj.moredata = true;
                        if (result["goodsData"].length == 0 && $scope.integralObj.totoalGoodsData.length == 0) {
                            $scope.integralObj.msgsIsNull = 1;
                            $scope.integralObj.emptyLogMsg = "兑换商品为空O(∩_∩)O~";
                        }else {
                            $scope.integralObj.msgsIsNull = 0;
                            $scope.integralObj.emptyLogMsg = "";
                        }
                    }else {
                        $scope.integralObj.moredata = false;
                    }
                    if (currentPage == 1){
                        $scope.sideMenuObj.sortedSecondClassObj = result["cateData"];
                        $scope.integralObj.slideData.bannerData = result["bannerData"];
                        
                    }
                    $scope.integralObj.totoalGoodsData = $scope.integralObj.totoalGoodsData.concat(result['goodsData']);
                    currentPage ++;
                    console.log($scope.integralObj.totoalGoodsData);
                    
                }else {
                    $scope.popTipsShow(result.desc);
                }
                $scope.$broadcast('scroll.infiniteScrollComplete');
    
            },
            error: function (err) {
                console.log(err);
            }
        };
        params.page = currentPage;
        HttpFactory.getData("/api/getGoods",params)
            .then(
                getData.success,
                getData.error
            );
        
    }
    //兑换方法
    function convertOption(event,item) {
        isExit = false;
        event.stopPropagation();
        $state.go('tabs.confirmOrder_IG',{goodsArray:JSON.stringify([item])});
        
        return;
    }
}]);