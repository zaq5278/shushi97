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