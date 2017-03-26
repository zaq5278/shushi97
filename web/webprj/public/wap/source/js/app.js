/**
 * Created by qingyun on 16/11/30.
 */
//js程序入口
angular.module('cftApp',['ionic','cftApp.storageFactory',"ctfApp.keyboardHandler",'cftApp.tabs','cftApp.factories','cftApp.slideBox','ctfApp.searchBar','cftApp.homePage','cftApp.integralStore','cftApp.napaStores','cftApp.personal','cftApp.goodsDetail','cftApp.collection','cftApp.myOrder','cftApp.receiptAddress','cftApp.shoppingCart','cftApp.totalScore','cftApp.payRecord','cftApp.integralGoodsDetail','cftApp.sortedGoods','cftApp.sortedIntegral','RongWebIMWidget','cftApp.confirmOrder','cftApp.evaluatePage','cftApp.scanCodePayment','myApp.codeExtend','myApp.aboutNapaStore'])
    .config(['$stateProvider','$urlRouterProvider','$ionicConfigProvider','$locationProvider',function ($stateProvider,$urlRouterProvider,$ionicConfigProvider,$locationProvider) {
        
        $ionicConfigProvider.platform.android.tabs.position("bottom");
        $ionicConfigProvider.tabs.position('bottom');
        $ionicConfigProvider.tabs.style('standard');
        $ionicConfigProvider.navBar.alignTitle('center');
        $ionicConfigProvider.scrolling.jsScrolling(true);
        ionic.Platform.isFullScreen = true;
        // $ionicConfigProvider.views.maxCache(10);
        // $ionicConfigProvider.templates.maxPrefetch(0);
        // $ionicConfigProvider.views.maxCache(0);
        $stateProvider.state("tabs",{
            url:"/tabs",
            abstract:true,
            templateUrl:"tabs.html",
            controller:'tabsController'
        });
        window.addEventListener('native.keyboardshow', function () {
            document.querySelector('div.tabs').style.display = 'none';
            angular.element(document.querySelector('ion-content.has-tabs')).css('bottom', 0);
        });
        window.addEventListener('native.keyboardhide', function () {
            var tabs = document.querySelectorAll('div.tabs');
            angular.element(tabs[0]).css('display', '');
        });
        //意外跳转
        $locationProvider.hashPrefix('?');
        $urlRouterProvider.otherwise('tabs/homePage');
}]);
var ROOT_URL = "http://www.sunnyshu.cn/sunny/wap",
    perPageCount = 10,
    user_car_num = '',
    IconROOT_URL = "http://www.sunnyshu.cn",
    SESSID = "";
    
    




    