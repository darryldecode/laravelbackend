angular.module('cb.navigation').controller('NavigationBuilderController', ['$scope','$window','$timeout','NavigationFactory','GlobalLoaderService','AlertService', function ($scope,$window,$timeout,NavigationFactory,GlobalLoaderService,AlertService) {

    console.log('NavigationBuilderController Init');

    /**
     * Drawer
     *
     * @type {{}}
     */
    $scope.drawer = {};
    $scope.drawer.panel = angular.element(".drawer");
    $scope.drawer.mode = null;
    $scope.drawer.open = false;
    $scope.drawer.show = function (mode) {
        $scope.drawer.mode = mode;
        $window.cb.drawer.show(mode);
    };
    $scope.drawer.hide = function (mode) {
        $scope.drawer.mode = null;
        $window.cb.drawer.hide(mode);
        resetNavigationFields();
    };

    // the custom created navigation list
    $scope.customNavigationList = [];

    /**
     * The Navigation to be created
     *
     * @type {{}}
     */
    $scope.navigation = {};
    $scope.navigation.id = null;
    $scope.navigation.items = [{
        title: "Sample Navigation",
        attr: {
            class: "some-class",
            id: "some-id"
        },
        url: "http://www.example.com",
        newTab: false,
        items: []
    }];
    $scope.navigation.name = '';
    $scope.navigation.title = '';
    $scope.navigation.attr = {
        class: '',
        id: ''
    };
    $scope.navigation.url = '';
    $scope.navigation.newTab = false;
    $scope.navigation.currentWorkingItem = null;
    $scope.navigation.isEditMode = false;
    $scope.navigation.isDoing = false;

    // edit the navigation
    $scope.navigation.edit = function (item) {

        // the item we are currently working on
        $scope.navigation.currentWorkingItem = item;
        $scope.navigation.isEditMode = true;

        // assign the model
        $scope.navigation.isEditMode = true;
        $scope.navigation.title = item.title;
        $scope.navigation.attr.class = item.attr.class;
        $scope.navigation.attr.id = item.attr.id;
        $scope.navigation.url = item.url;
        $scope.navigation.newTab = item.newTab;
    };

    // update the navigation entry
    $scope.navigation.update = function () {

        // make sure we have something to work on
        if(!$scope.navigation.currentWorkingItem) return;

        // update
        $scope.navigation.currentWorkingItem.title = $scope.navigation.title;
        $scope.navigation.currentWorkingItem.attr.class = $scope.navigation.attr.class;
        $scope.navigation.currentWorkingItem.attr.id = $scope.navigation.attr.id;
        $scope.navigation.currentWorkingItem.url = $scope.navigation.url;
        $scope.navigation.currentWorkingItem.newTab = $scope.navigation.newTab;

        // reset
        $scope.navigation.currentWorkingItem = null;
        $scope.navigation.isEditMode = false;
        $scope.navigation.title = '';
        $scope.navigation.attr.class = '';
        $scope.navigation.attr.id = '';
        $scope.navigation.url = '';
        $scope.navigation.newTab = false;
    };

    // cancel current operation
    $scope.navigation.cancel = function () {
        $scope.navigation.currentWorkingItem = null;
        $scope.navigation.isEditMode = false;
        $scope.navigation.title = '';
        $scope.navigation.attr.class = '';
        $scope.navigation.attr.id = '';
        $scope.navigation.url = '';
        $scope.navigation.newTab = false;
    };

    // adds new item
    $scope.navigation.addChild = function (item) {
        item.items.push({
            id: item.items.length + 1,
            title: item.title,
            attr: {
                class: item.attr.class,
                id: item.attr.id
            },
            url: item.url,
            newTab: item.newTab,
            items: []
        });
    };

    // configure click handle
    $scope.navigation.configure = function (nav, drawer) {
        $scope.drawer.show(drawer);
        $scope.navigation.id = nav.id;
        $scope.navigation.name = nav.name;
        $scope.navigation.items = nav.data;
    };

    // handle configure update
    $scope.navigation.configureUpdate = function () {

        $scope.navigation.isDoing = true;

        NavigationFactory.update(
            $scope.navigation.id,
            {
                name: $scope.navigation.name,
                data: $scope.navigation.items
            }
        ).then(function(success) {
                $scope.navigation.isDoing = false;
                queryCustomNavigationList({});
                resetNavigationFields();
                $scope.drawer.hide($scope.drawer.mode);
        }, function(error) {
                $scope.navigation.isDoing = false;
                GlobalLoaderService.show(error.data.message || 'An error has occurred.','danger').hide(3500);
        });
    };

    // remove an item
    $scope.navigation.remove = function (scope) {

        // do not allow if only 1 item remaining
        if( $scope.navigation.items.length == 1 ) return false;

        scope.remove();
    };

    // save navigation
    $scope.navigation.save = function () {

        $scope.navigation.isDoing = true;

        NavigationFactory.create({
            name: $scope.navigation.name,
            data: $scope.navigation.items
        }).then(function (success) {
            queryCustomNavigationList({});
            $scope.navigation.isDoing = false;
            $scope.navigation.cancel(); // same effect as it would clear fields
            $scope.drawer.hide($scope.drawer.mode);
        }, function (error) {
            $scope.navigation.isDoing = false;
            GlobalLoaderService.show(error.data.message || 'An error has occurred.','danger').hide(3500);
        });
    };

    // delete a custom navigation
    $scope.navigation.trashing = false;
    $scope.navigation.trash = function (id) {

        AlertService.showConfirm("Are you sure you want to delete this navigation?", function() {

            $scope.navigation.trashing = true;

            NavigationFactory.trash(id).then(function (success) {
                $scope.navigation.trashing = false;
                queryCustomNavigationList({});
            }, function (error) {
                $scope.navigation.trashing = false;
                GlobalLoaderService.show(error.data.message || 'An error has occurred.','danger').hide(3500);
            });
        });
    };

    // query custom navigation
    function queryCustomNavigationList (params) {
        NavigationFactory.listCustomNavigation(params).then(function (success) {
            $scope.customNavigationList = success.data.data;
        }, function (error) {
            GlobalLoaderService.show(error.data.message || 'An error has occurred.','danger').hide(3500);
        });
    }
    queryCustomNavigationList({});

    // reset navigation fields
    function resetNavigationFields () {
        $scope.navigation.id = null;
        $scope.navigation.items = [{
            title: "Sample Navigation",
            attr: {
                class: "some-class",
                id: "some-id"
            },
            url: "http://www.example.com",
            newTab: false,
            items: []
        }];
        $scope.navigation.name = '';
        $scope.navigation.title = '';
        $scope.navigation.attr = {
            class: "",
            id: ""
        };
        $scope.navigation.url = '';
        $scope.navigation.newTab = false;
        $scope.navigation.currentWorkingItem = null;
        $scope.navigation.isEditMode = false;
        $scope.navigation.isDoing = false;
    }
}]);