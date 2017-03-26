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