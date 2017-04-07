angular.module('cb.media').controller('MediaController', ['$scope','$timeout','$window','$filter','$modal','GlobalLoaderService','AlertService','MediaFactory','_CSRF', function ($scope,$timeout,$window,$filter,$modal,GlobalLoaderService,AlertService,MediaFactory,_CSRF) {

    console.log('MediaController Init');

    // files
    $scope.media = {

        init: function() {

            var opts = {
                url : '/backend/media/elFinder',
                customData: {'_token': _CSRF}
            };

            angular.element('#elfinder').elfinder(opts);
        }
    };

    $scope.media.init();
}]);