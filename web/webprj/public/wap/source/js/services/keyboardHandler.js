/**
 * Created by chaoshen on 2017/2/10.
 */
angular.module('ctfApp.keyboardHandler',[])
    .directive('keyboardHandler', ['$window','$rootScope',function ($window,$rootScope) {
        return {
            restrict: 'A',
            link: function postLink(scope, element, attrs) {
                angular.element($window).bind('native.keyboardshow', function() {
                    console.log(">>>..");
                    // alert(">>>>");
                    $rootScope.hideTabs = true;
                    // element.addClass('tabs-item-hide');
                });
                
                angular.element($window).bind('native.keyboardhide', function() {
                    // element.removeClass('tabs-item-hide');
                    $rootScope.hideTabs = false;
                });
            }
        };
    }]);