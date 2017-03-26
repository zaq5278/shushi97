/**
/**
 * Created by qingyun on 16/11/30.
 */
angular.module('cftApp.homePage',[]).config(['$stateProvider',function ($stateProvider) {
    $stateProvider.state('tabs.homePage',{
        url:'/homePage',
        views:{
            'tabs-homePage':{
                templateUrl:'homePage.html',
                controller:'homePageController'
            }
        }
    });

}]).controller('homePageController',['$scope','$rootScope','$ionicPopup','HttpFactory','$ionicSideMenuDelegate','$state','$ionicNavBarDelegate','$ionicViewSwitcher','$ionicModal', '$timeout','CftStore',function ($scope,$rootScope,$ionicPopup,HttpFactory,$ionicSideMenuDelegate,$state,$ionicNavBarDelegate,$ionicViewSwitcher,$ionicModal, $timeout,CftStore) {
    //搜索
   $scope.homeObj = {
       //是否是积分商品 0 普通商品 1积分商品
       integral: 0,
       //轮播图数据
       slideData: {
           bannerData: [],
           ishome: 0
       },
       //通过tab 获得侧边栏数据
       cateData: {},
       //当前页数
       currentpage: 1,
       sideObj: {},
       //商品数据
       goodsDatas: [],
       //是否加载更多
       moredata: false,
       //是否有更多数据
       noneOfMoreData: false,
       msgsIsNull: 0,
       emptyLogMsg: '',
       //已售罄
       sellOut: sellOut,
       //切换侧边栏
       toggleRight: toggleRight,
       //进入商品详情页
       goDetail: goDetail,
       //加入购物车
       takeShorpping: takeShorpping,
       //下拉刷新
       doRefresh: doRefresh,
       //加载更多
       loadMore: loadMore,
       //进行搜索
       goSearch: goSearch,
       onScroll: onScroll,
       swipeUp: swipeUp,
       dragup: dragup
    };
    
    
    var isQuit = true;
    function dragup() {
       if ($scope.homeObj.moredata && $scope.homeObj.goodsDatas.length > 0){
           $scope.homeObj.noneOfMoreData = true;
       }
       
    }
    
    var currentPage = 1;
    var params = {
        total: perPageCount, //每页多少条数据
        page: currentPage, // 当前页
        integral: 0, //home
        bannum: 5, //默认5条
        is_recom: 1,
        sortKey: 'on_saleTime'
    };
    var inputView = document.getElementById('cft-textField');
    function onScroll() {
        inputView.blur();
        
    }
    // setTimeout(function () {
    //     $state.go('aboutNapaStore');
    // },2000);
    function swipeUp() {
        inputView.blur();
    }
    // $scope.$on('$ionicView.enter'
    $scope.$on('$ionicView.enter', function () {
        if ($ionicSideMenuDelegate.isOpen()){
            $ionicSideMenuDelegate.toggleRight();
        }
        $scope.sideMenuObj.sideMenuClose();
        inputView = document.getElementById('cft-textField');
        var u = navigator.userAgent;
        var isAndroid = u.indexOf('Android') > -1 || u.indexOf('Adr') > -1; //android终端
        // var isiOS = !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/); //ios终端
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
        // $scope.sideMenuObj.sideMenuClose();
        $rootScope.hideTabs = false;
        $scope.searchValue = '';
       
        
    });
    // $scope.$on('$ionicView.beforeLeave', function () {
    //     setTimeout(function () {
    //
    //
    //     },500);
    //
    // });
    //搜索
    function goSearch(searchStr) {
        isQuit = false;
        inputView.blur();
        $state.go('tabs.sortedGoods',{searchStr: searchStr});
        $rootScope.hideTabs = true;
        $scope.sideMenuObj.isSearch = true;
        $ionicViewSwitcher.nextDirection('forward');
    }
    //首页跳转全部商品分类
    $scope.$on("home_sortedView",function (event,data) {
        isQuit = false;
        setTimeout(function () {
            $state.go("tabs.sortedGoods",{searchStr:'',cate_id: data});
            $ionicViewSwitcher.nextDirection('forward');
        },300)
    });
    
    //侧栏菜单按钮
    function toggleRight() {
        
        console.log("切换");
        $scope.sideMenuObj.sideMenuOnOpened(0,0);
        $ionicSideMenuDelegate.toggleRight();
    }

    //进入商品详情
    function goDetail(item) {
        isQuit = false;
        CftStore.set("goodsName",item.goods_name);
        var clickParam = {
            goods_id : item.goods_id
        };
        HttpFactory.getData("/api/clickGoods",clickParam,"POST").then(function (result) {
         
        },function (error) {
            console.log("失败");
        });
        $state.go('tabs.goodsDetail',{is_integral: "0", goods_id: item.goods_id,goods_icon: item['goods_introduction']});
        $ionicViewSwitcher.nextDirection('forward');
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
            if($scope.collect.val >= $scope.modal.goodsData.goods_number){
                $scope.popTipsShow("抱歉,您添加的商品数量大于库存量");
                return;
            }
            $scope.collect.val ++;
        }
    };
    //加入购物车的模态窗口
    $ionicModal.fromTemplateUrl('shopCarModal.html', {
        scope: $scope,
        animation: 'slide-in-up'
    }).then(function(modal) {
        $scope.modal = modal;
    });
    $scope.openModal = function() {
        //每次打开模态窗口都要清空数量
        $scope.collect.val = 1;
        $scope.modal.show();
    };

    //点击模态窗口的加入购物车触发的方法
    $scope.addToShoppingCar = function () {
        
        var params = {
            goods_id: $scope.modal.goodsData.goods_id,
            num:$scope.collect.val,
            sessid:SESSID
        };
        HttpFactory.getData("/api/ushoppingCart",params,"POST").then(function (result) {
            
            if (result.status == 0) {
                
                user_car_num += 1;
                $scope.user_Car_Num += user_car_num;
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
        $scope.modal.goodsData.goodsNum = $scope.collect.val;
        $scope.modal.hide();
        $state.go("tabs.confirmOrder",{goodsArray:JSON.stringify([$scope.modal.goodsData])});
    };
    //当我们用到模型时，清除它！
    $scope.$on('$destroy', function() {
        $scope.modal.remove();
    });
    function sellOut(event) {
        event.stopPropagation();
        $scope.popTipsShow("商品已售罄");
    }
    //打开模态窗口
    function takeShorpping($event,item) {
        $event.stopPropagation();
        $scope.openModal();
        $scope.modal.goodsData = item;
        $scope.modal.IconRootURL = IconROOT_URL;
    }
    //下拉刷新
    function doRefresh() {
        currentPage = 1;
        // $scope.homeObj.currentpage = 1;
        params.page = currentPage;
        $scope.homeObj.noneOfMoreData = false;
        var getData = {
            success: function (result) {
                
                if (result.status == 0) {
                    if (result.goodsData.length >= 10){
                        $scope.homeObj.moredata = false;
                    }else {
                        if (result.goodsData.length == 0){
                            $scope.homeObj.msgsIsNull = 1;
                            $scope.homeObj.emptyLogMsg = "该推荐商品为空O(∩_∩)O~"
                        }
                    }
                    $scope.homeObj.slideData.bannerData = result["bannerData"];
                    $scope.homeObj.goodsDatas = result["goodsData"];
                    $scope.sideMenuObj.sortedSecondClassObj = result["cateData"];
                    $scope.$broadcast('scroll.refreshComplete');
                }else {
                    
                }
                currentPage = 2;
            },
            error: function (err) {
                
            }
        };
        HttpFactory.getData("/api/getGoods",params)
            .then(
                getData.success,
                getData.error);
        
    }

    //上拉加载
    function loadMore() {
        params.page = currentPage;
        var loadMoreData = {
            success: function (result) {
                console.log(result);
                if (result.status == 0) {
                    if (result["goodsData"].length < perPageCount)
                    {
                        if (result.goodsData.length == 0 && $scope.homeObj.goodsDatas.length == 0){
                            $scope.homeObj.msgsIsNull = 1;
                            $scope.homeObj.emptyLogMsg = "该推荐商品为空O(∩_∩)O~";
                        }else {
                            $scope.homeObj.msgsIsNull = 0;
                            $scope.homeObj.emptyLogMsg = "";
                        }
                        $scope.homeObj.moredata = true;
                    }else {
                        $scope.homeObj.moredata = false;
                        
                    }
                    if (currentPage == 1)
                    {
                        $scope.sideMenuObj.sortedSecondClassObj = result["cateData"];
                        $scope.homeObj.slideData.bannerData = result["bannerData"];
                    }
                    $scope.homeObj.goodsDatas = $scope.homeObj.goodsDatas.concat(result["goodsData"]);
                    //必须放下面
                    currentPage ++;
                    // params.page = $scope.homeObj.currentpage;
                    $scope.$broadcast('scroll.infiniteScrollComplete');
                }else {
                    
                }

            },
            error: function (err) {
                
            }
        };
        HttpFactory.getData("/api/getGoods",params)
            .then(
                loadMoreData.success,
                loadMoreData.error);
        
    }

}]);