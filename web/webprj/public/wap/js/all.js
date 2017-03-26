/**
 * Created by qingyun on 16/11/30.
 */
//js程序入口
angular.module('cftApp',['ionic','cftApp.storageFactory',"ctfApp.keyboardHandler",'cftApp.tabs','cftApp.factories','cftApp.slideBox','ctfApp.searchBar','cftApp.homePage','cftApp.integralStore','cftApp.napaStores','cftApp.personal','cftApp.goodsDetail','cftApp.collection','cftApp.myOrder','cftApp.receiptAddress','cftApp.shoppingCart','cftApp.totalScore','cftApp.payRecord','cftApp.integralGoodsDetail','cftApp.sortedGoods','cftApp.sortedIntegral','RongWebIMWidget','cftApp.confirmOrder','cftApp.evaluatePage','cftApp.scanCodePayment','myApp.codeExtend','myApp.aboutNapaStore'])
    .config(['$stateProvider','$urlRouterProvider','$ionicConfigProvider','$locationProvider',function ($stateProvider,$urlRouterProvider,$ionicConfigProvider,$locationProvider) {
        
        $ionicConfigProvider.platform.android.tabs.position("bottom");
        $ionicConfigProvider.tabs.position('bottom');
        $ionicConfigProvider.tabs.style('standard');
        $ionicConfigProvider.navBar.alignTitle('center');
        $ionicConfigProvider.scrolling.jsScrolling(true);
        ionic.Platform.isFullScreen = true;
        // $ionicConfigProvider.views.maxCache(10);
        // $ionicConfigProvider.templates.maxPrefetch(0);
        // $ionicConfigProvider.views.maxCache(0);
        $stateProvider.state("tabs",{
            url:"/tabs",
            abstract:true,
            templateUrl:"tabs.html",
            controller:'tabsController'
        });
        window.addEventListener('native.keyboardshow', function () {
            document.querySelector('div.tabs').style.display = 'none';
            angular.element(document.querySelector('ion-content.has-tabs')).css('bottom', 0);
        });
        window.addEventListener('native.keyboardhide', function () {
            var tabs = document.querySelectorAll('div.tabs');
            angular.element(tabs[0]).css('display', '');
        });
        //意外跳转
        $locationProvider.hashPrefix('?');
        $urlRouterProvider.otherwise('tabs/homePage');
}]);
var ROOT_URL = "http://www.sunnyshu.cn/sunny/wap",
    perPageCount = 10,
    user_car_num = '',
    IconROOT_URL = "http://www.sunnyshu.cn",
    SESSID = "";
    
    




    
/**
 * Created by chaoshen on 2017/2/21.
 */
angular.module('myApp.aboutNapaStore',[]).config(["$stateProvider",function ($stateProvider) {
    $stateProvider.state('aboutNapaStore',{
        url: '/aboutNapaStore',
        templateUrl: 'aboutNapaStore.html',
        controller: 'aboutNapaStoreController'
        
    });
}])
    .controller('aboutNapaStoreController',[function () {
        
    }]);
/**
 * Created by chaoshen on 2017/2/21.
 */
angular.module('myApp.codeExtend',[]).config(["$stateProvider",function ($stateProvider) {
    $stateProvider.state('codeExtend',{
        url: '/codeExtend',
        templateUrl: 'codeExtend.html',
        controller: 'codeExtendController'
        
    });
}])
    .controller('codeExtendController',[function () {
        
    }]);
/**
 * Created by lx on 2016/12/9.
 */
