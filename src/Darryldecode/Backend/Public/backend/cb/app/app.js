angular.module('cb',[

    // components
    'ngCookies',
    'ngSanitize',

    // 3rd party
    'ui.tinymce',
    'ui.ace',
    'ui.bootstrap',
    'ui.select',
    'ui.date',
    'ui.sortable',
    'ui.tree',
    'frapontillo.bootstrap-switch',
    'angular-loading-bar',
    'angular.filter',
    'angularFileUpload',

    // common
    'cb.services',
    'cb.filters',
    'cb.directives',

    // app
    'cb.group',
    'cb.user',
    'cb.content',
    'cb.customFields',
    'cb.mediaManager',
    'cb.navigation',
    'cb.dashboard'
])

    .constant('GITHUB_API', 'https://api.github.com/repos/darryldecode/laravelbackend')
    .constant('ADMIN_URL', ADMIN_URL)
    .constant('BASE_URL', BASE_URL)
    .constant('STORAGE_URL', STORAGE_URL)
    .constant('_CSRF', _CSRF)
    .constant('USER_ID', angular.element("#backend-wrapper").data("user-id"))

    .config(['$httpProvider','_CSRF',function ($httpProvider, _CSRF) {
        $httpProvider.defaults.headers.common['X-CSRF-TOKEN']       = _CSRF;
        $httpProvider.defaults.headers.common['X-Requested-With']   = 'XMLHttpRequest';
    }])

    .controller('MasterController', ['$scope','NavigationFactory','GlobalLoaderService','STORAGE_URL', function($scope, NavigationFactory, GlobalLoaderService, STORAGE_URL) {

        console.log('MasterController Init');

        // make some constants available through out the application
        // without needing to inject to each sub controllers
        $scope.STORAGE_URL = STORAGE_URL;
        $scope.BASE_URL = BASE_URL;
        $scope._CSRF = _CSRF;

        // the global navigation
        $scope.navigation = [];

        // get navigation
        NavigationFactory.list({}).then(function (success) {
            $scope.navigation = success.data.data;
        }, function (error) {
            GlobalLoaderService.show('Failed to load navigation.','danger').hide(4000);
        });

        // init tooltip
        angular.element('a, .tooltip-init').tooltip();
    }]);