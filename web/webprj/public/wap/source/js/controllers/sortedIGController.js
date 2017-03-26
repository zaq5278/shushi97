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