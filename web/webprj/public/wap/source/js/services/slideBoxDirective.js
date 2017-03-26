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