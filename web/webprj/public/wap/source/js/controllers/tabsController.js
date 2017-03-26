
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

