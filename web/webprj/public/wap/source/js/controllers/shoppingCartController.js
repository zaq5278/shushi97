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