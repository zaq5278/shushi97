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