angular.module('cftApp.collection',['ionic']).config(['$stateProvider',function ($stateProvider) {
    $stateProvider.state('tabs.collectionPager',{
        cache:false,
        url:'/collectionPager',
        views:{
            'tabs-personal':{
                templateUrl:'collectionPager.html',
                controller:"collectionPagerController"
            }
        }
      });
}]).controller('collectionPagerController',['$scope','$ionicModal','HttpFactory','$rootScope','$ionicPopup','$state','$ionicViewSwitcher','CftStore',function ($scope,$ionicModal,HttpFactory,$rootScope,$ionicPopup,$state,$ionicViewSwitcher,CftStore) {
    //隐藏 tabs
    $scope.$on('$ionicView.beforeEnter', function () {
        $rootScope.hideTabs = true;
    });
    $scope.collect = {
        //图片跟地址
        iconRootUrl: '',
        //收藏是否为空的提示
        emptyShopCarStr:'',
        //收藏列表数组
        collectionData: [],
        //删除商品
        deleteCollect:deleteCollect,
        //用户点击商品购买的数量
        val : 1,
        //减少商品数量
        reduce:reduce,
        //增加商品数量
        add:add,
        loadCollectionsData: loadCollectionsData,
        //下拉刷新
        doRefresh:doRefresh,
        //上拉加载
        loadMore:loadMore,
        //是否加载更多
        isShowInfinite:true,
        //立即兑换
        goToExchangeNow:goToExchangeNow,
        //进入商品详情
        goToGoodsDetail:goToGoodsDetail,
        noMoreData: 0,
        noMoreDataMsg: '',
        dragup: dragup

    };
    
    function dragup() {
        
        if (!$scope.collect.isShowInfinite && $scope.collect.collectionData.length > 0){
            $scope.collect.noMoreData = 1;
            $scope.collect.noMoreDataMsg = "没有更多商品...";
        }
        
    }
    var index =0;
    $scope.collect.IconROOT_URL = IconROOT_URL;
    //下拉刷新
    function doRefresh() {
        $scope.collect.noMoreData = 0;
        $scope.collect.noMoreDataMsg = "";
        index = 1;
        loadCollectionsData('下拉');
        $scope.$broadcast('scroll.refreshComplete');
        $scope.collect.isShowInfinite = true;
    }
    //加载更多
    function loadMore() {
        index +=1;
        loadCollectionsData('上拉');
    }
    
    function loadCollectionsData(changeState) {
        var url = "/api/ucollection";
        var params = {
            page:index,
            sessid:SESSID
        };
        HttpFactory.getData(url,params)
            .then(function (result) {
                if(result.status == 10014){
                    $scope.popTipsShow(result.desc);
                    window.history.back();
                    $scope.collect.isShowInfinite = false;
                    return;
                }
                if(!result.collectionData.length){
                    $scope.collect.emptyShopCarStr = "您的收藏列表是空的O(∩_∩)O~";
                }
                
                 if(changeState == "下拉"){
                     index += 1;
                     $scope.collect.collectionData = result.collectionData;
                     
                 }else if(changeState=="上拉" && result.collectionData.length!=0){
                     $scope.collect.collectionData =  $scope.collect.collectionData.concat(result.collectionData);
                 }else if(result.collectionData.length == 0){
                     $scope.collect.isShowInfinite = false;

                 }

                if ($scope.collect.collectionData.length < 8){
                    $scope.collect.isShowInfinite = false;
                }else {

                    $scope.$broadcast('scroll.infiniteScrollComplete');
                }
                
            },function (err) {
                
                $scope.collect.isShowInfinite = false;
            });
    }

    //取消收藏
    function deleteCollect(event,item,index){
        $ionicPopup.show({
            cssClass:'myOrder',
            template:'确认要删除吗?',
            scope: $scope,
            buttons: [
                { text: '取消',
                    onTap:function (e) {
                        var backView = angular.element(document.querySelector('.backdrop'));
                        backView.removeClass('visible');
                        backView.removeClass('active');
                        var body = angular.element(document.querySelector('.myBody'));
                        body.removeClass('popup-open');
                        e.stopPropagation();
                    }
                },
                {
                    text: '确定',
                    onTap: function(e) {
                        var backView = angular.element(document.querySelector('.backdrop'));
                        backView.removeClass('visible');
                        backView.removeClass('active');
                        var body = angular.element(document.querySelector('.myBody'));
                        body.removeClass('popup-open');
                        e.stopPropagation();
                        var params = {
                            id: item["id"],
                            sessid:SESSID
                        
                        };
                        HttpFactory.getData("/api/ucollection",params,"DELETE")
                            .then(function (result) {
                                if (result.status == 0) {
                                    $scope.popTipsShow('删除成功');
                                    $scope.collect.collectionData.splice(index,1);
                                    
                                }else {
                                    $scope.popTipsShow("删除失败");
                                    
                                }
                            },function (err) {
                                
                            });
                    }
                }
            ]
        });
          
        event.stopPropagation();
    }
    
    //加入购物车的模态窗口
    $ionicModal.fromTemplateUrl('shopCarModal.html', {
        scope: $scope,
        animation: 'slide-in-up'
    }).then(function(modal) {
        $scope.modal = modal;
    });
    //打开加入购物车的模态窗口
    $scope.shopCardShow = function(index,event) {
        event.stopPropagation();
        $scope.modal.show();
        
        //用在加入购物车模态窗口详情的商品内容
        $scope.goods_index = $scope.collect.collectionData[index];
        //模态窗口的图片地址前缀
        $scope.modal.IconRootURL =  IconROOT_URL;
        $scope.modal.goodsData = {
            goods_introduction: [$scope.collect.collectionData[index].litpic],
            goods_name:$scope.collect.collectionData[index].title,
            shop_price:$scope.collect.collectionData[index].price,
            goods_number:$scope.collect.collectionData[index].num
        };
        // $scope.modal.goodsData = $scope.collect.collectionData[index];
    };

    //当我们用到模型时，清除它！
    $scope.$on('$destroy', function() {
        $scope.modal.remove();
    });

    //点击模态窗口加入购物车
    $scope.addToShoppingCar =function () {
        if($scope.goods_index.num <= 0){
            $scope.popTipsShow("抱歉,该商品没有库存了!");
            return;
        }
        var params = {
            goods_id: $scope.goods_index.g_id,
            num:$scope.collect.val,
            sessid:SESSID
        };
        HttpFactory.getData("/api/ushoppingCart",params,"POST")
            .then(function (result) {
                
                if (result.status == 0) {
                    $scope.modal.hide();
                    user_car_num += 1;
                    $scope.user_Car_Num += user_car_num;
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
        if($scope.goods_index.num <= 0){
            $scope.popTipsShow("抱歉,该商品没有库存了!");
            return;
        }
        $scope.goods_index.goodsNum = $scope.collect.val;
        
        $state.go("tabs.confirmOrder_personal",{goodsArray:JSON.stringify([$scope.goods_index])});
    };
    function reduce() {
        if($scope.collect.val > 1){
            $scope.collect.val--;
        }
        //让最少为一件
    }
    function  add () {
        if($scope.goods_index.num <= 0){
            $scope.popTipsShow("抱歉,该商品没有库存了!");
            return;
        }
        $scope.collect.val ++;
    }
    //立即兑换
    function goToExchangeNow(index,event) {
        event.stopPropagation();
        $state.go("tabs.confirmOrder_personal",{goodsArray:JSON.stringify([$scope.collect.collectionData[index]])});
        
    }
    //前往商品详情
    function goToGoodsDetail(index,goodsData) {
        
        CftStore.set('goodsName',goodsData.title);
        if (goodsData.is_integral == 0){
            $state.go('tabs.goodsDetail_collection',{is_integral: goodsData.is_integral, goods_id:  goodsData.g_id,goods_icon: goodsData.litpic});
        }else {
            $state.go('tabs.igDetail_personal',{is_integral: goodsData.is_integral, goods_id:  goodsData.g_id});
        }

    }
}]);
/**
 * Created by chaoshen on 2017/1/6.
 */
angular.module('cftApp.confirmOrder',[])
    .config(['$stateProvider',function ($stateProvider) {
        $stateProvider.state('tabs.confirmOrder',{
            url: '/confirmOrder/:goodsArray',
            views: {
                'tabs-homePage': {
                    templateUrl: 'confirmOrder.html',
                    controller: 'confirmOderController'
                }
            }
        }).state('tabs.confirmOrder_IG',{
            url: '/confirmOrder_IG/:goodsArray',
            views: {
                'tabs-integralStore': {
                    templateUrl: 'confirmOrder.html',
                    controller: 'confirmOderController'
                }
            }
        }).state('tabs.confirmOrder_personal',{
            url: '/confirmOrder_personal/:goodsArray',
            views: {
                'tabs-personal': {
                    templateUrl: 'confirmOrder.html',
                    controller: 'confirmOderController'
                }
            }
        });
    }])
    .controller('confirmOderController',['$scope','$rootScope','$stateParams','HttpFactory','$state','$location','$ionicModal','CftStore','MainData',function ($scope, $rootScope, $stateParams, HttpFactory, $state, $location,$ionicModal,CftStore,MainData) {

        //重置用户地址的选择
        MainData.userSelectAddress = null;
        if ($stateParams.goodsArray == "value传值"){
            console.log("value传值");
            console.log(MainData.shopping_car_goodsArray);
            //MainData.shopping_car_goodsArray
            $stateParams.goodsArray = CftStore.get('confirmGoodsArr');
        }
        $scope.$on('$ionicView.beforeLeave',function () {
            CftStore.set('confirmGoodsArr','');
        });
        // console.log(JSON.parse($stateParams.goodsArray));
        $scope.confirmObj = {
            
            //留言文本
            inputMsg: '',
            //是否是积分商品 0 积分商品
            is_integral: JSON.parse($stateParams.goodsArray)[0].is_integral ? JSON.parse($stateParams.goodsArray)[0].is_integral : 0,
            //默认地址对象
            defaultAddress: {},
            //是否有默认地址
            hasDefaultAddress: false,
            //商品数组
            goodsArray: JSON.parse($stateParams.goodsArray),
            //商品总数量
            goodsNum_all:null,
            //图片根地址
            IconROOTURL: IconROOT_URL,
            //商品总金额
            totalPrice: 0,
            bottomTotalPrice: 0,
            //积分商品的总积分
            totalIngegralNum: JSON.parse($stateParams.goodsArray)[0].price || JSON.parse($stateParams.goodsArray)[0].integral,
            //用户总积分余额
            userIntegral: localStorage.getItem("creditNum"),
            //进入收货地址页面
            goReceiptAddress: goReceiptAddress,
            //立即购买
            buyNow: buyNow,
            //立即兑换
            convertNow: convertNow,
            //进入订单详情
            goToLookOrderDetail:goToLookOrderDetail,
            // clickBackView: clickBackView,
            confirmOrderModalImg:'images/confirmOrder_ig.png',
            oid:'',//购买成功或者兑换成功的订单id
            freight:0,//商品运费
            isShow: false
        };
        
        var priceParams = {};
        priceParams["goods_id[]"] = [];
        priceParams["num[]"] = [];
        angular.forEach($scope.confirmObj.goodsArray,function (item) {
            priceParams["goods_id[]"].push(item.goods_id);
            priceParams["num[]"].push(item.goodsNum);
        });
        
        setTimeout(function () {
            if($scope.confirmObj.is_integral == 0){
                HttpFactory.getData('/api/getfsPrice',priceParams).then(function (result) {
                    
                    $scope.confirmObj.totalPrice = parseFloat(result.totalPrice.replace(/[^0-9-.]/g, ''));
                    
                    $scope.confirmObj.freight = parseFloat(result.disPrice);
        
                    angular.forEach($scope.confirmObj.goodsArray,function (item,index) {
                        // item.price =
                        $scope.confirmObj.goodsArray[index].price = result.goodsData[index].shop_price
                    })
                },function (err) {
                    console.log(err);
                });
            }
            
        },500);
        
        //计算商品总数量
        function calculateGoodsNum() {
            for (var i = 0;i < $scope.confirmObj.goodsArray.length;i++){
                if ($scope.confirmObj.goodsArray[i].is_integral == 0){
                    $scope.confirmObj.goodsNum_all += parseInt($scope.confirmObj.goodsArray[i].goodsNum);
                }else {
                    $scope.confirmObj.goodsNum_all = 1;
                }

            }
        }
        //计算商品总价
        function calculateGoodsPrice() {
            var goodsPrice = null;
            for (var i = 0;i < $scope.confirmObj.goodsArray.length;i++){
                if ($scope.confirmObj.goodsArray[i].is_integral == 0){
                    var price_one = $scope.confirmObj.goodsArray[i].shop_price ? $scope.confirmObj.goodsArray[i].shop_price : $scope.confirmObj.goodsArray[i].price;
                    
                    goodsPrice += price_one * $scope.confirmObj.goodsArray[i].goodsNum;
                }
            }
            $scope.confirmObj.totalPrice = goodsPrice;
            
        }
        calculateGoodsNum();
        calculateGoodsPrice();
        //进入收货地址页面
        function goReceiptAddress() {
            if($scope.confirmObj.is_integral == 0){
                if ($location.path().indexOf('confirmOrder_personal') > -1){
                    $state.go("tabs.receiptAddress");
                }else {
                    $state.go("tabs.receiptAddress_home");
                }
            }
            if($scope.confirmObj.is_integral == 1){
                if ($location.path().indexOf('confirmOrder_personal') > -1){
                    $state.go("tabs.receiptAddress");
                }else {
                    if($location.path().indexOf('confirmOrder_IG') > -1){
                        $state.go("tabs.receiptAddress_IG");
                    }else {
                        $state.go("tabs.receiptAddress_home");
                    }
                }
            }
        }
        $scope.$on('$ionicView.beforeLeave',function () {
            // $scope.confirmObj.isShow = false;
            if (document.querySelector('.myBackView')){
                removeBackView();
            }
        });
        $scope.$on('$ionicView.beforeEnter', function () {
            $rootScope.hideTabs = true;
            var infoParams = {
                sessid: SESSID
            };
            HttpFactory.getData("/api/memberInfo", infoParams).then(function (result) {
                
                if (result.status == 0) {
                    $scope.confirmObj.userIntegral = result.integral;
                    CftStore.set('creditNum', result.integral);
                }
            }, function (error) {
                
            });
            //从本地读取默认地址
            if (MainData.userSelectAddress && MainData.userSelectAddress != 'continue'){
                if (MainData.userSelectAddress == 'skip'){
                    
                }else {
                    
                    //用户现在了地址把地址给管理地址的对象
                    $scope.confirmObj.hasDefaultAddress = true;
                    $scope.confirmObj.defaultAddress = MainData.userSelectAddress;
                    $scope.confirmObj.defaultAddress.totalAddress =
                        $scope.confirmObj.defaultAddress.province +
                        $scope.confirmObj.defaultAddress.city +
                        $scope.confirmObj.defaultAddress.address;
                }
                return;
            }
            //网络获取默认地址
            setTimeout(function () {
                
                var getData = {
                    success: function (result) {
                        
                        if (result.status == 0) {
                            if (result.addressData.length == 1){
                                
                                //把网络请求的默认地址给管理地址的对象
                                $scope.confirmObj.defaultAddress = result.addressData[0];
                                $scope.confirmObj.defaultAddress.totalAddress =
                                    $scope.confirmObj.defaultAddress.province +
                                    $scope.confirmObj.defaultAddress.city +
                                    $scope.confirmObj.defaultAddress.address;
                                $scope.confirmObj.hasDefaultAddress = true;
                            }
                            else {
                                $scope.confirmObj.hasDefaultAddress = false;
                            }
                        }
                        $scope.$broadcast('scroll.refreshComplete');
                    },
                    error: function (err) {
                        
                    }
                };
                // requestAddesses();
                var params = {
                    page: 1,
                    sessid:SESSID,
                    setdefault: 1
                };
                HttpFactory.getData("/api/uAddress",params,"GET")
                    .then(
                        getData.success,
                        getData.error
                    );
            },300);
        });



        var goodsIdArray = [];//存放所有的商品id
        var goodsNumArray = [];//存放所有商品的数量
        for (var i = 0; i < $scope.confirmObj.goodsArray.length;i++){
            
            if ($scope.confirmObj.goodsArray[i].goods_id){
                
                goodsIdArray.push($scope.confirmObj.goodsArray[i].goods_id);
            }else {
                goodsIdArray.push($scope.confirmObj.goodsArray[i].g_id);
            }
            
            goodsNumArray.push($scope.confirmObj.goodsArray[i].goodsNum);
        }
        //获取运费
        HttpFactory.getData('/api/getAvgPrice',{sessid:SESSID,goods_id:JSON.stringify(goodsIdArray)}).then(function (result) {
            if (result.status == 0){
                $scope.confirmObj.freight = parseFloat(result.freight);
            }
        });

        /*//确认购买
        function buyNow() {
            // $scope.modal.show();
            // addBackView();
            if($scope.confirmObj.goodsArray[0].goods_number < 1){
                $scope.popTipsShow('库存不足');
                return;
            }
            if ($scope.confirmObj.defaultAddress.id){
                if (!$scope.confirmObj.freight){
                    $scope.popTipsShow('获取运费错误,购买失败!');
                    return;
                }
                var params = {
                    goods_id: JSON.stringify(goodsIdArray),
                    collid: $scope.confirmObj.defaultAddress.id,
                    mess: $scope.confirmObj.inputMsg,
                    num:JSON.stringify(goodsNumArray),
                    disPrice:$scope.confirmObj.freight,//快递费
                    sessid:SESSID
                };
                
                $scope.loadingShow();
                HttpFactory.getData("/api/ordercode",params,"POST").then(function (result) {
                    
                    $scope.loadingOrPopTipsHide();
                    if (result.status == 0){
                        result = result.parameters;
                        $scope.confirmObj.oid = result.oid;
                        wx.chooseWXPay({
                            timestamp: result.timeStamp, // 支付签名时间戳，注意微信jssdk中的所有使用timestamp字段均为小写。但最新版的支付后台生成签名使用的timeStamp字段名需大写其中的S字符
                            nonceStr: result.nonceStr, // 支付签名随机串，不长于 32 位
                            package: result.package, // 统一支付接口返回的prepay_id参数值，提交格式如：prepay_id=***）
                            signType: 'MD5', // 签名方式，默认为'SHA1'，使用新版支付需传入'MD5'
                            paySign: result.paySign, // 支付签名
                            success: function (res) {
                                // 支付成功后的回调函数
                                if(res.errMsg == "chooseWXPay:ok"){
                                    $scope.confirmObj.confirmOrderModalImg = 'images/confirmOrder.png';
                                    $scope.modal.show();
                                    // addBackView();
                                    
                                }else {
                                    $scope.popTipsShow("支付出错!");
                                }
                            }
                        });
                    }else {
                        $scope.popTipsShow(result.desc);
                    }

                },function (err) {
                    $scope.popTipsShow("库存不足！");
                })

            }else {
                $scope.popTipsShow("请先选择收货地址");
            }
        }
        function addBackView() {
            setTimeout(function () {
                var backView = document.querySelector('body>.modal-backdrop.active');
                var newNode = document.createElement("div");
                console.log(backView);
                newNode.className = 'myBackView';
                console.log(newNode);
                backView.appendChild(newNode);
                // newNode.onclick = function (e) {
                //     // e.stopPropagation();
                // }
                
            },300)
        }
        function removeBackView() {
            var backView = document.querySelector('.modal-backdrop');
            var myView = document.querySelector('.myBackView');
            backView.removeChild(myView);
        }*/
        /*//立即兑换
        function convertNow() {
            //默认地址存在的情况
            if ($scope.confirmObj.goodsArray[0].goods_number <= 1) {
                $scope.popTipsShow('库存不足！');
            }
            $scope.loadingShow();
            if ($scope.confirmObj.defaultAddress.id){
                var params = {
                    goods_id: $scope.confirmObj.goodsArray[0].goods_id,
                    collid: $scope.confirmObj.defaultAddress.id,
                    mess: $scope.confirmObj.inputMsg,
                    sessid:SESSID
                };
                
                HttpFactory.getData("/api/integralOrder",params,"POST")
                    .then(function (result) {
                        if (result.status == 0){
                            $scope.loadingOrPopTipsHide();
                            $scope.confirmObj.confirmOrderModalImg = 'images/confirmOrder_ig.png';
                            $scope.confirmObj.oid = result.oid;
                            $scope.modal.show();
                            // addBackView();
                            // $scope.confirmObj.isShow = true;
                        }else {
                            $scope.popTipsShow(result.desc);
                        }
                    },function (err) {
                        
                    });
            }else {
                $scope.popTipsShow("请先选择收货地址");
            }
        }
        // function qunide() {
        //     angular.element(document.querySelector('.modal-backdrop'))
        // }
        //兑换成功的模态提示
        $ionicModal.fromTemplateUrl('confirmOrderModal.html', {
            scope: $scope,
            animation: 'slide-in-enter'
        }).then(function(modal) {
            $scope.modal = modal;
        });
        //当我们不用模型时，清除它！
        $scope.$on('$destroy', function() {
            $scope.modal.remove();
        });
        //点击查看订单详情进入订单详情
        function goToLookOrderDetail() {
            // removeBackView();
            // console.log("hello");
            // $scope.confirmObj.isShow = false;
            CftStore.set("inputMsg",$scope.confirmObj.inputMsg);
            $scope.modal.hide();
            switch ($state.current.name){
                case 'tabs.confirmOrder_IG':
                    $state.go('tabs.orderDetail_ig',{oid:$scope.confirmObj.oid});
                    break;
                case 'tabs.confirmOrder':
                    $state.go('tabs.orderDetail_home',{oid:$scope.confirmObj.oid});
                    break;
                case 'tabs.confirmOrder_personal':
                    $state.go('tabs.orderDetail',{oid:$scope.confirmObj.oid});
                    break;
            }
        }*/
        //确认购买
        function buyNow() {
            if($scope.confirmObj.goodsArray[0].goods_number < 1){
                $scope.popTipsShow('库存不足');
                return;
            }
            if ($scope.confirmObj.defaultAddress.id){
                if (!$scope.confirmObj.freight){
                    $scope.popTipsShow('获取运费错误,购买失败!');
                    return;
                }
                console.log($scope.confirmObj.goodsArray);
                var params = {
                    goods_id: JSON.stringify(goodsIdArray),
                    collid: $scope.confirmObj.defaultAddress.id,
                    mess: $scope.confirmObj.inputMsg,
                    num:JSON.stringify(goodsNumArray),
                    disPrice:$scope.confirmObj.freight,//快递费
                    sessid:SESSID
                };
                console.log(params);
                $scope.loadingShow();
                HttpFactory.getData("/api/ordercode",params,"POST").then(function (result) {
                    console.log('**************************');
                    console.log(result);
                    $scope.loadingOrPopTipsHide();
                    if (result.status == 0){
                        result = result.parameters;
                        $scope.confirmObj.oid = result.oid;
                        wx.chooseWXPay({
                            timestamp: result.timeStamp, // 支付签名时间戳，注意微信jssdk中的所有使用timestamp字段均为小写。但最新版的支付后台生成签名使用的timeStamp字段名需大写其中的S字符
                            nonceStr: result.nonceStr, // 支付签名随机串，不长于 32 位
                            package: result.package, // 统一支付接口返回的prepay_id参数值，提交格式如：prepay_id=***）
                            signType: 'MD5', // 签名方式，默认为'SHA1'，使用新版支付需传入'MD5'
                            paySign: result.paySign, // 支付签名
                            success: function (res) {
                                // 支付成功后的回调函数
                                if(res.errMsg == "chooseWXPay:ok"){
                                    $scope.confirmObj.confirmOrderModalImg = 'images/confirmOrder.png';
                                    $scope.modal.show();
                                }else {
                                    $scope.popTipsShow("支付出错!");
                                }
                            }
                        });
                    }else {
                        $scope.popTipsShow(result.desc);
                    }
                
                },function (err) {
                    $scope.popTipsShow("库存不足！");
                })
            
            }else {
                $scope.popTipsShow("请先选择收货地址");
            }
        }
    
        //立即兑换
        function convertNow() {
            //默认地址存在的情况
            console.log($scope.confirmObj.goodsArray[0]);
            if ($scope.confirmObj.goodsArray[0].goods_number <= 1) {
                $scope.popTipsShow('库存不足！');
            }
            $scope.loadingShow();
            if ($scope.confirmObj.defaultAddress.id){
                var params = {
                    goods_id: $scope.confirmObj.goodsArray[0].goods_id,
                    collid: $scope.confirmObj.defaultAddress.id,
                    mess: $scope.confirmObj.inputMsg,
                    sessid:SESSID
                };
            
                HttpFactory.getData("/api/integralOrder",params,"POST")
                    .then(function (result) {
                        if (result.status == 0){
                            $scope.loadingOrPopTipsHide();
                            $scope.confirmObj.confirmOrderModalImg = 'images/confirmOrder_ig.png';
                            $scope.confirmObj.oid = result.oid;
                            $scope.modal.show();
                        }else {
                            $scope.popTipsShow(result.desc);
                        }
                    },function (err) {
                    
                    });
            }else {
                $scope.popTipsShow("请先选择收货地址");
            }
        }
        //兑换成功的模态提示
        $ionicModal.fromTemplateUrl('confirmOrderModal.html', {
            scope: $scope,
            animation: 'slide-in-enter'
        }).then(function(modal) {
            $scope.modal = modal;
        });
        //当我们不用模型时，清除它！
        $scope.$on('$destroy', function() {
            $scope.modal.remove();
        });
        //点击查看订单详情进入订单详情
        function goToLookOrderDetail() {
            CftStore.set("inputMsg",$scope.confirmObj.inputMsg);
            $scope.modal.hide();
            switch ($state.current.name){
                case 'tabs.confirmOrder_IG':
                    $state.go('tabs.orderDetail_ig',{oid:$scope.confirmObj.oid});
                    break;
                case 'tabs.confirmOrder':
                    $state.go('tabs.orderDetail_home',{oid:$scope.confirmObj.oid});
                    break;
                case 'tabs.confirmOrder_personal':
                    $state.go('tabs.orderDetail',{oid:$scope.confirmObj.oid});
                    break;
            }
        }
    }]);
/**
 * Created by chaoshen on 2017/1/16.
 */
angular.module("cftApp.evaluatePage",[])
    .config(["$stateProvider",function ($stateProvider) {
        $stateProvider.state('tabs.evaluatePage',{
            url: '/evaluatePage',
            params: {
                goodsMsg: {}
            },
            views: {
                'tabs-personal': {
                    templateUrl: 'evaluatePage.html',
                    controller: 'evaluateController'
                }
            }
        });
    }])
    .controller('evaluateController',['$scope','$rootScope','$stateParams','HttpFactory','CftStore',function ($scope,$rootScope,$stateParams,HttpFactory,CftStore) {
        
        //页面载入前
        $scope.$on('$ionicView.beforeEnter', function () {
            $rootScope.hideTabs = true;
        });
        $scope.evaluateObj = {
            selectedStar: selectedStar,
            goodsData: $stateParams.goodsMsg.goods_data,
            iconRootURL: '',
            assessSubmit: assessSubmit,
            inputMsg: ''
        
        };
        var params = {
            goods_id: '',
            oid: '',
            num: '',
            mess: ''
        };
        var assessNum = 0;
        function assessSubmit() {
            
            params.goods_id = $scope.evaluateObj.goodsData[assessNum].goods_id;
            params.oid = $stateParams.goodsMsg.ordercode;
            params.mess = $scope.evaluateObj.inputMsg[assessNum];
            params.num = $scope.evaluateObj.goodsData[assessNum].stars || 5;
            
            HttpFactory.getData("/api/assess",params,"POST").then(function (result) {
                
                // $scope.popTipsShow(result.desc);
                if(result.status == 0){
                    assessNum ++;
                    if (assessNum >= $scope.evaluateObj.goodsData.length){
                        $scope.popTipsShow(result.desc);
                        $scope.$emit('detailEvaSucc','success');
                        // CftStore.set('evaluateSuccess','yes');
                        window.history.go(-1);
                        return ;
                    }
                    assessSubmit(assessNum);
                    
                }else {
                    $scope.$emit('detailEvaSucc','success');
                    $scope.popTipsShow(result.desc);
                }
            },function (error) {
                console.log(error);
            });
        }
        
        $scope.evaluateObj.iconRootURL = IconROOT_URL;
        
        //设置星的评级
        function selectedStar(e,index,item) {
            
            item.stars = index + 1;
            params.num = index + 1;
            var stars = angular.element(e.target).parent().children();
            
            for (var i = 0; i<6; i ++){
                if (i <= index){
                    angular.element(stars[i]).removeClass("assess-grayStar");
                    angular.element(stars[i]).addClass("assess-redStar");
                }else {
                    angular.element(stars[i]).removeClass("assess-redStar");
                    angular.element(stars[i]).addClass("assess-grayStar");
                }
            }
            
        
        }
        $scope.graynums = [];
        $scope.graynums.length = 5;
        $scope.goodsIcons = ['img1'];
    }]);
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
/**
 * Created by lx on 2016/12/9.
 */
angular.module('cftApp.myOrder',['ionic','cftApp.orderDetail']).config(['$stateProvider',function ($stateProvider) {
    $stateProvider.state('tabs.myOrder',{
        url:'/myOrder',
        cache: false,
        views: {
           'tabs-personal': {
               templateUrl:'myOrder.html',
               controller:'myOrderController'
           }
        }
        
    });

}]).controller('myOrderController',['$rootScope','$scope','$state','$ionicModal','$ionicViewSwitcher','$ionicPopup','$ionicScrollDelegate','$timeout','HttpFactory','CftStore','$stateParams',function ($rootScope,$scope,$state,$ionicModal,$ionicViewSwitcher,$ionicPopup,$ionicScrollDelegate,$timeout,HttpFactory,CftStore,$stateParams) {
    $scope.myOrder = {
        //用于过滤数据的关键字
        keyWords:'',
        //导航栏选项再点击选项按钮时触发的事件
        navData:navData,
        //全部订单信息
        orderDatas:[],
        //状态列表
        stateInfos: ["待付款","待发货","待收货","交易完成","退款中","已退款","交易关闭"],
        //存储订单商品信息
        allData:'',
        //取消订单的方法
        cancelBill:cancelBill,
        //付款的方法
        payment:payment,
        //退款的方法
        refund:refund,
        //确认订单的方法
        confirm:confirm,
        //评价的方法
        appraise:appraise,
        //跳转订单详情
        goOrderDetail: goOrderDetail,
        //图片根地址
        IconRootURL: '',
        //申请退款原因
        applyMsg: '',
        //为空的信息
        emptyMsg: '',
        //是否加载更多
        moredata: false,
        isEvaluate: false,
        //没有更多数据时显示的文本
        noMoreDataMsg: '',
        //加载更多
        loadMore: loadMore,
        //刷新
        doRefresh: doRefresh,
        noMoreData: false,
        noneMsg: '',
        nullMsg: '',
        dataIsNull: false,
        dragup: dragup
        

    };
    
    function dragup() {
        if ($scope.myOrder.moredata && $scope.myOrder.orderDatas.length > 0){
            $scope.myOrder.noMoreData = true;
        }
    }
    var currentPage = 1;
    var orderState = '';
    var params = {
        page:1,
        total:10,
        state:'0',
        sessid: SESSID
        // type: 1
    };
    
    $scope.myOrder.IconRootURL = IconROOT_URL;
    
    //隐藏 tabs
    $scope.$on('$ionicView.beforeLeave',function () {
        CftStore.set('personalInto','');
    });
    $scope.$on('$ionicView.beforeEnter', function () {
        
        $rootScope.hideTabs = true;
        console.log("tabNum");
        console.log(CftStore.get('tabNum'));
        var tabNum = CftStore.get('tabNum');
        if ((CftStore.get('personalInto') != 'yes') && !CftStore.get('tabNum')){
            tabNum = '0';
            console.log("第一个");
        }
        if (CftStore.get('personalInto') == 'yes'){
            tabNum = '0';
            console.log("第二个");
        }
        
        var topWrapper = angular.element(document.querySelector('.topListWrapper')).children();
        topWrapper.removeClass('active');
        angular.element(topWrapper[tabNum]).addClass('active');
        params.page = 1;
        params.is_evaluation = '';
        
        switch (tabNum){
            //全部
            case '0':
                params.state = '0';
                break;
            case '1':
                params.state = '7';
                break;
            case '2':
                params.state = '1';
                break;
            case '3':
                params.state = '2';
                break;
            //    待评价
            case '4':{
                params.state = '3';
                params.is_evaluation = 2;
            }break;
        }
        
    });
    $scope.$on("",function (event,data) {
        setTimeout(function () {
            $state.go("tabs.sortedGoods",{searchStr:'',cate_id: data});
            $ionicViewSwitcher.nextDirection('forward');
        },300)
    });
    //下拉刷新
    function doRefresh() {
        $scope.myOrder.noMoreData = false;
        getOrders();
    }
    //下拉刷新获取订单数据
    function getOrders() {
        params.page = 1;
        
        HttpFactory.getData('/api/Order',params)
            .then(function (result) {
                if (result.status == 0) {
                    
                    $scope.$broadcast('scroll.refreshComplete');
                    
                    $scope.loadingOrPopTipsHide();
                    
                    $scope.myOrder.orderDatas = result.orderData;
                    if (result.orderData.length < perPageCount) {
                        if (result.orderData.length == 0) {
                            $scope.myOrder.dataIsNull = true;
                            $scope.myOrder.nullMsg = "您的订单是空的O(∩_∩)O~";
                        }else {
                            $scope.myOrder.dataIsNull = false;
                            $scope.myOrder.nullMsg = "";
                        }
                    }else {
                        $scope.myOrder.nullMsg = "";
                    }
                    
                    $scope.myOrder.moredata = (result.orderData.length < 10);
                    params.page ++;
                }
            },function (err) {
                
            });
    }
    //上拉加载
    function loadMore() {
        
        HttpFactory.getData('/api/Order',params)
            .then(function (result) {
                
                if (result.status == 0) {
                    $scope.loadingOrPopTipsHide();
                    if (result.orderData.length < perPageCount) {
                        if (result.orderData.length == 0 && $scope.myOrder.orderDatas.length == 0) {
                            $scope.myOrder.dataIsNull = true;
                            $scope.myOrder.nullMsg = "当前订单列表是空的O(∩_∩)O~";
                        }else {
                            $scope.myOrder.dataIsNull = false;
                            $scope.myOrder.nullMsg = "";
                        }
                    }else {
                        $scope.myOrder.dataIsNull = false;
                        $scope.myOrder.nullMsg = "";
                    }
                    $scope.myOrder.orderDatas = $scope.myOrder.orderDatas.concat(result.orderData);
                    
                    $scope.myOrder.moredata = (result.orderData.length < 10);
                    params.page ++;
                }else {
                    $scope.myOrder.moredata = true;
                }
                $scope.$broadcast('scroll.infiniteScrollComplete');
            },function (err) {
            });
    }
    //进入订单详情
    function goOrderDetail(orderData) {
        
        var orderObj = {
            orderData: orderData
        };
        //传参： 订单号
        $state.go('tabs.orderDetail',{oid: orderData.ordercode});
    }
    //点击导航栏菜单
    function navData(event) {
        $scope.myOrder.dataIsNull = false;
        $scope.myOrder.nullMsg = "";
        $scope.myOrder.noMoreData = false;
        $scope.myOrder.noneMsg = "";
        //每次点击的时候 页面置顶
        $ionicScrollDelegate.$getByHandle('orderScroll').scrollTop();
        //每次点击的时候不让下拉走
        $scope.myOrder.moredata = true;

        var list = angular.element(event.currentTarget).children();
        var item = angular.element(event.target);
        
        //对数据进行过滤
        if (item.text() == '全 部'){
            params.page = 1;
            $scope.myOrder.orderDatas = [];
            $scope.myOrder.moredata = false;
            params.state = '0';
            CftStore.set('tabNum','0');
        }
        //改变元素的样式.
        if (event.currentTarget != event.target){
            list.removeClass('active');
            item.addClass('active');
            params.page = 1;
            $scope.myOrder.orderDatas = [];
            $scope.myOrder.moredata = false;
            params.is_evaluation = '';
            // $scope.loadingShow();
            switch (item.text()){
                case '待付款':
                    CftStore.set('tabNum','1');
                    params.state = '7';
                    // params.is_evaluation = '';
                    // getOrders("0");
                    break;
                case '待发货':
                    CftStore.set('tabNum','2');
                    params.state = '1';
                    // getOrders("1");
                    break;
                case '待收货':
                    params.state = '2';
                    CftStore.set('tabNum','3');
                    // getOrders("2");
                    break;
                case '待评价':
                    CftStore.set('tabNum','4');
                    params.state = '3';
                    params.is_evaluation = 2;
                    // getOrders("3");
                    break;
                default:
                    break;
            }
        }
    }
    //取消订单
    function cancelBill(event,index,order) {
        
        event.stopPropagation();
        //弹出弹框
            $ionicPopup.show({
            cssClass:'myOrder',
            template:'确认要取消订单吗？',
            buttons:[{
                text:'取消',
                onTap:function (e) {
                    var backView = angular.element(document.querySelector('.backdrop'));
                    backView.removeClass('visible');
                    backView.removeClass('active');
                    var body = angular.element(document.querySelector('.myBody'));
                    body.removeClass('popup-open');
                    e.stopPropagation();
                    
                }
            },{
                text:'确定',
                onTap:function () {
                    var backView = angular.element(document.querySelector('.backdrop'));
                    backView.removeClass('visible');
                    backView.removeClass('active');
                    var body = angular.element(document.querySelector('.myBody'));
                    body.removeClass('popup-open');
                    var params_index = {
                        sseid: SESSID,
                        oid: order.ordercode,
                        state: 6
                    };
                    HttpFactory.getData("/api/Order",params_index,"PATCH").then(function (result) {
                        
                        if(result.status == 0){
                            doRefresh();
                            $scope.myOrder.orderDatas[index].state = "6";
                            $scope.popTipsShow("取消成功!");
                            
                        }else {
                            $scope.popTipsShow(result.desc);
                        }

                    },function (err) {
                        
                    });
                }
            }]

        });

    }

    //兑换成功的模态提示
    $ionicModal.fromTemplateUrl('confirmOrderModal.html', {
        scope: $scope,
        animation: 'slide-in-enter'
    }).then(function(modal) {
        $scope.modal = modal;
    });
    //当我们不用模型时，清除它！
    $scope.$on('$destroy', function() {
        $scope.modal.remove();
    });
    //付款
    function payment(event,index) {
        
        event.stopPropagation();
        var params = {};
        if ($scope.myOrder.orderDatas[index].goods_data.length == 1){
            params = {
                oid: $scope.myOrder.orderDatas[index].ordercode
            };
        }
        if ($scope.myOrder.orderDatas[index].goods_data.length > 1){
            params = {
                moid: $scope.myOrder.orderDatas[index].ordercode
            };
        }
        $scope.loadingShow();
        HttpFactory.getData("/api/memOrderListPay",params,"POST").then(function (result) {
            
            $scope.loadingOrPopTipsHide();
            if (result.status == 0){
                result = result.parameters;
                wx.chooseWXPay({
                    timestamp: result.timeStamp, // 支付签名时间戳，注意微信jssdk中的所有使用timestamp字段均为小写。但最新版的支付后台生成签名使用的timeStamp字段名需大写其中的S字符
                    nonceStr: result.nonceStr, // 支付签名随机串，不长于 32 位
                    package: result.package, // 统一支付接口返回的prepay_id参数值，提交格式如：prepay_id=***）
                    signType: 'MD5', // 签名方式，默认为'SHA1'，使用新版支付需传入'MD5'
                    paySign: result.paySign, // 支付签名
                    success: function (res) {
                        // 支付成功后的回调函数
                        if(res.errMsg == "chooseWXPay:ok"){
                            var params_index = {
                                sseid: SESSID,
                                oid: $scope.myOrder.orderDatas[index].ordercode,
                                state: 1
                            };
                            HttpFactory.getData("/api/Order",params_index,"PATCH").then(function (result) {
                                $scope.myOrder.orderDatas[index].state = "1";
                                $scope.confirmObj.confirmOrderModalImg = 'images/confirmOrder.png';
                                $scope.modal.show();
                                $scope.popTipsShow("付款成功");
    
                                doRefresh();

                            },function (err) {

                            });

                        }else {
                            $scope.popTipsShow("支付出错!");
                        }
                    }
                });
            }else {
                $scope.popTipsShow(result.desc);
            }

        },function (err) {
            $scope.popTipsShow("访问异常!");
        })


    }
    //申请退款
    function refund(event,index) {
        event.stopPropagation();
        
        $ionicPopup.show({
            cssClass:'myOrder refund',
            template:'<p>申请退款</p><textarea id="applyMsg" ng-model="myOrder.applyMsg" placeholder="请输入申请退款的原因？" maxlength="100"></textarea><div>{{myOrder.applyMsg.length}}/100</div>',
            scope: $scope,
            buttons:[{
                text:'取消',
                onTap:function (e) {
                    var backView = angular.element(document.querySelector('.backdrop'));
                    backView.removeClass('visible');
                    backView.removeClass('active');
                    var body = angular.element(document.querySelector('.myBody'));
                    body.removeClass('popup-open');
                    e.stopPropagation();
                }
            },{
                text:'确定',
                onTap:function () {
                    var backView = angular.element(document.querySelector('.backdrop'));
                    backView.removeClass('visible');
                    backView.removeClass('active');
                    var body = angular.element(document.querySelector('.myBody'));
                    body.removeClass('popup-open');
                    var params = {
                        sseid: SESSID,
                        oid: $scope.myOrder.orderDatas[index].ordercode,
                        state: 4,
                        mess: $scope.myOrder.applyMsg
                    };
                    HttpFactory.getData("/api/Order",params,"PATCH")
                        .then(function (result) {
                            
                            if(result.status == 0){
                                $scope.myOrder.orderDatas[index].state = "4";
                                $scope.popTipsShow("申请已提交!");
                                doRefresh();
                            }else {
                                $scope.popTipsShow(result.desc);
                            }
                        },function (error) {
                            
                        });
                }
            }]

        });
    }
    //确认收货
    function confirm(event,index) {
        
        event.stopPropagation();
        $ionicPopup.show({
            cssClass:'myOrder sure',
            template:'确认是否已收到货？',
            buttons:[{
                text:'取消',
                onTap:function (e) {
                    var backView = angular.element(document.querySelector('.backdrop'));
                    backView.removeClass('visible');
                    backView.removeClass('active');
                    var body = angular.element(document.querySelector('.myBody'));
                    body.removeClass('popup-open');
                    e.stopPropagation();
                }
            },{
                text:'确定',
                onTap:function () {
                    var backView = angular.element(document.querySelector('.backdrop'));
                    backView.removeClass('visible');
                    backView.removeClass('active');
                    var body = angular.element(document.querySelector('.myBody'));
                    body.removeClass('popup-open');
                    var params_index = {
                        sseid: SESSID,
                        oid: $scope.myOrder.orderDatas[index].ordercode,
                        state: 3
                    };
                    HttpFactory.getData("/api/Order",params_index,"PATCH").then(function (result) {
                        
                        if(result.status == 0){
                            $scope.myOrder.orderDatas[index].state = "3";
                            $scope.popTipsShow("交易完成!");
                            doRefresh();
                        }else {
                            $scope.popTipsShow(result.desc);
                        }

                    },function (err) {

                    });
                }
            }]

        });
        
    }
    //去评价
    function appraise(order,event) {
        event.stopPropagation();
        // CftStore.set('tabNum','4');
        $state.go('tabs.evaluatePage',{goodsMsg:order});
    }
}]);





/**
 * Created by qingyun on 16/11/30.
 */
angular.module('cftApp.napaStores',[]).config(['$stateProvider',function ($stateProvider) {
    $stateProvider.state('tabs.napaStores',{
        url:'/napaStores',
        views:{
            'tabs-napaStores':{
                templateUrl:'napaStores.html',
                controller:'napaStoresController'
            }
        }
    });
}]).controller('napaStoresController',['$scope','$state','HttpFactory','$ionicModal','$http',function ($scope,$state,HttpFactory,$ionicModal,$http) {
    $scope.items = ['1','2','3'];
    // var loa = getNapaStores();
    var napaObj = $scope.napaObj = {
        storesData: {},
        provinces: [],
        doRefresh: doRefresh,
        loadMore: loadMore,
        moreData: false,
        dataIsNull: 0,
        nullMsg: '',
        noMoreData: 0,
        dragup: dragup
    };
    
    //用于双向绑定地址的变量
    $scope.napaStores = {
        text:'定位中...'
    };
    function dragup() {
        if (!napaObj.moreData && napaObj.storesData.length > 0){
            napaObj.noMoreData = 1;
        }
    }
    var currentPage = 1;
    $http.get('lib/city.json')
        .success(function (result) {
            $scope.napaObj.provinces = result;
            $scope.napaObj.provinces.splice(0,1);
            
        });

    $ionicModal.fromTemplateUrl('choiceAddressModal.html',{
        scope:$scope,
        animation: 'fade-out',
        focusFirstInput:true,
        backdropClickToClose:true
    }).then(function(modal) {
        $scope.modal = modal;
    });


    //当我们用完模型时，清除它！
    $scope.$on('$destroy', function() {
        $scope.modal.remove();
    });
    setTimeout(function () {
        if ($scope.napaStores.text == "定位中..."){
            $scope.popTipsShow("网络请求超时");
        }
    },20000);
    wx.getLocation({
        type: 'wgs84', // 默认为wgs84的gps坐标，如果要返回直接给openLocation用的火星坐标，可传入'gcj02'
        success: function (res) {
            var latitude = res.latitude; // 纬度，浮点数，范围为90 ~ -90
            var longitude = res.longitude; // 经度，浮点数，范围为180 ~ -180。
            var point = new BMap.Point(parseFloat(longitude), parseFloat(latitude));
            var gc = new BMap.Geocoder();
            gc.getLocation(point, function (rs) {
                var addComp = rs.addressComponents;
                if (addComp.province.indexOf('省') > -1){
                    $state.reload();
                    $scope.napaStores.text = addComp.province.split('省')[0];
                }
            });
        },
        cancel: function (res) {
            $scope.popTipsShow('用户拒绝授权获取地理位置');
        }
    });
    function doRefresh() {
        napaObj.noMoreData = 0;
        getNapaStores('refresh');
    }
    function loadMore() {
        getNapaStores('loadMore')
    }
    //获取加盟店信息
    function getNapaStores(loadName) {
        var params = {
            nums:5,
            province:$scope.napaStores.text,
            page: currentPage
        };
        
        HttpFactory.getData("/api/franchise",params).then(function (result) {
            
            if (result["data"].length == 0 && napaObj.storesData.length == 0){
                napaObj.dataIsNull = 1;
                napaObj.nullMsg = '暂无加盟店信息O(∩_∩)O~';
                
            }else {
                napaObj.dataIsNull = 0;
                napaObj.nullMsg = '';
            }
            
            //下拉刷新
            if (loadName == 'refresh'){
                $scope.$broadcast('scroll.refreshComplete');
                currentPage = 2;
                napaObj.storesData = result["data"];
            }
            //加载更多
            if (loadName == 'loadMore'){
                currentPage ++;
                napaObj.moreData = result["data"].length >= perPageCount;
                napaObj.storesData = napaObj.storesData.concat(result["data"]);
                $scope.$broadcast('scroll.infiniteScrollComplete');
            }
            
            if(napaObj.storesData == undefined){
                $scope.popTipsShow("暂无加盟店信息!");
            }
        },function (err) {
        });
    }
    //监控用户城市的选择
    $scope.$watch('napaStores.text',function (newVal,oldVal) {
        if (newVal != oldVal){
            doRefresh();
            // $scope.napaObj.moreData = true;
            // loadMore();
        }
    });
}]);
/**
 * Created by chaoshen on 16/12/13.
 */
angular.module('cftApp.orderDetail',[])
    .config(['$stateProvider',function ($stateProvider) {
        $stateProvider.state('tabs.orderDetail',{
            url: '/orderDetail/:oid',
            views: {
                'tabs-personal': {
                    templateUrl: 'orderDetail.html',
                    controller: 'orderDetailController'
                }
            }
        }).state('tabs.orderDetail_home',{
            url: '/orderDetail_home/:oid',
            views: {
                'tabs-homePage': {
                    templateUrl: 'orderDetail.html',
                    controller: 'orderDetailController'
                }
            }
        }).state('tabs.orderDetail_ig',{
            url: '/orderDetail_ig/:oid',
            views: {
                'tabs-integralStore': {
                    templateUrl: 'orderDetail.html',
                    controller: 'orderDetailController'
                }
            }
        });
    }])
    .controller('orderDetailController',['$scope','HttpFactory','$stateParams','CftStore','$ionicPopup','$state','$rootScope',function ($scope,HttpFactory,$stateParams,CftStore,$ionicPopup,$state,$rootScope) {
        
        //全部数据
        $scope.orderDetail = {
            orderData: null,
            applyMsg: '',
            stateInfos: ["未付款","待发货","待收货","交易完成","退款中","已退款","交易关闭"],
            cancelBill: cancelBill,
            payment: payment,
            confirm: confirm,
            appraise: appraise,
            refund: refund,
            goDetail: goDetail
        };
        $scope.$on("toDetailSuccess",function (data) {
            if (data == 'success'){
                $scope.orderDetail.orderData.is_evaluation = '0'
            }
        });
        $scope.$on('$ionicView.beforeEnter', function () {
            $rootScope.hideTabs = true;
            // var evaluateSuccess = CftStore.get('evaluateSuccess');
            // if (orderDetail.orderData.state=='3' && evaluateSuccess == 'yes'){
            //     orderDetail.orderData.state=='3'
            // }

        });
        function goDetail(item) {
            
            var clickParam = {
                goods_id : item.goods_id
            };
            CftStore.set('goodsName',item.goods_name);
            HttpFactory.getData("/api/clickGoods",clickParam,"POST").then(function (result) {
                console.log("成功");
            },function (error) {
                console.log("失败");
            });
            $state.go('tabs.goodsDetail_orderDetail',{is_integral: "0", goods_id: item.goods_id,goods_icon: item['goods_introduction']});
            // $ionicViewSwitcher.nextDirection('forward');
        }
        function cancelBill(item,e) {
            
            $ionicPopup.show({
                cssClass:'myOrder',
                template:'确认要取消订单吗？',
                buttons:[{
                    text:'取消',
                    onTap:function (e) {
                        var backView = angular.element(document.querySelector('.backdrop'));
                        backView.removeClass('visible');
                        backView.removeClass('active');
                        var body = angular.element(document.querySelector('.myBody'));
                        body.removeClass('popup-open');
                        e.stopPropagation();
                    }
                },{
                    text:'确定',
                    onTap:function () {
                        var backView = angular.element(document.querySelector('.backdrop'));
                        backView.removeClass('visible');
                        backView.removeClass('active');
                        var body = angular.element(document.querySelector('.myBody'));
                        body.removeClass('popup-open');
                        var params_index = {
                            sseid: SESSID,
                            oid: $scope.orderDetail.orderData.ordercode,
                            state: 6
                        };
                        HttpFactory.getData("/api/Order",params_index,"PATCH").then(function (result) {
                            $scope.orderDetail.orderData.state = "6";
                            $scope.popTipsShow("取消成功!");
                          
                        },function (err) {
                    
                        });
                    }
                }]
        
            });
        }
        function payment(item,e) {
            event.stopPropagation();
            var params = {};
            if (item.goods_data.length == 1){
                params = {
                    oid: item.ordercode
                };
            }
            if (item.goods_data.length > 1){
                params = {
                    moid: item.ordercode
                };
            }
            $scope.loadingShow();
            HttpFactory.getData("/api/memOrderListPay",params,"POST").then(function (result) {
                
                $scope.loadingOrPopTipsHide();
                if (result.status == 0){
                    result = result.parameters;
                    wx.chooseWXPay({
                        timestamp: result.timeStamp, // 支付签名时间戳，注意微信jssdk中的所有使用timestamp字段均为小写。但最新版的支付后台生成签名使用的timeStamp字段名需大写其中的S字符
                        nonceStr: result.nonceStr, // 支付签名随机串，不长于 32 位
                        package: result.package, // 统一支付接口返回的prepay_id参数值，提交格式如：prepay_id=***）
                        signType: 'MD5', // 签名方式，默认为'SHA1'，使用新版支付需传入'MD5'
                        paySign: result.paySign, // 支付签名
                        success: function (res) {
                            // 支付成功后的回调函数
                            if(res.errMsg == "chooseWXPay:ok"){
                                var params_index = {
                                    sseid: SESSID,
                                    oid: $scope.orderDetail.orderData.ordercode,
                                    state: 1
                                };
                                HttpFactory.getData("/api/Order",params_index,"PATCH").then(function (result) {
                                    $scope.orderDetail.orderData.state = "1";
                                    $scope.confirmObj.confirmOrderModalImg = 'images/confirmOrder.png';
                                    $scope.modal.show();
                                 
                                },function (err) {
                            
                                });
                        
                            }else {
                                $scope.popTipsShow("支付出错!");
                            }
                        }
                    });
                }else {
                    $scope.popTipsShow(result.desc);
                }
        
            },function (err) {
                $scope.popTipsShow("访问异常!");
            })
        }
        function refund(item,e) {
            e.stopPropagation();
            
            $ionicPopup.show({
                cssClass:'myOrder refund',
                template:'<p>申请退款</p><textarea id="applyMsg" ng-model="orderDetail.applyMsg" placeholder="请输入申请退款的原因？" maxlength="100"></textarea><div>{{orderDetail.applyMsg.length}}/100</div>',
                scope: $scope,
                buttons:[{
                    text:'取消',
                    onTap:function (e) {
                        var backView = angular.element(document.querySelector('.backdrop'));
                        backView.removeClass('visible');
                        backView.removeClass('active');
                        var body = angular.element(document.querySelector('.myBody'));
                        body.removeClass('popup-open');
                        e.stopPropagation();
                    }
                },{
                    text:'确定',
                    onTap:function () {
                        var backView = angular.element(document.querySelector('.backdrop'));
                        backView.removeClass('visible');
                        backView.removeClass('active');
                        var body = angular.element(document.querySelector('.myBody'));
                        body.removeClass('popup-open');
                        var params = {
                            sseid: SESSID,
                            oid: $scope.orderDetail.orderData.ordercode,
                            state: 4,
                            mess: $scope.orderDetail.applyMsg
                        };
                        HttpFactory.getData("/api/Order",params,"PATCH")
                            .then(function (result) {
                                if (result.status == 0){
                                    $scope.orderDetail.orderData.state = "4";
                                    $scope.popTipsShow("申请已提交!");
                                }else {
                                    $scope.popTipsShow(result.desc);
                                }
                                
                              
                            },function (error) {
                        
                            });
                    }
                }]
        
            });
        }
        function confirm(item,e) {
            e.stopPropagation();
            
            $ionicPopup.show({
                cssClass:'myOrder',
                template:'确认是否已收到货？',
                buttons:[{
                    text:'取消',
                    onTap:function () {
                        var backView = angular.element(document.querySelector('.backdrop'));
                        backView.removeClass('visible');
                        backView.removeClass('active');
                        var body = angular.element(document.querySelector('.myBody'));
                        body.removeClass('popup-open');
                        e.stopPropagation();
                    }
                },{
                    text:'确定',
                    onTap:function () {
                        var backView = angular.element(document.querySelector('.backdrop'));
                        backView.removeClass('visible');
                        backView.removeClass('active');
                        var body = angular.element(document.querySelector('.myBody'));
                        body.removeClass('popup-open');
                        var params_index = {
                            sseid: SESSID,
                            oid: $scope.orderDetail.orderData.ordercode,
                            state: 3
                        };
                        HttpFactory.getData("/api/Order",params_index,"PATCH").then(function (result) {
                            
                            if(result.status == 0){
                                item.state = "3";
                                $scope.popTipsShow("交易完成!");
                            }else {
                                $scope.popTipsShow(result.desc);
                            }
                    
                        },function (err) {
                    
                        });
                    }
                }]
        
            });
        }
        function appraise(item,e) {
            e.stopPropagation();
            
            $state.go('tabs.evaluatePage',{goodsMsg:item});
        }
        var assessMsg = CftStore.get("inputMsg");
        
        $scope.orderDetail.IconRootURL = IconROOT_URL;
        HttpFactory.getData("/api/Order", {sessid: SESSID,oid: $stateParams.oid}).then(function (result) {
            
            if (result.status == 0) {
                $scope.orderDetail.orderData = result.orderData;
                
            }
        },function (error) {
            console.log(error);
        });
        
    }]);
/**
 * Created by Administrator on 2016/12/14.
 */
angular.module('cftApp.payRecord',[])
    .config(['$stateProvider',function ($stateProvider) {
        $stateProvider.state('tabs.payRecord',{
            url:'/payRecord',
            views:{
                'tabs-personal':{
                    templateUrl:'payRecord.html',
                    controller:'payRecordController'
                }
            }
        });
    }]).controller('payRecordController',['$scope','$rootScope','HttpFactory',function ($scope,$rootScope,HttpFactory) {
    $scope.$on('$ionicView.beforeEnter', function () {
        $rootScope.hideTabs = true;
    });
    $scope.payRecordObj = {
        //下拉刷新
        doRefresh: doRefresh,
        //加载更多
        loadMore: loadMore,
        //是否加载更多
        moredata: false,
        //记录数据
        paylogOrderDatas: [],
        dataIsNull: 0,
        nullMsg: '',
        dragup: dragup,
        noMoreData: 0
    };
    function dragup() {
        if ($scope.payRecordObj.moredata && $scope.payRecordObj.paylogOrderDatas.length > 0){
            $scope.payRecordObj.noMoreData = 1;
        }
    }
    var currentPage = 1;
    var params = {
        total: perPageCount,
        page: currentPage,
        sessid: SESSID
    };
    
    // doRefresh();
    function doRefresh() {
        $scope.payRecordObj.noMoreData = 0;
        currentPage = 1;
        params.page = currentPage;
        $scope.payRecordObj.moredata = false;
        $scope.payRecordObj.paylogOrderDatas = [];
        
        HttpFactory.getData("/api/payOrderLog",params).then(function (result) {
            
            if (result.status == 0) {
                if (result.paylogOrderDatas.length == 0) {
                    $scope.payRecordObj.dataIsNull = 1;
                    $scope.payRecordObj.nullMsg = "您还没有交易记录O(∩_∩)O~";
                }else {
                    $scope.payRecordObj.dataIsNull = 0;
                    $scope.payRecordObj.nullMsg = ""
                }
                currentPage = 2;
                $scope.payRecordObj.paylogOrderDatas = result.paylogOrderDatas;
            }
            $scope.$broadcast('scroll.refreshComplete');
        },function (error) {
        
        });
    }
    function loadMore() {
        params.page = currentPage;
        HttpFactory.getData("/api/payOrderLog",params).then(function (result) {
            
            if (result.status == 0) {
                
                if (result.paylogOrderDatas.length < perPageCount) {
                    $scope.payRecordObj.moredata = true;
                    if (result.paylogOrderDatas.length == 0 && $scope.payRecordObj.paylogOrderDatas.length == 0){
                        $scope.payRecordObj.dataIsNull = 1;
                        $scope.payRecordObj.nullMsg = "您还没有交易记录O(∩_∩)O~";
                    }
                }else {
                    $scope.payRecordObj.moredata = false;
                    
                }
                currentPage ++;
                $scope.payRecordObj.paylogOrderDatas = $scope.payRecordObj.paylogOrderDatas.concat(result.paylogOrderDatas);
                
            }
            $scope.$broadcast('scroll.infiniteScrollComplete');
        },function (error) {

        });
    }
    
    
    
}]);
/**
 * Created by qingyun on 16/11/30.
 */
angular.module('cftApp.personal',[]).config(['$stateProvider',function ($stateProvider) {
    $stateProvider.state('tabs.personal',{
        url:'/personal',
        views:{
            'tabs-personal':{
                templateUrl:'personal.html',
                controller:'personalController'
            }
        }
    });
}]).controller('personalController',['$scope','$rootScope','$state','$ionicViewSwitcher','$ionicModal','MainData','HttpFactory','CftStore','$ionicSideMenuDelegate',function ($scope,$rootScope,$state,$ionicViewSwitcher,$ionicModal,MainData,HttpFactory,CftStore,$ionicSideMenuDelegate) {

    
    $scope.personal = {
        //用户名
        userName:localStorage.userName ,
        //用户头像
        headimgurl:localStorage.headimgurl,
        //用户积分
        creditNum:localStorage.creditNum ? localStorage.creditNum : 0,
        //进入订单
        showOrder:showOrder,
        //进入收藏
        showCollect:showCollect,
        //进入购物车
        showShoppingCar:showShoppingCar,
        //进入积分页面
        showCredit:showCredit,
        //进入收货地址页面
        showAddress:showAddress,
        //进入支付记录页面
        showPay:showPay,
        //进入关注页面
        showAttention:showAttention,
        //打开分享提示模态
        share:share
        
    };
    // var phpCookies = $cookies.get('PHPSESSID');
    
    $scope.$on('userInfo',function (event,data) {
        $scope.personal.userName = data.userName;
        $scope.personal.headimgurl = data.headimgurl;
        $scope.personal.creditNum = data.creditNum ? data.creditNum : 0;
    });
    $scope.$on('$ionicView.beforeEnter', function () {
        $rootScope.hideTabs = false;
        var params = {
            sessid: SESSID
        };
        CftStore.set('tabNum','0');
        // CftStore.set('personalInto','');
        // alert(SESSID);
        HttpFactory.getData("/api/memberInfo",params).then(function (result) {
            
            if (result.status == 0) {
                $scope.personal.creditNum = result.integral;
                CftStore.set('creditNum',result.integral);
            }
        },function (error) {
            console.log(error);
        })
    });
    //进入订单页面
    function showOrder() {
        $state.go('tabs.myOrder');
        CftStore.set('personalInto','yes');
        // $ionicViewSwitcher.nextDirection("forward");
    }
    //进入收藏页面
    function showCollect() {
        $state.go('tabs.collectionPager');
    }
    //进入积分页面
    function showCredit() {
        $state.go('tabs.totalScore');
    }
    //进入购物车页面
    function showShoppingCar() {
        $state.go('tabs.shoppingCart');
    }
    //进入收货地址
    function showAddress() {
        MainData.isFromPersonToReceiptAddress = true;
        $state.go('tabs.receiptAddress');
    }
    //进入支付页面
    function showPay() {
        $state.go('tabs.payRecord');
    }
    //进入关注页面
    function showAttention() {
    }

    $ionicModal.fromTemplateUrl('shareModal.html',{
        scope:$scope,
        animation:'fade-in'
    }).then(function (modal) {
        $scope.modal = modal;
    });
    $scope.$on('$ionicView.beforeLeave', function () {
        $scope.modal.hide();
    });
    $scope.$on('$ionicView.enter', function () {
        if ($ionicSideMenuDelegate.isOpen()){
            $ionicSideMenuDelegate.toggleRight();
        }
    });
    //分享
    function share() {
        $scope.modal.show();
    }
    $scope.$on('$destroy',function () {
        $scope.modal.remove();
    })
}]);
/**
 * Created by Administrator on 2016/12/7.
 */
angular.module('cftApp.receiptAddress',[])
    .config(['$stateProvider',function ($stateProvider) {
        $stateProvider.state('tabs.receiptAddress',{
            url:'/receiptAddress',
            // cache:false,
            views:{
                'tabs-personal':{
                    templateUrl:'receiptAddress.html',
                    controller:'receiptAddressController'
                }
            }
        }).state('tabs.receiptAddress_IG',{
            url:'/receiptAddress_IG',
            // cache:false,
            views:{
                'tabs-integralStore':{
                    templateUrl:'receiptAddress.html',
                    controller:'receiptAddressController'
                }
            }
        }).state('tabs.receiptAddress_home',{
            url:'/receiptAddress_home',
            // cache:false,
            views:{
                'tabs-homePage':{
                    templateUrl:'receiptAddress.html',
                    controller:'receiptAddressController'
                }
            }
        });
    }])
    .controller('receiptAddressController',['$scope','$location','$rootScope','$ionicPopup','$timeout','HttpFactory','$ionicModal','$http','MainData',function ($scope,$location,$rootScope, $ionicPopup,$timeout, HttpFactory, $ionicModal, $http,MainData) {
        //隐藏tabs-bar
        $scope.$on('$ionicView.beforeEnter', function () {
            $rootScope.hideTabs = true;
        });
        //用户完成模态地址选择的和加盟店类似的数据对象
        $scope.napaObj = {
            provinces:[]
        };
        $scope.napaStores = {
            text:''
        };
        //初始化收货地址数据
        $scope.addressObj = {
            moredata:false,
            //收货地址列表数据
            adreessListDatas: [],
            //编辑地址的地址对象
            addressModal: {
                vname: '',
                tel: '',
                province: '',
                city: '',
                address: '',
                code: ''
            },
            //地址列表为空的提示
            emptyPromptStr:'',
            //省份列表数据
            provinces: [],
            // 城市列表数据
            cities: [],
            //选中的省份
            selectedProvince: '',
            //选中的城市
            selectedCity: '',
            //选中发生变化
            selectedChange: selectedChange,
            //数据是否为空
            dataIsNull: false,
            //添加地址
            addAddress: addAddress,
            //取消添加
            cancelAdd: cancelAdd,
            //保存地址
            saveAddress: saveAddress,
            //关闭模态视图
            closeModal:closeModal,
            //打开模态视图
            openModal:openModal,
            //改变默认收货地址
            changeDefault:changeDefault,
            //删除地址的模态窗口
            showConfirm:showConfirm,
            //下拉刷新
            doRefresh: doRefresh,
            //加载更多
            loadMore: loadMore,
            //用户选择一个地址去使用
            selectAddress_user:selectAddress_user,
            selectAddressModalShow:selectAddressModalShow,//打开省份和失去的选择模态窗口
            noMoreData: 0,
            noMoreDataMsg: '',
            dragup: dragup
        };
        function dragup() {
            
            if($scope.addressObj.moredata && $scope.addressObj.adreessListDatas.length > 0) {
                $scope.addressObj.noMoreData = 1;
                $scope.addressObj.noMoreDataMsg = "没有更多收货地址...";
            }
        }
        
        var currentIndex = 1;
        var isEdit = false;
        $scope.addressOptionObj={};
        //读取本地城市列表
        $http.get('lib/city.json')
            .success(function (result) {
                $scope.addressObj.provinces = result;
                $scope.addressObj.provinces.splice(0,1);
                angular.forEach($scope.addressObj.provinces,function (province) {

                    //剔除数组的第一个元素
                    if (province.hasOwnProperty("sub"))
                        province.sub.splice(0,1);
                })
            });


        //实现单选的选择 设置默认地址
        function changeDefault(index,list,event) {
            if(index == undefined){
                index = 0;
                list = $scope.addressObj.adreessListDatas[0];
            }else {
                event.stopPropagation();
                //当用户只是设置默认地址的再回去的时候略过地址改变
                MainData.userSelectAddress = 'skip';
            }
            if ($scope.addressObj.adreessListDatas.length){
                for (var i = 0;i<$scope.addressObj.adreessListDatas.length;i++){
                    $scope.addressObj.adreessListDatas[i].setdefault = 0;
                }
                $scope.addressObj.adreessListDatas[index].setdefault = 1;
                var params = {
                    id: list.id,

                    setdefault: 1,
                    sessid:SESSID
                };

                HttpFactory.getData("/api/uAddress",params,"PATCH").then(function (result) {
                    if (result.status == 0) {
                        //将默认地址存入本地，供确认订单使用
                    }
                },function (err) {

                });
            }


        }
        //选择省份改变的方法
        function selectedChange(selectedProvince) {

            angular.forEach($scope.addressObj.provinces,function (province) {

                //剔除数组的第一个元素
                if (province.name == selectedProvince)
                {
                    $scope.addressObj.cities = province.sub;
                }
            })
        }
        //保存收货地址
        function saveAddress(addressParams) {
            $scope.loadingShow();
            addressParams.province = $scope.addressObj.selectedProvince;
            addressParams.city = $scope.addressObj.selectedCity;
            addressParams.sessid = SESSID;
            if (addressParams.province == "请选择"){
                addressParams.province = '';
            }
            if (addressParams.city == "请选择"){
                addressParams.city = '';
            }
            if (addressParams.vname == '' ||
                addressParams.tel == '' ||
                addressParams.address == ''){
                $scope.popTipsShow("请补全地址信息");
                return;
            }
            if (!(/^1(3|4|5|7|8)\d{9}$/.test(addressParams.tel))) {
                $scope.popTipsShow("手机号输入有误");
                return;

            }
            $scope.addressObj.closeModal();
            if (isEdit) {
                // delete addressParams.setdefault;
                var thisSetdefault = addressParams.setdefault;
                addressParams.setdefault = '';

                HttpFactory.getData("/api/uAddress",addressParams,"PATCH")
                    .then(function (result) {
                        $rootScope.hideTabs = true;
                        if (result.status == "0"){
                            
                            addressParams.setdefault = thisSetdefault;
                            MainData.userSelectAddress = addressParams;
                            $scope.loadingOrPopTipsHide();
                            //成功提示
                            $scope.popTipsShow("地址修改成功");

                        }else {
                            //错误提示
                            $scope.loadingOrPopTipsHide();
                            $scope.popTipsShow("地址修改失败");
                        }
                    },function (err) {

                    });
            }else {

                $scope.addressObj.dataIsNull = false;

                HttpFactory.getData("/api/uAddress",addressParams,"POST")
                    .then(function (result) {
                        if (result.status == "0"){
                            $rootScope.hideTabs = true;
                            doRefresh('新增保存');
                        }else {
                            //错误提示
                            $scope.loadingOrPopTipsHide();
                            $scope.popTipsShow("地址保存失败");

                        }
                    },function (err) {

                    });
            }

        }

        //点击新增地址打开模态窗口
        function addAddress() {
            $scope.openModal();
        }
        

        //用户在购买时选择一个地址去使用
        function selectAddress_user(index) {
            if (!MainData.isFromPersonToReceiptAddress){
                MainData.userSelectAddress = $scope.addressObj.adreessListDatas[index];
                window.history.back();
            }
        }

        //下拉刷新
        function doRefresh(str) {
            currentIndex = 1;
            $scope.addressObj.noMoreData = 0;
            $scope.addressObj.noMoreDataMsg = "";
            var getData = {
                success: function (result) {
                    if (result.status == 0) {
                        if (result.addressData.length != 0){

                            $scope.addressObj.adreessListDatas = result.addressData;
                            $scope.addressObj.dataIsNull = false;
                            if(result.addressData.length >= 10){
                                $scope.addressObj.moredata = false;
                            }else {
                                $scope.addressObj.moredata = true;
                            }
                            if (str == "新增保存"){
                                $scope.loadingOrPopTipsHide();
                                $scope.popTipsShow("地址保存成功");
                                if ($scope.addressObj.adreessListDatas.length == 1)
                                {
                                    $scope.addressObj.adreessListDatas[0].setdefault = 1;
                                    changeDefault();
                                }
                            }

                        } else {//没有地址，页面提示
                            $scope.addressObj.dataIsNull = true;
                        }
                        currentIndex++;
                    }
                    $scope.$broadcast('scroll.refreshComplete');
                },
                error: function (err) {

                }
            };

            var params = {
                page: currentIndex,
                sessid:SESSID
            };
            HttpFactory.getData("/api/uAddress",params,"GET")
                .then(
                    getData.success,
                    getData.error
                );
        }
        //上拉加载
        function loadMore() {
            var params = {
                page: currentIndex,
                sessid:SESSID
            };
            HttpFactory.getData("/api/uAddress",params,"GET").then(function (result) {
                
                if (result.status == 0) {
                    
                    if(result.addressData.length == 0 && $scope.addressObj.adreessListDatas.length == 0){
                        $scope.addressObj.emptyPromptStr = "您的收货地址为空\(^o^)/~";
                        $scope.addressObj.dataIsNull = true;

                    }else {
                        $scope.addressObj.dataIsNull = false;
                    }
                    if (result.addressData.length < perPageCount){
                        $scope.addressObj.moredata = true;
                    }else {
                        $scope.addressObj.moredata = false;
                    }
                    $scope.addressObj.adreessListDatas = $scope.addressObj.adreessListDatas.concat(result.addressData);
                    currentIndex ++;
                    $scope.$broadcast('scroll.infiniteScrollComplete');
                }else {

                }
            },function (err) {
                $scope.addressObj.moredata = true;
                $scope.popTipsShow("访问异常!");
            });
        }


        //打开模态
        function openModal(option,list,event) {
            if(option == 'edit'){
                isEdit = true;
                event.stopPropagation();
                $scope.addressOptionObj = list;
                $scope.addressObj.selectedProvince = list.province;
                angular.forEach($scope.addressObj.provinces,function (province) {
                    //剔除数组的第一个元素
                    if (province.name == list.province)
                    {
                        $scope.addressObj.cities = province.sub;
                    }
                });
                $scope.addressObj.selectedCity = list.city;
                $scope.addressOptionObj.title="编辑收货地址";
            }else{
                isEdit = false;
                $scope.addressObj.selectedProvince = '';
                $scope.addressObj.selectedCity = '';
                $scope.addressObj.cities = [];
                $scope.addressOptionObj = {
                    vname: '',
                    tel: '',
                    province: '',
                    city: '',
                    address: '',
                    code: ''
                };
                $scope.addressOptionObj.title = "新增收货地址"
            }
            $scope.addressModal.show();
        }


        function showConfirm(index,list,event) {
            event.stopPropagation();
            var myPopup = $ionicPopup.show({
                cssClass:'myOrder deleteAddress',
                template:'确认要删除该地址吗？',
                scope: $scope,
                buttons: [
                    {   text: '取消',
                        type: '',
                        onTap:function (e) {
                            var backView = angular.element(document.querySelector('.backdrop'));
                            backView.removeClass('visible');
                            backView.removeClass('active');
                            var body = angular.element(document.querySelector('.myBody'));
                            body.removeClass('popup-open');
                            e.stopPropagation();
                        }
                    },
                    {
                        text: '确定',
                        type: '',
                        onTap: function(e) {
                            var backView = angular.element(document.querySelector('.backdrop'));
                            backView.removeClass('visible');
                            backView.removeClass('active');
                            var body = angular.element(document.querySelector('.myBody'));
                            body.removeClass('popup-open');
                            var id= list.id;
                            //删除收货地址的网络请求
                            if ($scope.addressObj.adreessListDatas.length == 0){
                                $scope.addressObj.dataIsNull = true;
                            }else {
                                $scope.addressObj.dataIsNull = false;
                            }
                            HttpFactory.getData("/api/uAddress",{id:id,sessid:SESSID},"DELETE")
                                .then(function (result) {
                                    if (result['status'] == '0'){
                                        //当用户删除的地址就是用户以前选择的地址的时候继续获取默认的
                                        MainData.userSelectAddress = 'continue';
                                        $scope.popTipsShow("删除成功");
                                        //当删除的地址为默认地址的时候 重置成第一个为默认地址
                                        if ($scope.addressObj.adreessListDatas.length && $scope.addressObj.adreessListDatas[index].setdefault == 1){
                                            $scope.addressObj.adreessListDatas.splice(index ,1);
                                            //设置第一个为默认地址
                                            changeDefault();                                        }else {
                                            $scope.addressObj.adreessListDatas.splice(index ,1);
                                        }

                                    }else {
                                        //错误提示
                                        $scope.popTipsShow(result.desc);
                                    }
                                },function (err) {

                                });
                        }
                    }
                ]
            });
        }
        //引入外部的编辑地址模态
        $ionicModal.fromTemplateUrl('editAddressModal.html', {
            scope: $scope,
            animation: 'slide-in-up'
        }).then(function(modal) {
            $scope.addressModal = modal;
        });
        $ionicModal.fromTemplateUrl('choiceAddressModal.html',{
            scope:$scope,
            animation: 'fade-out',
            focusFirstInput:true,
            backdropClickToClose:true
        }).then(function(modal) {
            $scope.selectModal = modal;
        });
        //关闭模态
        function cancelAdd() {
            $scope.addressModal.hide();
        }
        function closeModal() {
            $scope.addressModal.hide();
        }
        $scope.doSome = function () {
            
        };
        //当销毁controller时会清除模态modal
        $scope.$on('$destroy', function() {
            MainData.isFromPersonToReceiptAddress = false;
            $scope.addressModal.remove();
            $scope.selectModal.remove();
            $scope.loadingOrPopTipsHide();

        });
        var selectType = '省份';
        //关闭selectModal 命名为$scope.modal.show()是为了配合加盟店的模态
        $scope.modal = {
            hide:function () {
                if (selectType == "省份"){
                    $scope.addressObj.selectedProvince = $scope.napaStores.text;
                    $scope.addressObj.selectedCity = '';
                }
                if (selectType == "城市"){
                    $scope.addressObj.selectedCity = $scope.napaStores.text;
                }
                $scope.selectModal.hide();
            }
        };
        //打开省份和失去的选择模态窗口
        function selectAddressModalShow(str) {
            selectType = str;
            if (str == "省份"){
                $scope.napaObj.provinces = $scope.addressObj.provinces;
            }
            if(selectType == "城市"){
                selectedChange($scope.addressObj.selectedProvince);
                $scope.napaObj.provinces = $scope.addressObj.cities;
            }
            $scope.selectModal.show();
        }
    }]);
/**
 * Created by zaq on 17/1/23.
 */
angular.module('cftApp.scanCodePayment',[])
    .config(['$stateProvider',function ($stateProvider) {
        $stateProvider.state('tabs.scanCodePayment',{
            url:'/scanCodePayment',
            views:{
                'tabs-napaStores':{
                    templateUrl:'scanCodePayment.html',
                    controller:'scanCodePaymentController'
                }
            }
        });
    }]).controller('scanCodePaymentController',['$scope','$rootScope','$ionicPopup','$timeout','$ionicModal','HttpFactory','$stateParams',function ($scope,$rootScope,$ionicPopup,$timeout, $ionicModal,HttpFactory,$stateParams) {
        
    //隐藏tabs-bar
    $scope.$on('$ionicView.beforeEnter', function () {
        $rootScope.hideTabs = true;
    });
    $scope.scanCodeObj = {
        price:null,
        confirmPayment:confirmPayment

    };
    function confirmPayment() {
        if ($scope.scanCodeObj.price == 0 || !$scope.scanCodeObj.price){
           $scope.popTipsShow("请输入您要付款的金额!");
            return;
        }
        $scope.loadingShow();
        HttpFactory.getData('/api/wxFranchise',{sessid:SESSID,franchise_id:3,price:$scope.scanCodeObj.price},'POST').then(function (result) {
            
            $scope.loadingOrPopTipsHide();
            if (result.status == 0){
                result = result.parameters;
                wx.chooseWXPay({
                    timestamp: result.timeStamp, // 支付签名时间戳，注意微信jssdk中的所有使用timestamp字段均为小写。但最新版的支付后台生成签名使用的timeStamp字段名需大写其中的S字符
                    nonceStr: result.nonceStr, // 支付签名随机串，不长于 32 位
                    package: result.package, // 统一支付接口返回的prepay_id参数值，提交格式如：prepay_id=***）
                    signType: 'MD5', // 签名方式，默认为'SHA1'，使用新版支付需传入'MD5'
                    paySign: result.paySign, // 支付签名
                    success: function (res) {
                        // 支付成功后的回调函数
                        if(res.errMsg == "chooseWXPay:ok"){

                        }else {
                            $scope.popTipsShow("支付出错!");
                        }
                    }
                });
            }else {
                $scope.popTipsShow(result.desc);
            }
        },function (err) {

        })
    }



}]);
/**
 * Created by lx on 2016/12/9.
 */
angular.module('cftApp.shoppingCart',['ionic']).config(['$stateProvider',function ($stateProvider) {
    $stateProvider.state('tabs.shoppingCart',{
        url:'/shoppingCart',
        views:{
            'tabs-personal':{
                templateUrl:'shoppingCart.html',
                controller:'shoppingCartController'
            }
        }
    });
    $stateProvider.state('tabs.shoppingCart_fromDetail',{
        url:'/shoppingCart_fromDetail',
        views:{
            'tabs-homePage':{
                templateUrl:'shoppingCart.html',
                controller:'shoppingCartController'
            }
        }
    });
}]).controller('shoppingCartController',['$scope','$rootScope','$state','$ionicPopup','$timeout','$ionicViewSwitcher','HttpFactory','MainData','CftStore',function ($scope,$rootScope,$state,$ionicPopup,$timeout,$ionicViewSwitcher,HttpFactory,MainData,CftStore) {
    $scope.$on('$ionicView.beforeEnter', function () {
        $rootScope.hideTabs = true;
        CftStore.set('confirmGoodsArr','');
    });

    // 收到数据
    $scope.shoppingCart = {
        //购物车列表
        CartList : [] ,
        //购物车总金额
        CartMoney : 0 ,
        //购物车数量
        CartCount : 0 ,
        //是否停止加载更多
        isShowInfinite : true ,
        //购物车为空的提示
        emptyShopCarStr : "",
        //控制全选按钮红点 刚进去的时候默认不全选
        SelectAll:true,
        //上拉加载
        loadMore:loadMore ,
        //下拉刷新
        doRefresh:doRefresh,
        //确认删除
        confirmDelete:confirmDelete,
        //全选按钮判断
        ifSelectAll:ifSelectAll,
        //选择购物车商品并计算总金额 判断全选按钮是否显示
        ifSelect:ifSelect,
        //选中的商品数组
        selectedArray:[],
        //去结算的方法
        goToSettlement:goToSettlement,
        noneOfMoreData: false,
        noneMsg: '',
        drapup: drapup
    };
    function drapup() {
        
        if (!$scope.shoppingCart.isShowInfinite){
            $scope.shoppingCart.noneOfMoreData = true;
            $scope.shoppingCart.noneMsg = "没有更多商品..."
        }
        
    }
    var more = 1;

    // 上拉加载函数
    function loadMore() {
        
        var shoppingCartUrl = '/api/ushoppingCart';
        var params = {
            page:more,
            sessid:SESSID
        };
        $timeout(function () {
            HttpFactory.getData(shoppingCartUrl,params).then(function (result) {
                
                console.log(result);
                if (result.shoppingCart.length < 10){
                    if (result.shoppingCart.length == 0){
                        $scope.shoppingCart.noneOfMoreData = false;
                        $scope.shoppingCart.noneMsg = "";
                        $scope.shoppingCart.emptyShopCarStr = "您的购物车是空的O(∩_∩)O~";
                    }
                    else{
                        
                    }
                    $scope.shoppingCart.isShowInfinite = false;
                }else {
                    $scope.shoppingCart.isShowInfinite = true;
                    $scope.shoppingCart.noneOfMoreData = false;
                }
                $scope.shoppingCart.CartList = $scope.shoppingCart.CartList.concat(result.shoppingCart);
                
                $scope.$broadcast('scroll.infiniteScrollComplete');
                more++;
            },function (err) {
                $scope.shoppingCart.isShowInfinite = false;
                $scope.popTipsShow('获取数据失败');
            });
        },300);
    }

    // 下拉刷新函数
    function doRefresh() {
        
        more = 1;
        var shoppingCartUrl = '/api/ushoppingCart';
        var params = {
            page:more,
            sessid:SESSID
        };
        $scope.shoppingCart.noneOfMoreData = false;
        // 没有更多商品...
        $scope.shoppingCart.noneMsg = "";
        HttpFactory.getData(shoppingCartUrl,params)
            .then(function (result) {
                
                if (result.shoppingCart.length >= 10){
                    $scope.shoppingCart.noneOfMoreData = false;
                    $scope.shoppingCart.isShowInfinite = true;
                    $scope.shoppingCart.noneMsg = ""
                }else {
                    if (result.shoppingCart.length == 0){
                        $scope.shoppingCart.noneMsg = "";
                        $scope.shoppingCart.noneOfMoreData = false;
                        $scope.shoppingCart.emptyShopCarStr = "您的购物车是空的O(∩_∩)O~";
                    }else {
                        
                    }
                    
                }
                $scope.shoppingCart.CartList = result.shoppingCart;
                // goodsIfOutData();
                more++;
            }).finally(function () {
            $scope.$broadcast('scroll.refreshComplete');
        });
        $scope.shoppingCart.SelectAll = true;
        $scope.shoppingCart.CartMoney = 0;
        $scope.shoppingCart.CartCount = 0;

    }


    // 判断是否失效
    // function goodsIfOutData() {
    //     var nowTime = new Date();
    //     console.log(nowTime)
    //     for (var s = 0;s<$scope.shoppingCart.CartList.length;s++){
    //         // console.log(((nowTime / 1000) - (parseFloat($scope.shoppingCart.CartList[s].addTime))) / (60));
    //         if (((nowTime / 1000) - (parseFloat($scope.shoppingCart.CartList[s].addTime))) / (60) > 5 ){
    //             $scope.shoppingCart.CartList[s].isHave = false;
    //         }else {
    //             $scope.shoppingCart.CartList[s].isHave = true;
    //         }
    //     }
    // }

    // 计算总价格和总数量
    function shoppingCartallMoney() {
        var shoppingMoney = 0;
        var shoppingCartCount = 0;
        // 选中所有的label标签里的input标签
        var shoppingCheckbox = document.querySelectorAll('.radio>input');
        
        for (var p = 0; p < shoppingCheckbox.length; p++) {
            // shoppingCheckbox[p].checked == true
            if (shoppingCheckbox[p].checked == true && $scope.shoppingCart.CartList[p].is_Have == 1){
                shoppingMoney += $scope.shoppingCart.CartList[p].price * $scope.shoppingCart.CartList[p].num;
                shoppingCartCount += Number($scope.shoppingCart.CartList[p].num);
            }
            
        }
        $scope.shoppingCart.CartMoney = shoppingMoney;
        $scope.shoppingCart.CartCount = shoppingCartCount;
    }



    // 全选按钮判断
    function ifSelectAll() {
        $scope.shoppingCart.SelectAll = !$scope.shoppingCart.SelectAll;
        // 选中所有的label标签里的input标签
        var shoppingCheckbox = angular.element(document.querySelectorAll('.radio>input'));
        if (!$scope.shoppingCart.SelectAll) {
            shoppingCheckbox.attr('checked','true');
            for (var i = 0; i < $scope.shoppingCart.CartList.length; i ++) {
                if ($scope.shoppingCart.CartList[i].is_Have == 1 && $scope.shoppingCart.CartList[i].is_on_sale == 1){
                    $scope.shoppingCart.selectedArray.push($scope.shoppingCart.CartList[i]);
                }
            }
        }
        // 如果取消全选的话让所有商品都取消选中
        else{
            $scope.shoppingCart.selectedArray = [];
            shoppingCheckbox.attr('checked','');
        }
        //这里数组不能直接赋值，会存在浅拷贝的问题
        
        console.log("选中所有");
        console.log($scope.shoppingCart.selectedArray);
        shoppingCartallMoney();
    }
    // 选择购物车商品并计算总金额 判断全选按钮是否显示
    function ifSelect(index) {
        //计算商品数量和总商品金额
        shoppingCartallMoney();
        // ？？？
        // $scope.shoppingCart.SelectAll = true;
        // 判断当所有商品都选中时全选按钮也要被选中
        var shoppingCheckbox = document.querySelectorAll('.radio>input');
        var ifArray = [];
        for (var q = 0;q < shoppingCheckbox.length;q++){
            ifArray = ifArray.concat(shoppingCheckbox[q].checked);
        }
        
        var arr;
        for(var o = 0;o < ifArray.length;o++){
            arr += ifArray[o]+'';
        }
        arr.indexOf('false');
        $scope.shoppingCart.SelectAll = arr.indexOf('false') > 0;
        
        var t_index = 'a';
        for(var i = 0;i < $scope.shoppingCart.selectedArray.length;i++){
            if ($scope.shoppingCart.CartList[index].$$hashKey == $scope.shoppingCart.selectedArray[i].$$hashKey){
                t_index = i;
                $scope.shoppingCart.selectedArray.splice(i,1);
                
                break;
            }
        }
        console.log("不知道要干啥");
        console.log($scope.shoppingCart.selectedArray);
        if (t_index == 'a'){
            $scope.shoppingCart.selectedArray.push($scope.shoppingCart.CartList[index])
        }
        console.log($scope.shoppingCart.selectedArray);
    }

    //去结算的方法
    function goToSettlement() {
        console.log($scope.shoppingCart.selectedArray);
        if($scope.shoppingCart.selectedArray.length == 0){
            $scope.popTipsShow("您未选择任何商品!");
            return;
        }
        for (var b = 0;b < $scope.shoppingCart.selectedArray.length;b++){
            $scope.shoppingCart.selectedArray[b].goodsNum = $scope.shoppingCart.selectedArray[b].num;
            $scope.shoppingCart.selectedArray[b].is_integral = 0;
            $scope.shoppingCart.selectedArray[b].goods_id = $scope.shoppingCart.selectedArray[b].g_id;

        }
        
        MainData.shopping_car_goodsArray = JSON.stringify($scope.shoppingCart.selectedArray);
        if ($state.current.name == 'tabs.shoppingCart_fromDetail'){
            
            $state.go("tabs.confirmOrder",{goodsArray:'value传值'});

        }else {
            CftStore.set('confirmGoodsArr',JSON.stringify($scope.shoppingCart.selectedArray));
            $state.go("tabs.confirmOrder_personal",{goodsArray:'value传值'});

        }
    }

    // 前往商品详情
    $scope.lookGoodDetail = function (index,goodsData) {
        
        if (goodsData.is_on_sale != 1){
            $scope.popTipsShow("商品已下架");
            return;
        }
        CftStore.set('goodsName',goodsData.title);
        
        if ($state.current.name == 'tabs.shoppingCart_fromDetail'){
            $state.go('tabs.goodsDetail',{is_integral: '0', goods_id:  $scope.shoppingCart.CartList[index].g_id,goods_icon: $scope.shoppingCart.CartList[index].litpic});
            $ionicViewSwitcher.nextDirection('forward');
        }else {
            $state.go('tabs.goodsDetail_collection',{is_integral: '0', goods_id:  $scope.shoppingCart.CartList[index].g_id,goods_icon: $scope.shoppingCart.CartList[index].litpic});
            $ionicViewSwitcher.nextDirection('forward');
        }
    };

    // 删除商品
    function confirmDelete (index) {
        
        $ionicPopup.show({
            cssClass:'myOrder',
            template:'确认要删除该商品吗？',
            buttons:[{
                text:'取消',
                type: 'button-clear button-dark',
                onTap:function (e) {
                    var backView = angular.element(document.querySelector('.backdrop'));
                    backView.removeClass('visible');
                    backView.removeClass('active');
                    var body = angular.element(document.querySelector('.myBody'));
                    body.removeClass('popup-open');
                    e.stopPropagation();
                }
            },{
                text:'确定',
                type: 'button-clear button-assertive',
                onTap:function (e) {
                    var backView = angular.element(document.querySelector('.backdrop'));
                    backView.removeClass('visible');
                    backView.removeClass('active');
                    var body = angular.element(document.querySelector('.myBody'));
                    body.removeClass('popup-open');
                    // return;
                    var params = {
                        id: $scope.shoppingCart.CartList[index].id,
                        sessid:SESSID
                    };
                    HttpFactory.getData("/api/ushoppingCart",params,"DELETE").then(function (result) {
                        
                        if (result.status == 0) {
                            var shoppingCheckbox = document.querySelectorAll('.radio>input');
                            
                            if (shoppingCheckbox[index] && shoppingCheckbox[index].checked){
                                shoppingCheckbox[index].checked = '';
                            }
                            shoppingCartallMoney();
                            $scope.shoppingCart.CartList.splice(index,1);
                            $scope.popTipsShow("删除成功");
                        }else {
                            $scope.popTipsShow("删除失败");
                        }
                    },function (err) {
                        
                        $scope.popTipsShow("获取数据失败");
                    });

                }
            }]
        });
    }
    
}]);
/**
 * Created by chaoshen on 2016/12/18.
 */
angular.module('cftApp.sortedGoods',[])
    .config(['$stateProvider',function ($stateProvider) {
        $stateProvider.state('tabs.sortedGoods',{
            url: '/sortedGoods/:searchStr/:cate_id',
            views: {
                'tabs-homePage': {
                    templateUrl: 'sortedGoods.html',
                    controller: "sortedGoodsController"
                }
            }

        })
    }])
    .controller('sortedGoodsController',['$scope','$stateParams','$state','$ionicSideMenuDelegate','HttpFactory','$ionicViewSwitcher','$ionicLoading','$ionicScrollDelegate','$ionicModal','$rootScope',function ($scope,$stateParams,$state,$ionicSideMenuDelegate,HttpFactory,$ionicViewSwitcher,$ionicLoading,$ionicScrollDelegate,$ionicModal,$rootScope) {
        
        var sortedGoodsObj = $scope.sortedGoodsObj = {
            //记录价格排序点击次数
            priceTapNums : 0,
            //价格由高到低排序
            isPriceHeigh : true,
            hotTapNums : 0,
            isHotDesc: true,
            //是否取消加载动画
            moredata: false,
            //判断是否还有更多数据
            noneOfMoreData: false,
            dataIsNull: 0,
            nullMsg: '',
            //排序关键字
            sortWords : '',
            cateData: null,
            //商品数据
            goodsDatas : [],
            //进入详情页
            goDetail : goDetail,
            //点击购物车
            takeShopping : takeShopping,
            //排序方式
            sortAction : sortAction,
            //搜索商品
            goSearch : goSearch,
            //刷新
            doRefresh: doRefresh,
            //加载更多
            loadMore: loadMore,
            //当前页面
            currentPage: 1,
            // dataIsNull: 1,
            arrowImg : "images/shangArrow.png",
            hotImgeName: "xiaoArrow.png",
            noneMsg: '',
            dragup: dragup
            
        };
        function dragup() {
            if (sortedGoodsObj.moredata && sortedGoodsObj.goodsDatas.length > 0){
                sortedGoodsObj.noneOfMoreData = true;
                sortedGoodsObj.noneMsg = "没有更多商品..."
            }
        }
        
        $scope.$on('$ionicView.beforeEnter', function () {
            $rootScope.hideTabs = true;
        });
        // $scope.$on('$ionicView.enter', function () {
        //     if ($ionicSideMenuDelegate.isOpen()){
        //         $ionicSideMenuDelegate.toggleRight();
        //     }
        // });
        //作为整个页面的参数对象使用，利于刷新时的统一
        var currentPage = 1;
        var params = {
            integral: 0,
            total : perPageCount,
            page : currentPage,
            searchStr : $stateParams.searchStr,
            "cate_id[]" : $stateParams.cate_id == '' ? '' : $stateParams.cate_id.split(',')
        };
        
        //下拉刷新
        function doRefresh() {
            if (params.searchStr == undefined){
                params.searchStr = "";
            }
            sortedGoodsObj.noneOfMoreData = false;
            sortedGoodsObj.noneMsg = "";
            currentPage = 1;
            params.page = currentPage;
            sortedGoodsObj.moredata = false;
            var getData = {
                success: function (result) {
                    
                    if (result.status == 0){
                        // $scope.sortedGoodsObj.cateData = result.cateData;
                        if(result["goodsData"].length < perPageCount){
                            sortedGoodsObj.moredata = true;
                            if (result["goodsData"].length == 0 && sortedGoodsObj.goodsDatas.length == 0) {
                                $scope.sortedGoodsObj.nullMsg = "没有此类商品哦O(∩_∩)O~";
                                $scope.sortedGoodsObj.dataIsNull = 1;
                            }else {
                                $scope.sortedGoodsObj.nullMsg = "";
                                $scope.sortedGoodsObj.dataIsNull = 0;
                            }
                        }else {
                            sortedGoodsObj.moredata = false;
                        }
                        
                        sortedGoodsObj.goodsDatas = result["goodsData"];
                        currentPage++;
                    }else {
                    }
    
                    $scope.$broadcast('scroll.refreshComplete');
    
                },
                error: function (err) {
                    
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
            
            params.page = currentPage;
            if (currentPage == 1){
                setTimeout(function () {
                    var loadMoreData = {
                        success: function (result) {
                            if (result.status == 0) {
                                
                                $scope.sortedGoodsObj.cateData = result.cateData;
                                if (result["goodsData"].length < perPageCount){
                                    if (result["goodsData"].length == 0 && sortedGoodsObj.goodsDatas.length == 0){
                                        // sortedGoodsObj.noneOfMoreData = false;
                                        // sortedGoodsObj.nullMsg = "";
                                        $scope.sortedGoodsObj.dataIsNull = 1;
                                        $scope.sortedGoodsObj.nullMsg = "没有此类商品哦O(∩_∩)O~";
                                    }else {
                                        $scope.sortedGoodsObj.dataIsNull = 0;
                                        $scope.sortedGoodsObj.nullMsg = "";
                                    }
                                    sortedGoodsObj.moredata = true;
                                }else {
                                    
                                    sortedGoodsObj.moredata = false;
                                }
                                sortedGoodsObj.goodsDatas = [];
                                sortedGoodsObj.goodsDatas = sortedGoodsObj.goodsDatas.concat(result["goodsData"]);
                                currentPage ++;
                                // params.page = sortedGoodsObj.currentPage;
                            }else {
                                
                            }
                            $scope.$broadcast('scroll.infiniteScrollComplete');
                        },
                        error: function (err) {
                            
                        }
                    };
                    
                    HttpFactory.getData("/api/getGoods",params)
                        .then(
                            loadMoreData.success,
                            loadMoreData.error);
                },300);
                return;
            }else {
                
                params.page = currentPage;
                HttpFactory.getData("/api/getGoods",params)
                    .then(function (result) {
                        
                        if (result.status == 0) {
                            if (result["goodsData"].length < perPageCount){
                                sortedGoodsObj.moredata = true;
                                // sortedGoodsObj.noneOfMoreData = true;
                                // sortedGoodsObj.noneMsg = "没有更多商品";
                            }else {
                                sortedGoodsObj.moredata = false;
                                // sortedGoodsObj.noneOfMoreData = false;
                                // sortedGoodsObj.noneMsg = "";
                            }
                            sortedGoodsObj.goodsDatas = sortedGoodsObj.goodsDatas.concat(result["goodsData"]);
                            currentPage ++;
                        }else {
                
                        }
                        $scope.$broadcast('scroll.infiniteScrollComplete');
                    },function (err) {
            
                    })
            }
            
        }
        
        //进入商品详情页
        function goDetail(item) {
            $state.go('tabs.goodsDetail',{is_integral: 0,goods_id: item.goods_id});
        }
        //当前页搜索
        function goSearch(searchStr) {
            
            if (searchStr == undefined){
                searchStr = "";
                
            }
            params["cate_id[]"] = '';
            params.startPrice = '';
            params.endPrice = '';
            params.searchStr = searchStr;
            $scope.sideMenuObj.isSearch  = true;
            sortedGoodsObj.goodsDatas = [];
            doRefresh();
        }

        //点击购物车打开模态
        function takeShopping($event,item) {
            $event.stopPropagation();
            $scope.openModal();
            
            $scope.modal.goodsData = item;
            $scope.modal.IconRootURL = IconROOT_URL;
        }
        //模态窗口的立即购买
        $scope.goToConfirmOrder = function () {
            $scope.modal.goodsData.goodsNum = $scope.collect.val;
            $scope.modal.hide();
            $state.go("tabs.confirmOrder", {goodsArray: JSON.stringify([$scope.modal.goodsData])})
        };
        //购物车模态窗口相关操作
        $scope.collect = {
            val : 1,
            reduce:function () {
                if($scope.collect.val > 1){
                    $scope.collect.val--;
                }
            },
            add:function () {
                if($scope.collect.val > $scope.modal.goodsData.goods_number){
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
                    $scope.user_Car_Num = user_car_num;
                    $scope.modal.hide();
                    $scope.popTipsShow("加入购物车成功");
                }else {
                    $scope.popTipsShow("加入购物车失败");
                }
            },function (err) {
               
            });
        };
        //排序请求
        function requestSorted(paramsObj) {
            
            sortedGoodsObj.moredata = false;
            currentPage = 1;
            paramsObj.page = currentPage;
            $ionicScrollDelegate.scrollTop();
            $ionicScrollDelegate.resize();
            
        }
        //所有的排序行为
        function sortAction(event) {
            $scope.loadingShow();
            $scope.loadingOrPopTipsHide();
            var actions = angular.element(event.currentTarget).children();
            var target = angular.element(event.target);
            sortedGoodsObj.noneOfMoreData = false;
            sortedGoodsObj.noneMsg = "";
            sortedGoodsObj.moredata = false;
            switch (target.text()){
                case "综合":
                    {
                        $scope.sortedGoodsObj.dataIsNull = 0;
                        descSorted("coll");
                    }break;

                case "销量":
                    {
                        $scope.sortedGoodsObj.dataIsNull = 0;
                        // if (target.hasClass("active")){
                        //     $scope.sortedGoodsObj.hotTapNums ++;
                        //     if ($scope.sortedGoodsObj.hotTapNums > 0){
                        //         $scope.sortedGoodsObj.isHotDesc = !$scope.sortedGoodsObj.isHotDesc;
                        //
                        //         if ($scope.sortedGoodsObj.isHotDesc){
                        //             $scope.sortedGoodsObj.hotImgeName = "xiaoArrow.png";
                        //
                        descSorted("hot");
                                    
                        //         }else {
                        //             $scope.sortedGoodsObj.hotImgeName = "shangArrow.png";
                        //             ascSorted("hot");
                        //
                        //         }
                        //     }
                        // }else {
                        //     //初次点击价格按钮时
                        //     $scope.sortedGoodsObj.hotTapNums = 0;
                        //     if ($scope.sortedGoodsObj.isHotDesc){
                        //         $scope.sortedGoodsObj.hotImgeName = "xiaoArrow.png";
                        //         descSorted("hot");
                        //     }else {
                        //         $scope.sortedGoodsObj.hotImgeName = "shangArrow.png";
                        //         ascSorted("hot");
                        //
                        //     }
                        // }
                    }break;

                case "价格":
                    {
    
                        $scope.sortedGoodsObj.dataIsNull = 0;
                        if (target.hasClass("active")){
                            $scope.sortedGoodsObj.priceTapNums ++;
                            if ($scope.sortedGoodsObj.priceTapNums > 0){
                                $scope.sortedGoodsObj.isPriceHeigh = !$scope.sortedGoodsObj.isPriceHeigh;
                                
                                if (!$scope.sortedGoodsObj.isPriceHeigh){
                                    descSorted("shop_price");
                                    $scope.sortedGoodsObj.arrowImg = "images/xiaoArrow.png"
                                }else {
                                    
                                    ascSorted("shop_price");
                                    $scope.sortedGoodsObj.arrowImg = "images/shangArrow.png"
                                    
                                }
                            }
                        }else {
                            //初次点击价格按钮时
                            $scope.sortedGoodsObj.priceTapNums = 0;
                            if (!$scope.sortedGoodsObj.isPriceHeigh){
                                descSorted("shop_price");
                                $scope.sortedGoodsObj.arrowImg = "images/xiaoArrow.png"
                            }else {
                                $scope.sortedGoodsObj.arrowImg = "images/shangArrow.png";
                                ascSorted("shop_price");
                                
                            }
                        }
                    
                    }break;

                case "筛选": {
                    console.log('cate_data。。。。。');
                    console.log($scope.sortedGoodsObj.cateData);
                    $scope.sideMenuObj.sideMenuOnOpened(0,1,$scope.sortedGoodsObj.cateData);
                    $ionicSideMenuDelegate.toggleRight();
                    
                }break;
            }
            function ascSorted(sortKey) {
                
                sortedGoodsObj.goodsDatas = [];
                params.sortKey = sortKey;
                params.sort = "asc";
                requestSorted(params);
                
            }
            function descSorted(sortkey) {
                sortedGoodsObj.goodsDatas = [];
                // params.sfield = "shop_price";
                params.sortKey = sortkey;
                params.sort = "desc";
                requestSorted(params);
                
            }
            //这里是为了避免箭头图片作为点击对象
            if (target.toString().indexOf("Image")!=-1){
                if (target.parent().text() != "筛选"){
                    actions.removeClass("active");
                    target.parent().addClass("active");
                }
            }else if(target.text() == "筛选"){
                
            }else  {
                actions.removeClass("active");
                target.addClass("active");
            }
        }
        $scope.$on("sureSorted",function (event,data) {
            
            $scope.searchStr = '';
            sortedGoodsObj.currentPage = 1;
            params["cate_id[]"] = [];
            params["cate_id[]"]= data["sortedSelectedIDS"];
            // if (data["sortedSelectedIDS"].length > 1) {
                
            // }else {
            //     var sortedClassIDS = data["sortedSelectedIDS"];
            //     angular.forEach($scope.sideMenuObj.sortedSecondClassObj[data["sortedSelectedIDS"][0]].childData,function (item) {
            //         sortedClassIDS.push(item.id);
            //     });
            //     params["cate_id[]"] = sortedClassIDS;
            //
            // }
            
            currentPage = 1;
            params.page = 1;
            params.startPrice = data["minPrice"];
            params.endPrice = data["maxPrice"];
            params.searchStr = '';
            sortedGoodsObj.goodsDatas = [];
            
            // $scope.loadingShow();
            setTimeout(function () {
                doRefresh();
            },200);
            // var sortedRequest = {
            //     success: function (result) {
            //         if (result.status == 0){
            //             $scope.loadingOrPopTipsHide();
            //             if (result["goodsData"].length == 0 && sortedGoodsObj.goodsDatas.length == 0){
            //                 // sortedGoodsObj.noneOfMoreData = false;
            //                 // sortedGoodsObj.noneMsg = "";
            //                 // sortedGoodsObj.dataIsNull = true;
            //                 $scope.sortedGoodsObj.dataIsNull = 1;
            //                 $scope.sortedGoodsObj.nullMsg = "没有此类商品哦O(∩_∩)O~";
            //             }else {
            //                 $scope.sortedGoodsObj.dataIsNull = 0;
            //                 $scope.sortedGoodsObj.nullMsg = "";
            //                 // sortedGoodsObj.dataIsNull = false;
            //                 // sortedGoodsObj.noneOfMoreData = true;
            //                 // sortedGoodsObj.noneMsg = "没有更多商品..."
            //             }
            //             sortedGoodsObj.goodsDatas = result["goodsData"];
            //         }
            //
            //     },
            //     error: function (err) {
            //
            //     }
            // };
            // HttpFactory.getData("/api/getGoods",params)
            //     .then(
            //         sortedRequest.success,
            //         sortedRequest.error
            //     );
        });
        $scope.$on('$ionicView.enter', function () {
            $rootScope.hideTabs = true;
            // if ($ionicSideMenuDelegate.isOpen()){
            //     $ionicSideMenuDelegate.toggleRight();
            // }
            $scope.sideMenuObj.sideMenuClose();
            // inputView = document.getElementById('cft-textField');
            var u = navigator.userAgent;
            var isAndroid = u.indexOf('Android') > -1 || u.indexOf('Adr') > -1; //android终端
            // var isiOS = !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/); //ios终端
            if (isAndroid) {
                var windowHeight = document.body.clientHeight;
                var minHeight = document.body.clientHeight;
                var tabs = document.getElementsByClassName("tabs")[0];
            
                var isShow = false;
                // inputView.onfocus = function () {
                //     tabs.style.opacity = 0;
                //     $rootScope.hideTabs = true;
                //     if (!isShow) {
                //         $timeout(function () {
                //             isShow = true;
                //             // document.body.scrollTop();
                //             // document.documentElement.scrollTop();
                //             document.body.style.position = "absolute";
                //             document.body.style.top = document.body.offsetTop + '1px';
                //             document.body.style.position = "static";
                //             // window.scrollTop(100);
                //         },30);
                //     }
                // };
                // inputView.onblur = function () {
                //     isShow = false;
                //     tabs.style.opacity = 1;
                //     setTimeout(function () {
                //         $rootScope.hideTabs = false;
                //     },40);
                // };
                window.onresize = function () {
                    if (document.body.clientHeight < windowHeight) {
                        if (minHeight > document.body.clientHeight){
                            minHeight = document.body.clientHeight;
                            $rootScope.hideTabs = true;
                            
                        }else {
                        
                        }
                    }
                    if (document.body.clientHeight > minHeight) {
                        if ($ionicSideMenuDelegate.isOpen()){
                            var minPriceInput = document.querySelector('.minPriceInput');
                            var maxPriceInput = document.querySelector('.maxPriceInput');
                            minPriceInput.blur();
                            maxPriceInput.blur();
                        }
                        
                        // inputView.blur();
                        // $rootScope.hideTabs = false;
                    
                    }
                
                };
            }
        
        });
    }]);
/**
 * Created by chaoshen on 2016/12/20.
 */
angular.module('cftApp.sortedIntegral',[])
    .config(['$stateProvider',function ($stateProvider) {
        $stateProvider.state('tabs.sortedIntegral',{
            url: '/sortedIntegral/:searchStr/:cate_id',
            views: {
                'tabs-integralStore': {
                    templateUrl: "sortedIntegralGoods.html",
                    controller: "sortedIntegralController"
                }
            }
        })
    }])
    .controller('sortedIntegralController',['$scope','$stateParams','$state','$ionicSideMenuDelegate','HttpFactory','$ionicViewSwitcher','$ionicScrollDelegate','$rootScope',function ($scope,$stateParams,$state,$ionicSideMenuDelegate,HttpFactory,$ionicViewSwitcher,$ionicScrollDelegate,$rootScope) {
    
        var sortedGoodsObj = $scope.sortedGoodsObj = {
            //对于价格按钮的点击逻辑起作用
            priceTapNums : 0,
            hotTapNums: 0,
            //记录价格排序方式
            isPriceHeigh : true,
            //是否支持加载更多数据
            moredata: false,
            viewTitle : '',
            //数据为空
            dataIsNull: 0,
            nullMsg: '',
            cateData: null,
            //商品数据
            goodsDatas : [],
            //判断是否还有更多数据
            noneOfMoreData: false,
            //进入详情页
            goDetail : goDetail,
            //立即兑换
            convertNow : convertNow,
            //排序方式
            sortAction : sortAction,
            //搜索商品
            goSearch : goSearch,
            //刷新
            doRefresh: doRefresh,
            //加载更多
            loadMore: loadMore,
            //当前页面数
            currentPage: 1,
            hotImgeName: "xiaoArrow.png",
            arrowImg : "images/shangArrow.png",
            noneMsg: '',
            dragup: dragup
        };
        
        function dragup() {
            if (sortedGoodsObj.moredata && sortedGoodsObj.goodsDatas.length > 0){
                sortedGoodsObj.noneOfMoreData = true;
                sortedGoodsObj.noneMsg = "没有更多商品..."
            }
        }
        $scope.$on('$ionicView.beforeEnter', function () {
            $rootScope.hideTabs = true;
        });
        // $scope.$on('$ionicView.enter', function () {
        //     if ($ionicSideMenuDelegate.isOpen()){
        //         $ionicSideMenuDelegate.toggleRight();
        //     }
        // });
        $scope.sortedGoodsObj.viewTitle = $stateParams.sortname;
        var currentPage = 1;
        //作为整个页面的参数对象使用，利于刷新时的统一
        var params = {
            integral: 1,
            total : perPageCount,
            page : currentPage,
            searchStr : $stateParams.searchStr,
            "cate_id[]" : $stateParams.cate_id.split(',')
        };
        
        //刷新
        function doRefresh() {
            
            if (params.searchStr == undefined){
                params.searchStr = "";
            }
            sortedGoodsObj.noneOfMoreData = false;
            sortedGoodsObj.noneMsg = "";
            currentPage = 1;
            params.page = currentPage;
            console.log("+++++"+currentPage);
            // params.page = currentPage;
            sortedGoodsObj.moredata = false;
            var getData = {
                success: function (result) {
                    currentPage = 2;
                    if (result.status == 0){
                        if(result["goodsData"].length < perPageCount){
                            sortedGoodsObj.moredata = true;
                            if (result["goodsData"].length == 0) {
                                $scope.sortedGoodsObj.dataIsNull = 1;
                                // 没有此类商品哦...
                                $scope.sortedGoodsObj.nullMsg = "没有此类商品哦O(∩_∩)O~";
                                
                            }else {
                                $scope.sortedGoodsObj.dataIsNull = 0;
                                $scope.sortedGoodsObj.nullMsg = ""
                            }
                        }else {
                            sortedGoodsObj.moredata = false;
                        }
                        sortedGoodsObj.goodsDatas = result["goodsData"];
                    }
                    $scope.$broadcast('scroll.refreshComplete');
    
                },
                error: function (err) {
                    
                }
            };
            HttpFactory.getData("/api/getGoods",params)
                .then(
                    getData.success,
                    getData.error
                );
        }
        
        function loadMore() {
            console.log("page0->",currentPage);
            params.page = currentPage;
            if (currentPage == 1){
                setTimeout(function () {
                    var loadMoreData = {
                        success: function (result) {
                            
                            if (result.status == 0) {
                                $scope.sortedGoodsObj.cateData = result.cateData;
                                console.log(result);
                                if (result["goodsData"].length < perPageCount){
                                    if (result["goodsData"].length == 0 && sortedGoodsObj.goodsDatas.length == 0){
                                        sortedGoodsObj.dataIsNull = true;
                                        $scope.sortedGoodsObj.nullMsg = "没有此类商品哦O(∩_∩)O~";
                                        $scope.sortedGoodsObj.dataIsNull = 1;
                                    }else {
                                        $scope.sortedGoodsObj.dataIsNull = 0;
                                        $scope.sortedGoodsObj.nullMsg = "";
                                        sortedGoodsObj.dataIsNull = false;
                                    }
                                    sortedGoodsObj.moredata = true;
        
                                }else {
                                    
                                    sortedGoodsObj.moredata = false;
                                    // sortedGoodsObj.noneOfMoreData = false;
                                }
                                sortedGoodsObj.goodsDatas = [];
                                sortedGoodsObj.goodsDatas = sortedGoodsObj.goodsDatas.concat(result["goodsData"]);
                                currentPage ++;
                                // params.page = currentPage;
                            }else {
                            
                            }
                            $scope.$broadcast('scroll.infiniteScrollComplete');
                        },
                        error: function (err) {
                        
                        }
                    };
                    console.log("page->",params.page);
                    HttpFactory.getData("/api/getGoods",params)
                        .then(
                            loadMoreData.success,
                            loadMoreData.error);
                },300);
                return;
            }else {
                console.log(">>>>>>");
                console.log(params);
                params.page = currentPage;
                HttpFactory.getData("/api/getGoods",params)
                    .then(function (result) {
                        console.log(result);
                        if (result.status == 0) {
                            if (result["goodsData"].length < perPageCount){
                                sortedGoodsObj.moredata = true;
                                // sortedGoodsObj.noneOfMoreData = true;
                            }else {
                                sortedGoodsObj.moredata = false;
                                // sortedGoodsObj.noneOfMoreData = false;
                            }
                            sortedGoodsObj.goodsDatas = sortedGoodsObj.goodsDatas.concat(result["goodsData"]);
                            currentPage ++;
                            // params.page = sortedGoodsObj.currentPage;
                        }else {
                        
                        }
                        $scope.$broadcast('scroll.infiniteScrollComplete');
                    },function (err) {
                    })
            }
        
        }
        //进入商品详情页
        function goDetail(item) {
            $state.go('tabs.igDetail',{is_integral: 1,goods_id: item.goods_id});
            $ionicViewSwitcher.nextDirection('forward');
        }
        //当前页搜索
        function goSearch(searchStr) {
        
            if (searchStr == undefined){
                searchStr = "";
            
            }
            params["cate_id[]"] = [];
            params.startPrice = '';
            params.endPrice = '';
            params.searchStr = searchStr;
            $scope.sideMenuObj.isSearch = true;
            sortedGoodsObj.goodsDatas = [];
            doRefresh();
        }
        //点击购物车
        function convertNow($event,item) {
            
        }
        //排序请求
        function requestSorted(paramsObj) {
            
            sortedGoodsObj.moredata = false;
            currentPage = 1;
            paramsObj.page = currentPage;
            // doRefresh();
            // doRefresh();
            $ionicScrollDelegate.scrollTop();
            $ionicScrollDelegate.resize();
            // HttpFactory.getData("/api/getGoods",paramsObj)
            //     .then(function (result) {
            //         sortedGoodsObj.goodsDatas = result["goodsData"];
            //         $ionicScrollDelegate.scrollTop();
            //     },function (err) {
            //
            //     });
        }
        /*function sortAction(event) {
            var actions = angular.element(event.currentTarget).children();
            var target = angular.element(event.target);
        
            switch (target.text()){
                case "综合":
                {
                    $scope.sortedGoodsObj.keyWords = '';
                }break;
            
                case "销量":
                {
                    $scope.sortedGoodsObj.keyWords = 'sellNums';
                }break;
            
                case "价格":
                {
                
                    if (target.hasClass("active")){
                        $scope.sortedGoodsObj.priceTapNums ++;
                        if ($scope.sortedGoodsObj.priceTapNums > 0){
                            $scope.sortedGoodsObj.isPriceHeigh = !$scope.sortedGoodsObj.isPriceHeigh;
                        
                            if ($scope.sortedGoodsObj.isPriceHeigh){
                                ascSorted();
                            }else {
                                descSorted();
                            }
                        }
                    }else {
                        //初次点击价格按钮时
                        $scope.sortedGoodsObj.priceTapNums = 0;
                        if ($scope.sortedGoodsObj.isPriceHeigh){
                            ascSorted();
                        }else {
                            descSorted();
                        }
                    }
                
                }break;
            
                case "筛选": {
                    $scope.sideMenuObj.sideMenuOnOpened(0,1);
                    $ionicSideMenuDelegate.toggleRight();
                }break;
            }
        
            function ascSorted() {
                sortedGoodsObj.goodsDatas = [];
                params.sfield = "shop_price";
                params.sort = "asc";
                requestSorted(params);
                $scope.sortedGoodsObj.keyWords = '-price';
                $scope.sortedGoodsObj.arrowImg = "images/xiaoArrow.png"
            }
            function descSorted() {
                sortedGoodsObj.goodsDatas = [];
                params.sfield = "shop_price";
                params.sort = "desc";
            
                requestSorted(params);
                $scope.sortedGoodsObj.keyWords = 'price';
                $scope.sortedGoodsObj.arrowImg = "images/shangArrow.png"
            }
            //这里是为了避免箭头图片作为点击对象
            if (target.toString().indexOf("Image")!=-1){
                
                if (target.parent().text() != "筛选"){
                    
                    actions.removeClass("active");
                    target.parent().addClass("active");
                }
            }else if(target.text() == "筛选"){
            
            }else  {
                actions.removeClass("active");
                target.addClass("active");
            }
        }*/
        function sortAction(event) {
            $scope.loadingShow();
            $scope.loadingOrPopTipsHide();
            var actions = angular.element(event.currentTarget).children();
            var target = angular.element(event.target);
            sortedGoodsObj.noneOfMoreData = false;
            sortedGoodsObj.noneMsg = "";
            sortedGoodsObj.moredata = false;
            // sortedGoodsObj.moredata = true;
            switch (target.text()){
                case "综合":
                {
                    $scope.sortedGoodsObj.dataIsNull = 0;
                    
                    descSorted("coll");
                }break;
            
                case "销量":
                {
                    $scope.sortedGoodsObj.dataIsNull = 0;
                    // if (target.hasClass("active")){
                    //     $scope.sortedGoodsObj.hotTapNums ++;
                    //     if ($scope.sortedGoodsObj.hotTapNums > 0){
                    //         $scope.sortedGoodsObj.isHotDesc = !$scope.sortedGoodsObj.isHotDesc;
                    //
                    //         if ($scope.sortedGoodsObj.isHotDesc){
                    //             $scope.sortedGoodsObj.hotImgeName = "shangArrow.png";
                    //             ascSorted("hot");
                    //
                    //         }else {
                    //             $scope.sortedGoodsObj.hotImgeName = "xiaoArrow.png";
                    //
                                descSorted("hot");
                    //         }
                    //     }
                    // }else {
                    //     //初次点击价格按钮时
                    //     $scope.sortedGoodsObj.hotTapNums = 0;
                    //     if ($scope.sortedGoodsObj.isHotDesc){
                    //         $scope.sortedGoodsObj.hotImgeName = "shangArrow.png";
                    //         ascSorted("hot");
                    //     }else {
                    //         $scope.sortedGoodsObj.hotImgeName = "xiaoArrow.png";
                    //
                    //
                    //         descSorted("hot");
                    //     }
                    // }
                }break;
            
                case "价格":
                {
                
                    $scope.sortedGoodsObj.dataIsNull = 0;
                    if (target.hasClass("active")){
                        $scope.sortedGoodsObj.priceTapNums ++;
                        if ($scope.sortedGoodsObj.priceTapNums > 0){
                            $scope.sortedGoodsObj.isPriceHeigh = !$scope.sortedGoodsObj.isPriceHeigh;
                            
                            if (!$scope.sortedGoodsObj.isPriceHeigh){
                                descSorted("integral");
                                $scope.sortedGoodsObj.arrowImg = "images/xiaoArrow.png"
                            }else {
                                ascSorted("integral");
                                $scope.sortedGoodsObj.arrowImg = "images/shangArrow.png"
                                
                            }
                        }
                    }else {
                        //初次点击价格按钮时
                        $scope.sortedGoodsObj.priceTapNums = 0;
                        if (!$scope.sortedGoodsObj.isPriceHeigh){
                            descSorted("integral");
                            $scope.sortedGoodsObj.arrowImg = "images/xiaoArrow.png"
                        }else {
                            ascSorted("integral");
                            $scope.sortedGoodsObj.arrowImg = "images/shangArrow.png"
                            
                        }
                    }
                
                }break;
            
                case "筛选": {
                    $scope.sideMenuObj.sideMenuOnOpened(0,1,$scope.sortedGoodsObj.cateData);
                    $ionicSideMenuDelegate.toggleRight();
                
                }break;
            }
            function ascSorted(sortKey) {
            
                sortedGoodsObj.goodsDatas = [];
                params.sortKey = sortKey;
                params.sort = "asc";
                
                requestSorted(params);
                // $scope.sortedGoodsObj.keyWords = '-price';
            
            }
            function descSorted(sortkey) {
                sortedGoodsObj.goodsDatas = [];
                // params.sfield = "shop_price";
                params.sortKey = sortkey;
                params.sort = "desc";
                requestSorted(params);
                // $scope.sortedGoodsObj.keyWords = 'price';
            
            
            }
            //这里是为了避免箭头图片作为点击对象
            if (target.toString().indexOf("Image")!=-1){
                if (target.parent().text() != "筛选"){
                    actions.removeClass("active");
                    target.parent().addClass("active");
                }
            }else if(target.text() == "筛选"){
            
            }else  {
                actions.removeClass("active");
                target.addClass("active");
            }
        }
        $scope.$on("sureSorted",function (event,data) {
            
            $scope.searchStr = '';
            console.log(data);
            sortedGoodsObj.currentPage = 1;
            // if (data["sortedSelectedIDS"].length > 1) {
                params["cate_id[]"] = [];
                params["cate_id[]"]= data["sortedSelectedIDS"];
            // }else {
            //     var sortedClassIDS = data["sortedSelectedIDS"];
            //     angular.forEach($scope.sideMenuObj.sortedSecondClassObj[data["sortedSelectedIDS"][0]].childData,function (item) {
            //         sortedClassIDS.push(item.id);
            //     });
            //     params["cate_id[]"] = sortedClassIDS;
            //
            // }
            
            params.startPrice = data["minPrice"];
            params.endPrice = data["maxPrice"];
            params.searchStr = '';
            sortedGoodsObj.goodsDatas = [];
            sortedGoodsObj.noneOfMoreData = false;
            console.log(">>>>>>>>");
            console.log(params);
            setTimeout(function () {
                doRefresh();
            },200);
            
            // var sortedRequest = {
            //     success: function (result) {
            //         if (result.status == 0){
            //
            //             if (result["goodsData"].length == 0 && sortedGoodsObj.goodsDatas.length == 0){
            //                 // sortedGoodsObj.dataIsNull = true;
            //                 $scope.sortedGoodsObj.nullMsg = "没有此类商品哦O(∩_∩)O~";
            //                 $scope.sortedGoodsObj.dataIsNull = 1;
            //             }else {
            //                 $scope.sortedGoodsObj.dataIsNull = 0;
            //                 $scope.sortedGoodsObj.nullMsg = "";
            //                 // sortedGoodsObj.dataIsNull = false;
            //             }
            //
            //
            //             sortedGoodsObj.goodsDatas = result["goodsData"];
            //         }
            //
            //     },
            //     error: function (err) {
            //
            //     }
            // };
            // HttpFactory.getData("/api/getGoods",params)
            //     .then(
            //         sortedRequest.success,
            //         sortedRequest.error
            //     );
        });
        $scope.$on('$ionicView.enter', function () {
            $rootScope.hideTabs = true;
            // if ($ionicSideMenuDelegate.isOpen()){
            //     $ionicSideMenuDelegate.toggleRight();
            // }
            $scope.sideMenuObj.sideMenuClose();
            // inputView = document.getElementById('cft-textField');
            var u = navigator.userAgent;
            var isAndroid = u.indexOf('Android') > -1 || u.indexOf('Adr') > -1; //android终端
            // var isiOS = !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/); //ios终端
            if (isAndroid) {
                var windowHeight = document.body.clientHeight;
                var minHeight = document.body.clientHeight;
                var tabs = document.getElementsByClassName("tabs")[0];
            
                var isShow = false;
                // inputView.onfocus = function () {
                //     tabs.style.opacity = 0;
                //     $rootScope.hideTabs = true;
                //     if (!isShow) {
                //         $timeout(function () {
                //             isShow = true;
                //             // document.body.scrollTop();
                //             // document.documentElement.scrollTop();
                //             document.body.style.position = "absolute";
                //             document.body.style.top = document.body.offsetTop + '1px';
                //             document.body.style.position = "static";
                //             // window.scrollTop(100);
                //         },30);
                //     }
                // };
                // inputView.onblur = function () {
                //     isShow = false;
                //     tabs.style.opacity = 1;
                //     setTimeout(function () {
                //         $rootScope.hideTabs = false;
                //     },40);
                // };
                window.onresize = function () {
                    if (document.body.clientHeight < windowHeight) {
                        if (minHeight > document.body.clientHeight){
                            minHeight = document.body.clientHeight;
                            $rootScope.hideTabs = true;
                        
                        }else {
                        
                        }
                    }
                    if (document.body.clientHeight > minHeight) {
                        if ($ionicSideMenuDelegate.isOpen()){
                            var minPriceInput = document.querySelector('.minPriceInput');
                            var maxPriceInput = document.querySelector('.maxPriceInput');
                            minPriceInput.blur();
                            maxPriceInput.blur();
                        }
                    
                        // inputView.blur();
                        // $rootScope.hideTabs = false;
                    
                    }
                
                };
            }
        
        });
    }]);

/**
 * Created by qingyun on 16/11/30.
 */
angular.module('cftApp.tabs',[])
    .controller('tabsController',['$scope','$rootScope','$ionicViewSwitcher','$location','$state','$ionicLoading','$ionicGesture','$document','HttpFactory','WebIMWidget','RongCustomerService','CftStore','$http',function ($scope, $rootScope, $ionicViewSwitcher, $location, $state, $ionicLoading, $ionicGesture, $document, HttpFactory,WebIMWidget,RongCustomerService, CftStore,$http) {
        var isConfigRongYunSuccess = false;
        var sideMenuObj = $scope.sideMenuObj = {
            //是否是积分商品 0 普通商品 1 积分商品
            is_integral: 0,
            //是否是所有商品
            is_allGoods: 0,
            //侧边栏一级标题
            headTitle: '一级分类',
            menuSecondClasses: [],
            sortedClassKeys: [],
            //用作将全部商品筛选的结果反馈给 sortedController
            filterObj: {
                sortedSelectedIDS: [],
                minPrice: '',//最小价
                maxPrice: ''//最高价
            },
            //用于标示全局搜索
            isSearch: false,
            isOpen: false,
            isShow: false,
            sortedSecondClassObj: {},
            //选择全部商品
            selectAll: selectAll,
            //选择首页一级菜单
            selectHomeFirstClass: selectHomeFirstClass,
            //选中首页二级菜单
            tapedHomeSecondClass: tapedHomeSecondClass,
            //侧边栏菜单打开时的一些默认配置和操作
            sideMenuOnOpened: sideMenuOnOpened,
            //选中筛选一级菜单
            selectFiterFirstClass: selectFiterFirstClass,
            //选中筛选二级菜单
            tapedSortedSecondClass: tapedSortedSecondClass,
            //取消按钮
            cancelOption: cancelOption,
            //确认按钮
            sureOption: sureOption,
            //重置侧边栏
            // resetSideMenu: resetSideMenu
            sideMenuClose: sideMenuClose,
            priceOnFocus: priceOnFocus,
            priceOnBlur: priceOnBlur,
            tabIsHide: 0
        };
        function priceOnFocus(inputName,event) {
            console.log(event.target);
            event.target.placeholder = '';
            $scope.sideMenuObj.tabIsHide = 1;
        }
        function priceOnBlur(inputName,event) {
            $scope.sideMenuObj.tabIsHide = 0;
            if (inputName == 'min'){
                event.target.placeholder = '最低价';
            }else {
                event.target.placeholder = '最高价';
            }
        }
        var selectedObj = {
            oneclass: '2',
            secondClass: [],
            minPrice: '',
            maxPrice: ''
        };
        //用户购物车商品数量
        $scope.user_Car_Num = '';
        //记录打开客服还是扫码
        var is_KF_scanQRCode = '';
        var selectedIDS = [];
        localStorage.headimgurl = '';
        localStorage.userName = '';
        localStorage.creditNum = '';
        
        //微信jsAPI接入
        
        HttpFactory.getData("/api/getSign",{url:location.href.split('#')[0]}).then(function (result) {
            wx.config({
                debug: false, // 开启调试模式,调用的所有api的返回值会在客户端alert出来，若要查看传入的参数，可以在pc端打开，参数信息会通过log打出，仅在pc端时才会打印。
                appId: result.appId, // 必填，公众号的唯一标识
                timestamp: result.timestamp, // 必填，生成签名的时间戳
                nonceStr: result.nonceStr, // 必填，生成签名的随机串
                signature: result.signature,// 必填，签名，见附录1
                jsApiList: ["onMenuShareTimeline","onMenuShareAppMessage","openLocation","getLocation","scanQRCode","chooseWXPay",'openProductSpecificView','closeWindow','hideMenuItems'] // 必填，需要使用的JS接口列表，所有JS接口列表见附录2
            });
        });
        
        wx.ready(function(){
            console.log("微信准备完毕");
            // wx.showAllNonBaseMenuItem();
            wx.hideMenuItems({
                menuList: [
                    "menuItem:share:email",
                    "menuItem:openWithSafari",
                    "menuItem:share:qq",
                    'menuItem:share:QZone',
                    "menuItem:copyUrl",
                    "menuItem:exposeArticle",
                    "menuItem:setFont",
                    "menuItem:readMode"
                    // "menuItem:refresh"
                ] // 要隐藏的菜单项，只能隐藏“传播类”和“保护类”按钮，所有menu项见附录3
            });
            // wx.showMenuItems({
            //     menuList: [
            //         'menuItem:profile', // 添加查看公众号
            //         'menuItem:share:appMessage',
            //         "menuItem:share:timeline",
            //         "menuItem:favorite"
            //     ]
            // });
            // config信息验证后会执行ready方法，所有接口调用都必须在config接口获得结果之后，config是一个客户端的异步操作，所以如果需要在页面加载时就调用相关接口，则须把相关接口放在ready函数中调用来确保正确执行。对于用户触发时才调用的接口，则可以直接调用，不需要放在ready函数中。
            var phpCookies = CftStore.get('phpCookies');
            var cookieParam = {PHPSESSID:phpCookies};
            console.log(cookieParam);
            if (phpCookies == '' || phpCookies == 'undefined'){
                cookieParam = {};
            }
            //,cookieParam
            // ,cookieParam
            
            HttpFactory.getData('/api/memberInfo').then(function (result) {
                // alert(JSON.stringify(result));
                if(result.status == 1) {
                    $scope.popTipsShow('未登录，请重新进入公众号');
                    $state.go('tabs.homePage');
                }
                // alert('测试头像:'+result.headimgurl);
                user_car_num = parseInt(result.cartnum);
                // alert("头像URL:"+result.headimgurl+",状态："+result.status)
                localStorage.headimgurl = result.headimgurl;
                localStorage.userName = result.nickname;
                localStorage.creditNum = result.integral;
                $scope.$broadcast("userInfo",{headimgurl: result.headimgurl,userName:result.nickname,creditNum: result.integral});
                // localStorage.integral = result.integral
                
                wx.onMenuShareAppMessage({
                    title: 'SUNNY SHU官方商城', // 分享标题
                    desc: '有机生活共享平台，欢迎您的光临。', // 分享描述
                    link: 'http://www.sunnyshu.cn/sunny/wap/api/memberlogin?isp=4&c_user=' + result.userid, // 分享链接
                    imgUrl: 'http://www.sunnyshu.cn/sunny/wap/images/logo_share.png', // 分享图标
                    type: 'link', // 分享类型,music、video或link，不填默认为link
                    dataUrl: '', // 如果type是music或video，则要提供数据链接，默认为空
                    success: function () {
                        // 用户确认分享后执行的回调函数
                    },
                    cancel: function () {
                        // 用户取消分享后执行的回调函数
                    }
                });
                wx.onMenuShareTimeline({
                    title: 'SUNNY SHU官方商城', // 分享标题
                    link: 'http://www.sunnyshu.cn/sunny/wap/api/memberlogin?isp=4&c_user=' + result.userid, // 分享链接
                    imgUrl: 'http://www.sunnyshu.cn/sunny/wap/images/logo_share.png', // 分享图标
                    success: function () {
                        // 用户确认分享后执行的回调函数
                    },
                    cancel: function () {
                        // 用户取消分享后执行的回调函数
                    }
                });
            },function (err) {

            });

        });
        wx.error(function(res){
            // alert(JSON.stringify(res));
            // config信息验证失败会执行error函数，如签名过期导致验证失败，具体错误信息可以打开config的debug模式查看，也可以在返回的res参数中查看，对于SPA可以在这里更新签名。

        });
        $rootScope.$on('$routeChangeStart',function (evt,current,previous) {
            console.log(current);

            // window.addEventListener("popstate", function(e) {
            //     if (current.name == "tabs.homePage" || current.name == "tabs.personal" || current.name == "tabs.integralStore" || current.name == "tabs.napaStores"){
            //         console.log(window.history.length);
            //         if(window.history.length > 2){
            //             wx.closeWindow();
            //         }
            //     }
            // }, false);
        });
        $rootScope.$on('$stateChangeSuccess',function (evt,current,previous,fromState) {

            var self_kf_btn = angular.element(document.getElementById("self_kf_btn"));
            if (current.name == "tabs.personal" || current.name == "tabs.myOrder" || current.name == "tabs.collectionPager" || current.name == "tabs.shoppingCart" || current.name == "tabs.totalScore" || current.name == "tabs.receiptAddress" || current.name == "tabs.payRecord" || current.name == "tabs.scanCodePayment" || current.name == "tabs.evaluatePage" || current.name == "tabs.confirmOrder_personal" || current.name == "tabs.orderDetail" || current.name == "tabs.confirmOrder_personal" || current.name == "tabs.confirmOrder_personal" || current.name == "tabs.confirmOrder_personal" || !isConfigRongYunSuccess){
                self_kf_btn.css("display",'none');
            }else {
                self_kf_btn.css("display",'');
                if (current.name == 'tabs.napaStores'){
                    is_KF_scanQRCode = '扫码';
                    self_kf_btn.css("background","url('images/paymoney.png') no-repeat center/100%");
                }else {
                    is_KF_scanQRCode = '';
                    self_kf_btn.css("background","url('images/service1.png') no-repeat center/100%");
                }
            }
            var update_wx_title = function(title) {
                var body = document.getElementsByTagName('body')[0];
                document.title = title;
                var iframe = document.createElement("iframe");
                iframe.setAttribute("src", "images/empty.png");
                iframe.addEventListener('load', function() {
                    setTimeout(function() {
                        // iframe.removeEventListener('load');
                        document.body.removeChild(iframe);
                    });
                });
                document.body.appendChild(iframe);
            };
            switch (current.name){
                case 'tabs.homePage':
                    update_wx_title("SUNNY SHU官方商城");
                    break;
                case 'tabs.integralStore':
                    update_wx_title("积分商城");
                    break;
                case 'tabs.napaStores':
                    update_wx_title("加盟店");
                    break;
                case 'tabs.personal':
                    update_wx_title("个人中心");
                    break;
                case 'tabs.goodsDetail':{
                    update_wx_title(CftStore.get("goodsName"));
                }break;
                case 'tabs.goodsDetail_collection':{
                    update_wx_title(CftStore.get("goodsName"));
                }break;
                case 'tabs.goodsDetail_orderDetail':{
                    update_wx_title(CftStore.get("goodsName"));
                }break;
                case 'tabs.igDetail_personal':{
                    update_wx_title(CftStore.get("goodsName"));
                }break;
                //tabs.goodsDetail_collection
                case 'tabs.shoppingCart':
                    update_wx_title("购物车");
                    break;
                case 'tabs.shoppingCart_fromDetail':
                    update_wx_title("购物车");
                    break;
                case 'tabs.igDetail':
                    update_wx_title(CftStore.get("goodsName"));
                    break;
                case 'tabs.receiptAddress_home':
                    update_wx_title("管理收货地址");
                    break;
                case 'tabs.receiptAddress':
                    update_wx_title("管理收货地址");
                    break;
                case 'tabs.receiptAddress_IG':
                    update_wx_title("管理收货地址");
                    break;
                case 'tabs.collectionPager':
                    update_wx_title("我的收藏");
                    break;
                case 'tabs.myOrder':
                    update_wx_title("我的订单");
                    break;
                case 'tabs.totalScore':
                    update_wx_title("积分查询");
                    break;
                case 'tabs.payRecord':
                    update_wx_title("交易记录");
                    break;
                case 'tabs.confirmOrder':
                    update_wx_title("确认订单");
                    break;
                case 'tabs.confirmOrder_personal':
                    update_wx_title("确认订单");
                    break;
                case 'tabs.confirmOrder_IG':
                    update_wx_title("确认订单");
                    break;
                case 'tabs.orderDetail':
                    update_wx_title("订单详情");
                    break;
                case 'tabs.sortedGoods':
                    update_wx_title("所有商品");
                    break;
            }

        });
        $scope.$on('detailEvaSucc',function (data) {
            console.log("评价是否成功");
            console.log(data);
           if (data == 'success'){
               $scope.$broadcast('toDetailSuccess','success');
           }
        });
        //进去积分商城的方法
        $scope.clickHome = function () {
            $state.go("tabs.integralStore");
        };
        //进入个人中心的方法
        $scope.clickPersonal = function () {
            $state.go("tabs.personal");
        };
        //选中全部商品全部商品
        function selectAll(event) {
            
            $rootScope.hideTabs = true;
            
            sideMenuObj.isSearch = true;
            sideMenuObj.oneclass = '';
            sideMenuObj.secondClass = [];
            CftStore.setObject("selectedObj",selectedObj);
            
            if (sideMenuObj.is_integral === 0){
                //广播给 homePage
                $scope.$broadcast("home_sortedView","");
            }else {
                //广播给 integralStore
                $scope.$broadcast("integral_sortedView","");
            }
        }
        //选中首页一级标题
        function selectHomeFirstClass(event,key) {
            
            selectedObj.oneclass = key;
            selectedIDS = [];
            selectedIDS.push(key);
            setSecondClassMenu(key);
        }
        //选中首页二级标题
        function tapedHomeSecondClass(event,item) {
            sideMenuObj.isSearch = false;
            selectedObj.secondClass = [];
            selectedObj.secondClass.push(item.id);
            selectedIDS = [];
            selectedIDS.push(item.id);
            console.log("首页选中二级标题");
            console.log(selectedObj.secondClass);
            CftStore.setObject("selectedObj",selectedObj);
            console.log(selectedIDS);
            $rootScope.hideTabs = true;
            if (sideMenuObj.is_integral === 0){
            
                $scope.$broadcast("home_sortedView",selectedIDS);
            }else {
                $scope.$broadcast("integral_sortedView",selectedIDS);
            }
        }
        //通过一级分类的键获取二级分类数据
        function setSecondClassMenu(num) {
            console.log("选中一级");
            console.log(num);
            if (num){
                $scope.sideMenuObj.isShow = true;
            }else {
                $scope.sideMenuObj.isShow = false;
                return;
            }
            $scope.sideMenuObj.menuSecondClasses = sideMenuObj.sortedSecondClassObj[num].childData;
            var secondClasses = angular.element(document.querySelector("#secondClasses")).children();
            secondClasses.removeClass("active");
            if(sideMenuObj.sortedSecondClassObj[num].childData == null){
                $scope.popTipsShow("暂无二级分类");
                return;
            }
            sideMenuObj.headTitle = "二级分类";
        }
        
        //is_integral; 0 普通商品首页 1 积分首页; is_allGoods: 0 全部普通商品 1 全部积分商品
        //            打开全部商品的侧边栏
        function sideMenuOnOpened(is_integral,is_allGoods,cateData) {
            setTimeout(function () {
                $scope.sideMenuObj.isOpen = true;
            },500);
            sideMenuObj.is_allGoods = is_allGoods;
            sideMenuObj.is_integral = is_integral;
            console.log("cateData");
            console.log(cateData);
            console.log(sideMenuObj.sortedSecondClassObj);
            if (JSON.stringify(sideMenuObj.sortedSecondClassObj) != '{}'){
                console.log(".....");
                sideMenuObj.sortedClassKeys = Object.keys(sideMenuObj.sortedSecondClassObj);
            }else {
                sideMenuObj.sortedSecondClassObj = cateData;
                sideMenuObj.sortedClassKeys = Object.keys(cateData);
            }
            
            console.log(sideMenuObj.sortedClassKeys);
            //首页筛选
            if (!is_allGoods){
                sideMenuObj.headTitle = "一级分类";
            }
            //所有商品筛选
            else {
                // cancelOption();
                var secondClasses = angular.element(document.querySelector("#secondClasses")).children();
                secondClasses.removeClass("active");
                if (sideMenuObj.isSearch){
                    selectedObj.oneclass = '';
                    selectedObj.secondClass = [];
                    selectedObj.minPrice = '';
                    selectedObj.maxPrice = '';
                    CftStore.setObject("selectedObj",selectedObj);
                    // return;
                }else {
                    selectedObj = CftStore.getObject("selectedObj");
                    console.log("这是全部商品打开 侧边栏对象");
                    console.log(selectedObj);
                }
                sideMenuObj.filterObj.minPrice = selectedObj.minPrice;
                sideMenuObj.filterObj.maxPrice = selectedObj.maxPrice;
                setSecondClassMenu(selectedObj.oneclass);
        
                setTimeout(function () {
            
                    //打开时先获取本地选中对象
                    setSecondClassMenu(selectedObj.oneclass);
                    //将所有一级菜单重置
                    var firstClasses = angular.element(document.getElementById("sortedFirstClass")).children();
                    firstClasses.removeClass("active");
                    //设置一级菜单
                    angular.forEach(firstClasses,function (value,key) {
                        //转为ng元素
                        var ngEle = angular.element(value);
                
                        if (ngEle.hasClass(selectedObj.oneclass))
                            ngEle.addClass("active");
                
                    });
                    var secondClasses = angular.element(document.getElementById("secondClasses")).children();
                    secondClasses.removeClass("active");
                    //设置二级菜单
                    for (var className in selectedObj.secondClass){
                        if (selectedObj.secondClass.hasOwnProperty(className)){
                    
                            angular.forEach(secondClasses,function (value,key) {
                                var ngEle = angular.element(value);
                        
                                if (ngEle.hasClass(selectedObj.secondClass[Number(className)])){
                                    ngEle.addClass("active");
                                }
                            });
                        }
                    }
            
            
                },10);
            }
            
            
        }
        //关闭侧边栏
        function sideMenuClose() {
            if ($scope.sideMenuObj.isOpen){
                $scope.sideMenuObj.isOpen = false;
                
            }
        }
        
        //2.全部商品 筛选分类逻辑
        function selectFiterFirstClass(event,key) {
            //清空二级菜单
            selectedObj.secondClass = [];
            //遍历一级菜单
            var firstClasses = angular.element(document.getElementById("sortedFirstClass")).children();
            //移除所有 active 类
            firstClasses.removeClass("active");
            //将选中 元素添加 active 类
            var target = angular.element(event.target);
            target.addClass("active");
            //创建二级菜单
            setSecondClassMenu(key);
            //修改本地的一级选中
            selectedObj.oneclass = key;
           
        }
        
        function tapedSortedSecondClass(event,item) {
            var target = angular.element(event.target);
            if (target.hasClass("active")){
                target.removeClass("active");
                selectedObj.secondClass.cftRemove(item.id);
            }else {
                target.addClass("active");
                selectedObj.secondClass.push(item.id);
            }
        }
        function cancelOption() {
            selectedObj = CftStore.getObject("selectedObj");
            var secondClasses = angular.element(document.querySelector("#secondClasses")).children();
            secondClasses.removeClass("active");
        }
        function sureOption() {
            selectedObj.minPrice = sideMenuObj.filterObj.minPrice;
            selectedObj.maxPrice = sideMenuObj.filterObj.maxPrice;
            sideMenuObj.isSearch = false;
            CftStore.setObject("selectedObj",selectedObj);
            selectedIDS = [];
            if(selectedObj.secondClass.length > 0){
                selectedIDS = selectedObj.secondClass;
            }else {
                selectedIDS=selectedObj.oneclass;
            }
            sideMenuObj.filterObj.sortedSelectedIDS = selectedIDS;
            console.log("???????");
            console.log(sideMenuObj.filterObj);
            $scope.$broadcast("sureSorted",sideMenuObj.filterObj);
        }


        //全局提示的弹窗
        $scope.popTipsShow = function (msg) {
            $ionicLoading.show({
                template: msg,
                duration: 1300,
                noBackdrop: true
            }).then(function(){
                
            });
        };
        //全局加载中提示
        $scope.loadingShow = function (str) {
            str = str ? str : "加载中...";
            $ionicLoading.show({
                template: '<ion-spinner class="selfSpinner">' + str + '</ion-spinner>',
                noBackdrop:true
            });
        };
        // $scope.sessid = SESSID;
        //提示隐藏
        $scope.loadingOrPopTipsHide = function(){
            $ionicLoading.hide();
        };
        
        // getRongyun();
        // setTimeout(function () {
        //     getRongyun();
        // },5000);
        // //接入融云初始化客服
        // function getRongyun() {
            //?sessid=' + SESSID
            HttpFactory.getData('/rongyun/index').then(function (result) {
                // console.log("融云介入");
                // alert('融云介入'+SESSID);
                // alert(result);
                // alert(JSON.stringify(result));
                RongCustomerService.init({
                    appkey:result.appkey,
                    token:result.token,
                    customerServiceId:result.customerServiceId,
                    position:RongCustomerService.Position.right,
                    reminder:" ",
                    // displayConversationList:false,
                    style:{
                        width:document.body.clientWidth,
                        height:document.body.clientHeight,
                        displayMinButton:false,
                        positionFixed:true
                    },
                    onSuccess:function(){
                        // alert("配置成功");
                        //初始化完成
                        isConfigRongYunSuccess = true;
                        var rongWidget = angular.element(document.getElementsByTagName('rong-widget'));
                        rongWidget.css('display','');
                        //Messages
                        var rongCentent = document.getElementById('Messages');
                        console.log("融云聊天内容");
                        console.log(rongCentent);
                        rongCentent.addEventListener('touchstart',function () {
                            angular.element(rongCentent).css('overflow','scroll !important');
                            console.log("摸我干啥");
                        },false);
                        // rongCentent.css('overflow','scroll !important');
                        //设置客服按钮位置
                        var kf = angular.element(document.getElementById('rong-widget-minbtn'));
                        kf.css('display','none');
                        var rongSendBtn = angular.element(document.getElementById('rong-sendBtn'));
                        rongSendBtn.css('backgroundColor','#E60012');
                
                        var minBtn = angular.element(document.getElementById('header').childNodes[1].childNodes[1]);
                        minBtn.on('click',function () {
                            
                            // RongCustomerService.minimize();
                            console.log("hello,word");
                        });
                        var self_kf_btn = angular.element(document.getElementById("self_kf_btn"));
                        if ($location.url() != '/tabs/personal'){
                            self_kf_btn.css("display",'');
                            $state.reload();
                        }
                        var nowBottom = parseFloat(self_kf_btn.css("bottom").split('px')[0]);
                        // document.addEventListener('touchstart', function(event) {
                        //     // 判断默认行为是否可以被禁用
                        //     if (event.cancelable) {
                        //         // 判断默认行为是否已经被禁用
                        //         if (!event.defaultPrevented) {
                        //             event.preventDefault();
                        //         }
                        //     }
                        // }, false);
                        $document.on('touchmove',function (event) {
                            // 判断默认行为是否可以被禁用
                            if (event.cancelable) {
                                // 判断默认行为是否已经被禁用
                                if (!event.defaultPrevented) {
                                    event.preventDefault();
                                    event.stopPropagation();
                                }
                            }
                        });
                        $ionicGesture.on('touch',function (e) {
                            nowBottom = parseFloat(self_kf_btn.css("bottom").split('px')[0]);
                            console.log(".......");
                        },self_kf_btn);
                        // $ionicGesture.on('release',function (e) {
                        //     $document.off('touchstart',function () {
                        //         alert(1);
                        //     })
                        // },self_kf_btn);
                        $ionicGesture.on('dragup',function (e) {
                            if (document.body.clientHeight - parseFloat(self_kf_btn.css("bottom").split('px')[0]) < 30){
                                self_kf_btn.css("bottom",document.body.clientHeight - 30 + "px");
                            }else {
                                self_kf_btn.css("bottom",nowBottom - e.gesture.deltaY + "px");
                            }
                        },self_kf_btn);
                        $ionicGesture.on('dragdown',function (e) {
                            if (parseFloat(self_kf_btn.css("bottom").split('px')[0]) < 10){
                                self_kf_btn.css("bottom",10 + "px");
                            }else {
                                self_kf_btn.css("bottom",nowBottom - e.gesture.deltaY + "px");
                            }
                        },self_kf_btn);
                
                    },
                    onError:function(err){
                        //初始化错误
                        // alert(err);
                    }
                });
            });
        // }
        

        //打开客服或者扫码的方法
        $scope.open_KF_scanQRCode = function () {
            if(is_KF_scanQRCode == '扫码'){
                wx.scanQRCode({
                    needResult: 0, // 默认为0，扫描结果由微信处理，1则直接返回扫描结果，
                    scanType: ["qrCode","barCode"], // 可以指定扫二维码还是一维码，默认二者都有
                    success: function (res) {
                        var result = res.resultStr; // 当needResult 为 1 时，扫码返回的结果
                    }
                });
            }else {
                $scope.showbtn();
            }
        };
        
    }]);


/**
 * Created by Administrator on 2016/12/14.
 */
angular.module('cftApp.totalScore',[])
    .config(['$stateProvider',function ($stateProvider) {
        $stateProvider.state('tabs.totalScore',{
            url:'/totalScore',
            views:{
                'tabs-personal':{
                    templateUrl:'totalScore.html',
                    controller:'totalScoreController'
                }
            }
        });
    }]).controller('totalScoreController',['$scope','$rootScope','$state','$ionicViewSwitcher','HttpFactory','CftStore',function ($scope,$rootScope,$state,$ionicViewSwitcher,HttpFactory,CftStore) {
    $scope.goExchange=function(msg){
        $state.go('tabs.integralStore');
        // $ionicViewSwitcher.nextDirection("forward");
    };
    $scope.$on('$ionicView.beforeEnter', function () {
        $rootScope.hideTabs = true;
    });

    $scope.scoreObj = {
        scoreListDatas: [],
        moredata: true,
        loadMore: loadMore,
        doRefresh: doRefresh,
        msgsIsNull: 0,
        emptyLogMsg: 1,
        dragup: dragup,
        noMoreData: 0,
        noMoreDataMsg: '',
        creditNum: 0
        
    };
    var freshFlag = 0;
    $scope.scoreObj.creditNum = CftStore.get('creditNum');
    // var params = {
    //
    // };
    function dragup() {
        console.log("my integral drag up");
        if (!$scope.scoreObj.moredata && $scope.scoreObj.scoreListDatas.length > 0){
            $scope.scoreObj.noMoreData = 1;
            $scope.scoreObj.noMoreDataMsg = "没有更多积分记录";
        }
    }
    var currentPage = 1;
    function doRefresh() {
        if (freshFlag == 0){
            $scope.$broadcast('scroll.refreshComplete');
            return;
        }
        // $scope.$broadcast('scroll.infiniteScrollComplete');
        currentPage = 1;
        $scope.scoreObj.noMoreData = 0;
        $scope.scoreObj.noMoreDataMsg = "";
        
        HttpFactory.getData("/api/uintegral",{sessid:SESSID,page: currentPage,total: perPageCount},"GET")
            .then(function (result) {
                if (result.status == 0){
                    currentPage  = 2;
                    if (result['integralData'].length == 0 && $scope.scoreObj.scoreListDatas.length == 0){
                        
                        $scope.scoreObj.msgsIsNull = 1;
                        $scope.scoreObj.emptyLogMsg = "您还没积分记录O(∩_∩)O~"
                    }else {
                        
                        $scope.scoreObj.msgsIsNull = 0;
                        $scope.scoreObj.emptyLogMsg = ""
                    }
                    if (result['integralData'].length >= perPageCount){
                        $scope.scoreObj.moredata = true;
                    }else {
                        $scope.scoreObj.moredata = false;
                    }
                    $scope.scoreObj.scoreListDatas =result['integralData'];
                }
                console.log(result);
                $scope.$broadcast('scroll.refreshComplete');
                console.log($scope.scoreObj.scoreListDatas);
            },function (err) {
            
            });
    }
    function loadMore() {
        console.log(currentPage);
        HttpFactory.getData("/api/uintegral",{sessid:SESSID,page: currentPage,total: perPageCount},"GET")
            .then(function (result) {
                freshFlag = 1;
                if (result.status == 0){
                    currentPage++;
                    if (result['integralData'].length < perPageCount){
                        $scope.scoreObj.moredata = false;
                        if (result['integralData'].length == 0 && $scope.scoreObj.scoreListDatas.length == 0){
                            $scope.scoreObj.msgsIsNull = 1;
                            $scope.scoreObj.emptyLogMsg = "您还没积分记录O(∩_∩)O~"
                        }else {
                            $scope.scoreObj.msgsIsNull = 0;
                            $scope.scoreObj.emptyLogMsg = ""
                        }
                    }else {
                        $scope.scoreObj.moredata = true;
                    }
                    $scope.scoreObj.scoreListDatas =$scope.scoreObj.scoreListDatas.concat(result['integralData']);
                }
                console.log(result);
                $scope.$broadcast('scroll.infiniteScrollComplete');
                console.log($scope.scoreObj.scoreListDatas);
            },function (err) {
            
            });
    }
    

}]);
/**
 * Created by qingyun on 16/12/2.
 */
angular.module('cftApp.factories',[]).factory('HttpFactory',['$http','$q',function ($http,$q) {
    return {
        getData:function (url,params,type) {
            if (url){
                url = ROOT_URL + url;
                var promise = $q.defer();
                type = type ? type:"GET";
                params = params ? params:{};
                $http({
                    url:url,
                    method:type,
                    // params:type == "GET" ? params:{},
                    // data:type == "GET" ? {}:params,
                    params:params,
                    timeout:20000
                }).then(function (reslut) {
                    reslut =reslut.data;
                    // reslut = reslut[Object.keys(reslut)[0]];
                    promise.resolve(reslut);
                },function (err) {
                    promise.reject(err);
                });
                return promise.promise;
            }
        }
    };
}]).value('MainData',{
    shopping_car_goodsArray:null,
    userSelectAddress:null,
    selectedOrder_datas: null,
    isFromPersonToReceiptAddress:false
});
/**
 * Created by chaoshen on 2017/2/10.
 */
angular.module('ctfApp.keyboardHandler',[])
    .directive('keyboardHandler', ['$window','$rootScope',function ($window,$rootScope) {
        return {
            restrict: 'A',
            link: function postLink(scope, element, attrs) {
                angular.element($window).bind('native.keyboardshow', function() {
                    console.log(">>>..");
                    // alert(">>>>");
                    $rootScope.hideTabs = true;
                    // element.addClass('tabs-item-hide');
                });
                
                angular.element($window).bind('native.keyboardhide', function() {
                    // element.removeClass('tabs-item-hide');
                    $rootScope.hideTabs = false;
                });
            }
        };
    }]);
/**
 * Created by chaoshen on 2016/12/27.
 */
void function (global) {
    function cftRequestUrl(/*requestUrl*/requestUrl,/*paramters*/params) {
        //判断传入的是不是对象
        var property,
            urlStr = ROOT_URL + requestUrl;
        if (Object.prototype.toString.call(params) === '[object Object]' && params != null){
            var paramsCount = 0;//主要作用是让参数的第一位有所区别
            for (property in params){
                if (params.hasOwnProperty(property)){
                    if (paramsCount == 0){
                        urlStr = urlStr + "?" + property + "=" + params[property];
                    }else {
                        urlStr = urlStr + "&" + property + "=" + params[property];
                    }
                    paramsCount ++;
                }
            }
            console.log(urlStr);
            return urlStr;
        //参数作为字符串的时候
        }else if(typeof params == 'string' && params.indexOf("?") == 0){
            return urlStr + params;
        }else {
            return urlStr;
        }
    }
    global.cftRequestUrl = cftRequestUrl;
}(window);
/**
 * Created by chaoshen on 16/12/5.
 */
angular.module('ctfApp.searchBar',[])
    .directive('ctfSearchBar',function () {
        return {
            restrict: 'EA',
            replace: true,
            scope: {
                integral: "@",
                isSorted: "@",
                searchDatas: "="
            },
            template:
            '<div class="ctf-search-view">' +
                '<label class="item item-input">' +
                '<input class="cft-textField" ng-model="searchValue" type="text" placeholder="例：大闸蟹">' +
                '</label>' +
                '<button class="ctf-search-btn" style="height: 1.6rem" ng-click="goSearch(searchValue)"></button>' +
            '</div>',
            controller: ["$scope","$state",'$ionicViewSwitcher','$rootScope','HttpFactory',function ($scope,$state,$ionicViewSwitcher,$rootScope,HttpFactory) {
                
                
        //         $scope.goSearch = function (searchValue) {
        //             if (searchValue){
        //                 console.log("值不为空："+searchValue);
        //                 //普通商品
        //                 if ($scope.integral === "0") {
        //                     if ($scope.isSorted){
        //                         console.log("普通分类筛选");
        //                         $scope.searchDatas = "hello";
        //                         // HttpFactory.getData();
        //                     }else {
        //                         console.log("普通商品");
        //                         $state.go('tabs.sortedGoods',{sortname:"全部商品",searchStr: searchValue});
        //                         $rootScope.hideTabs = true;
        //                         $ionicViewSwitcher.nextDirection('forward');
        //                     }
        //                     // var homeObj = scope.$parent.homeObj
        //                 }
        //                 //积分商
        //                 else {
        //                     if ($scope.isSorted){
        //                         console.log("普通积分分类筛选");
        //                     }else {
        //                         console.log("积分商品");
        //                         // $state.go('tabs.sortedGoods',{sortname:"全部商品",searchStr: searchValue});
        //                         $rootScope.hideTabs = true;
        //                         $ionicViewSwitcher.nextDirection('forward');
        //                     }
        //                 }
        //
        //             }else {
        //                 console.log("输入的值为空");
        //             }
        //         }
            }],
            link: function (scope,elem,attrs) {

            }
        }
    });
/**
 * Created by qingyun on 16/12/2.
 */
//<div class="slideBottomDiv"></div>
angular.module('cftApp.slideBox',[]).directive('mgSlideBox',[function () {
    return{
        restrict:"E",
        scope:{sourceData:'='},
        template:'<div class="topCarousel"><ion-slide-box delegate-handle="topCarouselSlideBox" auto-play="true" does-continue="true"  slide-interval="3000" show-pager="true" on-drag="drag($event)"  ng-if="isShowSlideBox"><ion-slide ng-if="sourceData.ishome == 0 || sourceData.ishome == 1" ng-repeat="ads in sourceData.bannerData track by $index" ng-click="goToDetailView(ads)"><div ng-if="sourceData.ishome == 0 || sourceData.ishome == 1" style="background: url({{iconRootUrl + ads.image_url}}) no-repeat center; width: 100%;background-size: 100% 100%;" class="topCarouselImg"></div></ion-slide><ion-slide ng-if="sourceData.ishome == 2" ng-repeat="imgUrl in sourceData.bannerData track by $index"><div style="background: url(http://www.sunnyshu.cn{{imgUrl}}) no-repeat center;background-size: 100% 100%;width: 100%;padding-bottom: 61%" class="detailTopCarouselImg"></div></ion-slide></ion-slide-box><p></p></div>',
        controller:['$scope','$element','$ionicSlideBoxDelegate','$ionicScrollDelegate','$state','$ionicViewSwitcher','$rootScope','$ionicLoading','CftStore',function ($scope,$element,$ionicSlideBoxDelegate,$ionicScrollDelegate,$state,$ionicViewSwitcher,$rootScope,$ionicLoading,CftStore) {

            //通过 sourceData.instegral 区分进入的详情
            $scope.iconRootUrl = IconROOT_URL;
            $scope.goToDetailView = function (item) {
                if ($scope.sourceData.ishome == 0) {
                    if (!item.param){
                        $ionicLoading.show({
                            template: "该商品不存在!",
                            duration: 1300,
                            noBackdrop: true
                        }).then(function(){

                        });
                        return;
                    }
                    CftStore.set('goodsName',item.name);
                    $state.go('tabs.goodsDetail',{goods_id: item.param});
                }else{
                    if (!item.param){
                        $ionicLoading.show({
                            template: "该商品不存在!",
                            duration: 1300,
                            noBackdrop: true
                        }).then(function(){

                        });
                        return;
                    }
                    CftStore.set('goodsName',item.name);
                    $state.go('tabs.igDetail',{goods_id: item.param});
                }
                $ionicViewSwitcher.nextDirection('forward');
            };
            $scope.isShowSlideBox = false;
            $scope.$watch('sourceData.bannerData',function (newVal,oldVal) {
                
                if (newVal && newVal.length){
                    $scope.isShowSlideBox = true;
                    $ionicSlideBoxDelegate.update();
                    $ionicSlideBoxDelegate.loop(true);
                }
            });
        }],
        replace:true,
        link:function (scope,tElement,tAtts) {
        }
    };
}]);
/**
 * Created by chaoshen on 2017/1/5.
 */
angular.module('cftApp.storageFactory',[])
    .factory('CftStore',["$window",function ($window) {
        return{
            //存储单个属性
            set :function(key,value){
                $window.localStorage[key]=value;
            },
            //读取单个属性
            get:function(key,defaultValue){
                return  $window.localStorage[key] || defaultValue;
            },
            //存储对象，以JSON格式存储
            setObject:function(key,value){
                $window.localStorage[key]=JSON.stringify(value);
            },
            //读取对象
            getObject: function (key) {
                if ($window.localStorage[key]){
                    return JSON.parse($window.localStorage[key]);
                }else {
                    return null;
                }
            }
        
        }
    }]);
/**
 * Created by chaoshen on 2016/12/29.
 */
void function (global) {
    //用于数组删除指定字符串元素元素
    Array.prototype.cftRemove = function (val) {
        var index = this.indexOf(val);
        if (index > -1) {
            this.splice(index,1);
        }
    }
}(window);