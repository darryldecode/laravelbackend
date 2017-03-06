angular.module('cb.user').controller('ManageUsersController', ['$scope','$window','$timeout','GroupFactory','GlobalLoaderService','UsersFactory','AlertService', function ($scope,$window,$timeout,GroupFactory,GlobalLoaderService,UsersFactory,AlertService) {

    console.log('ManageUsersController Init');

    // the users data
    $scope.users = [];
    $scope.groups = [];
    $scope.permissions = [];

    // users pagination
    $scope.pagination = {};
    $scope.pagination.current = 1;
    $scope.pagination.perPage = 5;
    $scope.pagination.next = function () {
        UsersFactory.get({page:$scope.pagination.current,perPage:$scope.pagination.perPage}).then(function (success) {
            $scope.users = success.data.data;
        }, function (error) {
            GlobalLoaderService.show(error.data.message || 'An error has occurred.', 'danger').hide(4000);
        });
    };

    // drawer
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
        $scope.user.cancel();
    };

    /**
     * create user
     *
     * @type {{}}
     */
    $scope.user = {};
    $scope.user.isDoing = false;
    $scope.user.id = null;
    $scope.user.firstName = '';
    $scope.user.lastName = '';
    $scope.user.email = '';
    $scope.user.password = '';
    $scope.user.passwordConfirm = '';
    $scope.user.passwordConfirmMatched = false;
    $scope.user.groups = [];
    $scope.user.permissions = [];
    $scope.user.permissionKey = '';
    $scope.user.permissionValue = '';
    $scope.user.permissionsOptions = UsersFactory.getUserPermissionOptions();

    $scope.user.addPermission = function () {
        if( ($scope.user.permissionKey=='') || ($scope.user.permissionValue=='') ) return;

        $scope.user.permissions.push({
            'key': $scope.user.permissionKey,
            'value': $scope.user.permissionValue
        });
        $scope.user.permissionKey = '';
        $scope.user.permissionValue = '';
    };

    $scope.user.removePermission = function (index) {
        $scope.user.permissions.splice(index,1);
    };

    $scope.user.save = function () {

        if( ! $scope.user.passwordConfirmMatched ) return false;

        $scope.user.isDoing = true;

        UsersFactory.save({
            firstName: $scope.user.firstName,
            lastName: $scope.user.lastName,
            email: $scope.user.email,
            password: $scope.user.password,
            groups: getAssignedGroupIds($scope.groups),
            permissions: $scope.user.permissions
        }).then(function (success) {

            GlobalLoaderService.show(success.data.message,'success').hide(4000);

            // reset
            $scope.user.isDoing = false;
            $scope.user.firstName = '';
            $scope.user.lastName = '';
            $scope.user.email = '';
            $scope.user.password = '';
            $scope.user.passwordConfirm = '';
            $scope.user.passwordConfirmMatched = false;
            $scope.user.groups = [];
            $scope.user.permissions = [];
            $scope.user.permissionKey = '';
            $scope.user.permissionValue = '';

            // requery
            queryInitialData();

        }, function (error) {

            GlobalLoaderService.show(error.data.message,'danger').hide(4000);
            $scope.user.isDoing = false;

        });
    };

    $scope.user.edit = function (mode, user, index) {

        $scope.drawer.show(mode);
        $scope.user.id = user.id;
        $scope.user.firstName = user.first_name;
        $scope.user.lastName = user.last_name;
        $scope.user.email = user.email;
        $scope.user.password = null;
        $scope.user.passwordConfirm = null;
        $scope.user.passwordConfirmMatched = false;
        $scope.user.permissions = parsePermissionsFromServer(user.permissions);

        angular.forEach($scope.groups, function (group) {
            angular.forEach(user.groups, function (userGroup) {
                if(userGroup.id==group.id) {
                    group.assigned = true;
                }
            });
        });

    };

    $scope.user.update = function () {

        if( $scope.user.password != null && ($scope.user.passwordConfirm != $scope.user.password) ) {
            return false;
        }

        $scope.user.isDoing = true;

        UsersFactory.update($scope.user.id, {
            firstName: $scope.user.firstName,
            lastName: $scope.user.lastName,
            email: $scope.user.email,
            password: $scope.user.password,
            groups: getAssignedGroupIds($scope.groups),
            permissions: $scope.user.permissions
        }).then(function (success) {

            GlobalLoaderService.show(success.data.message,'success').hide(4000);

            // reset
            $scope.user.cancel();

            // re-query
            queryInitialData();

        }, function (error) {

            GlobalLoaderService.show(error.data.message,'danger').hide(4000);

        });

    };

    $scope.user.cancel = function () {

        $scope.user.isDoing = false;
        $scope.user.id = null;
        $scope.user.firstName = '';
        $scope.user.lastName = '';
        $scope.user.email = '';
        $scope.user.password = '';
        $scope.user.permissions = [];
        $scope.user.groups = [];
        angular.forEach($scope.groups, function (group) {
            group.assigned = false;
        });
        $window.cb.drawer.hide($scope.drawer.mode);

    };

    $scope.user.trash = function (userId, index) {
        AlertService.showConfirm("Are you sure you want to delete this user?", function () {
            GlobalLoaderService.show('Deleting user..','info');
            UsersFactory.trash(userId).then(function (success) {
                angular.element(".user-item-"+index).addClass('animated fadeOutRight', function(){
                    $timeout(function(){
                        $scope.users.data.splice(index,1);
                    },500);
                });
                GlobalLoaderService.show(success.data.message,'success').hide();
            }, function (error) {
                GlobalLoaderService.show(error.data.message || 'An error has occurred.','danger').hide(4500);
            });
        });
    };

    // filter results
    $scope.filter = {};
    $scope.filter.isQuerying = false;
    $scope.filter.isOpen = false;
    $scope.filter.firstName = '';
    $scope.filter.lastName = '';
    $scope.filter.email = '';
    $scope.filter.group = '';
    $scope.filter.toggle = function () {
        if( $scope.filter.isOpen ) queryInitialData();
        $scope.filter.isOpen = !$scope.filter.isOpen;
    };
    $scope.filter.filter = function () {
        $scope.filter.isQuerying = true;
        UsersFactory.get({
            perPage: $scope.pagination.perPage,
            groupId: $scope.filter.group,
            firstName: $scope.filter.firstName,
            lastName: $scope.filter.lastName,
            email: $scope.filter.email
        }).then(function (success) {
            $scope.filter.isQuerying = false;
            $scope.pagination.current = 1;
            $scope.users = success.data.data;
        }, function (error) {
            GlobalLoaderService.show(error.data.message || 'An error has occurred.', 'danger').hide(4000);
        });
    };

    // watch if password match
    $scope.$watch('user.passwordConfirm', function () {
        if( $scope.user.password == '' ) return;
        $scope.user.passwordConfirmMatched = ($scope.user.password == $scope.user.passwordConfirm);
    });

    // Query Initial Data
    function queryInitialData() {
        UsersFactory.get({perPage:$scope.pagination.perPage}).then(function (success) {
            $scope.pagination.current = 1;
            $scope.users = success.data.data;
        }, function (error) {
            GlobalLoaderService.show(error.data.message || 'An error has occurred.', 'danger').hide(4000);
        });
        UsersFactory.getAvailablePermissions().then(function (success) {
            $scope.permissions = success.data.data;
        });
        GroupFactory.get().then(function (success) {
            $scope.groups = success.data.data;
        }, function (error) {
            GlobalLoaderService.show(error.data.message || "An error has occurred.", "danger").hide(3500);
        });
    }
    queryInitialData();

    // extract Id's from groups who are marked as assigned to the user
    function getAssignedGroupIds (groups) {
        var assignedGroupIds = [];
        angular.forEach(groups, function(group, i) {
            if ( group.assigned == true ) {
                assignedGroupIds.push(group.id)
            }
        });
        return assignedGroupIds;
    }

    // parse user permission's format from server to be easily manipulated in client
    function parsePermissionsFromServer (permissions) {
        var p = [];
        for (var prop in permissions) {
            if (permissions.hasOwnProperty(prop)) {
                p.push({
                    'key': prop,
                    'value': permissions[prop]
                });
            }
        }
        return p;
    }

}]);
