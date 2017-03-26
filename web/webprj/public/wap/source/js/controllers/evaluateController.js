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