/**
 * Created by chaoshen on 2017/2/21.
 */
angular.module('myApp.aboutNapaStore',[]).config(["$stateProvider",function ($stateProvider) {
    $stateProvider.state('aboutNapaStore',{
        url: '/aboutNapaStore',
        templateUrl: 'aboutNapaStore.html',
        controller: 'aboutNapaStoreController'
        
    });
}])
    .controller('aboutNapaStoreController',[function () {
        
    }]);