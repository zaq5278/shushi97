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




