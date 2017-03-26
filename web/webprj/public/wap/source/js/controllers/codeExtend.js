/**
 * Created by chaoshen on 2017/2/21.
 */
angular.module('myApp.codeExtend',[]).config(["$stateProvider",function ($stateProvider) {
    $stateProvider.state('codeExtend',{
        url: '/codeExtend',
        templateUrl: 'codeExtend.html',
        controller: 'codeExtendController'
        
    });
}])
    .controller('codeExtendController',[function () {
        
    }]);