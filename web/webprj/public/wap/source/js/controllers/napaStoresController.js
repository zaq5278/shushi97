/**
 * Created by qingyun on 16/11/30.
 */
angular.module('cftApp.napaStores',[]).config(['$stateProvider',function ($stateProvider) {
    $stateProvider.state('tabs.napaStores',{
        url:'/napaStores',
        views:{
            'tabs-napaStores':{
                templateUrl:'napaStores.html',
                controller:'napaStoresController'
            }
        }
    });
}]).controller('napaStoresController',['$scope','$state','HttpFactory','$ionicModal','$http',function ($scope,$state,HttpFactory,$ionicModal,$http) {
    $scope.items = ['1','2','3'];
    // var loa = getNapaStores();
    var napaObj = $scope.napaObj = {
        storesData: {},
        provinces: [],
        doRefresh: doRefresh,
        loadMore: loadMore,
        moreData: false,
        dataIsNull: 0,
        nullMsg: '',
        noMoreData: 0,
        dragup: dragup
    };
    
    //用于双向绑定地址的变量
    $scope.napaStores = {
        text:'定位中...'
    };
    function dragup() {
        if (!napaObj.moreData && napaObj.storesData.length > 0){
            napaObj.noMoreData = 1;
        }
    }
    var currentPage = 1;
    $http.get('lib/city.json')
        .success(function (result) {
            $scope.napaObj.provinces = result;
            $scope.napaObj.provinces.splice(0,1);
            
        });

    $ionicModal.fromTemplateUrl('choiceAddressModal.html',{
        scope:$scope,
        animation: 'fade-out',
        focusFirstInput:true,
        backdropClickToClose:true
    }).then(function(modal) {
        $scope.modal = modal;
    });


    //当我们用完模型时，清除它！
    $scope.$on('$destroy', function() {
        $scope.modal.remove();
    });
    setTimeout(function () {
        if ($scope.napaStores.text == "定位中..."){
            $scope.popTipsShow("网络请求超时");
        }
    },20000);
    wx.getLocation({
        type: 'wgs84', // 默认为wgs84的gps坐标，如果要返回直接给openLocation用的火星坐标，可传入'gcj02'
        success: function (res) {
            var latitude = res.latitude; // 纬度，浮点数，范围为90 ~ -90
            var longitude = res.longitude; // 经度，浮点数，范围为180 ~ -180。
            var point = new BMap.Point(parseFloat(longitude), parseFloat(latitude));
            var gc = new BMap.Geocoder();
            gc.getLocation(point, function (rs) {
                var addComp = rs.addressComponents;
                if (addComp.province.indexOf('省') > -1){
                    $state.reload();
                    $scope.napaStores.text = addComp.province.split('省')[0];
                }
            });
        },
        cancel: function (res) {
            $scope.popTipsShow('用户拒绝授权获取地理位置');
        }
    });
    function doRefresh() {
        napaObj.noMoreData = 0;
        getNapaStores('refresh');
    }
    function loadMore() {
        getNapaStores('loadMore')
    }
    //获取加盟店信息
    function getNapaStores(loadName) {
        var params = {
            nums:5,
            province:$scope.napaStores.text,
            page: currentPage
        };
        
        HttpFactory.getData("/api/franchise",params).then(function (result) {
            
            if (result["data"].length == 0 && napaObj.storesData.length == 0){
                napaObj.dataIsNull = 1;
                napaObj.nullMsg = '暂无加盟店信息O(∩_∩)O~';
                
            }else {
                napaObj.dataIsNull = 0;
                napaObj.nullMsg = '';
            }
            
            //下拉刷新
            if (loadName == 'refresh'){
                $scope.$broadcast('scroll.refreshComplete');
                currentPage = 2;
                napaObj.storesData = result["data"];
            }
            //加载更多
            if (loadName == 'loadMore'){
                currentPage ++;
                napaObj.moreData = result["data"].length >= perPageCount;
                napaObj.storesData = napaObj.storesData.concat(result["data"]);
                $scope.$broadcast('scroll.infiniteScrollComplete');
            }
            
            if(napaObj.storesData == undefined){
                $scope.popTipsShow("暂无加盟店信息!");
            }
        },function (err) {
        });
    }
    //监控用户城市的选择
    $scope.$watch('napaStores.text',function (newVal,oldVal) {
        if (newVal != oldVal){
            doRefresh();
            // $scope.napaObj.moreData = true;
            // loadMore();
        }
    });
}]);