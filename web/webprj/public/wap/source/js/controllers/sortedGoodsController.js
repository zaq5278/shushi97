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