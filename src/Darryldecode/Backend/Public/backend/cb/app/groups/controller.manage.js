angular.module('cb.group').controller('ManageGroupsController', ['$scope','$timeout','GroupFactory','UsersFactory','GlobalLoaderService','AlertService', function ($scope,$timeout,GroupFactory,UsersFactory,GlobalLoaderService,AlertService) {

    console.log('ManageGroupsController Init');

    // the groups
    $scope.groups = [];
    $scope.permissions = [];

    /**
     * edit group
     *
     * @type {{}}
     */
    $scope.edit = {};
    $scope.edit.isUpdating = false;
    $scope.edit.isEditMode = false;

    $scope.edit.edit = function (group) {

        $scope.edit.isEditMode = true;

        $scope.group.id = group.id;
        $scope.group.name = group.name;
        $scope.group.permissions = transformPermissionsToRaw(group.permissions);

    };

    $scope.edit.cancel = function () {
        $scope.group.id = null;
        $scope.group.name = '';
        $scope.group.permissions = [];
        $scope.edit.isEditMode = false;
    };

    $scope.edit.update = function () {
        $scope.edit.isUpdating = true;
        GroupFactory.update($scope.group.id, {
            name: $scope.group.name,
            permissions: $scope.group.permissions
        }).then(function (success) {
            $scope.group.id = null;
            $scope.group.name = '';
            $scope.group.permissions = [];
            $scope.edit.isEditMode = false;
            queryInitialData();
            GlobalLoaderService.show(success.data.message, 'success').hide(3500);
            $scope.edit.isUpdating = false;
        }, function (error) {
            GlobalLoaderService.show(error.data.message, 'danger').hide(4000);
            $scope.edit.isUpdating = false;
        });
    };

    /**
     * create group
     *
     * @type {{}}
     */
    $scope.group = {};
    $scope.group.id = null;
    $scope.group.name = '';
    $scope.group.permissions = [];
    $scope.group.permissionKey = '';
    $scope.group.permissionValue = '';
    $scope.group.permissionsOptions = GroupFactory.getGroupPermissionOptions();
    $scope.group.isSaving = false;

    $scope.group.add = function () {

        if( ($scope.group.permissionKey=='') || ($scope.group.permissionValue=='') ) return;

        $scope.group.permissions.push({
            'key': $scope.group.permissionKey,
            'value': $scope.group.permissionValue
        });
        $scope.group.permissionKey = '';
        $scope.group.permissionValue = '';
    };

    $scope.group.remove = function (index) {
        $scope.group.permissions.splice(index,1);
    };

    $scope.group.save = function () {

        $scope.group.isSaving = true;

        GroupFactory.save({
            name: $scope.group.name,
            permissions: $scope.group.permissions
        }).then(function(success) {
            $scope.groups.push(success.data.data);
            $scope.group.name = '';
            $scope.group.permissions = [];
            $scope.group.isSaving = false;
            GlobalLoaderService.show(success.data.message, "success").hide(3500);
        }, function (error) {
            $scope.group.isSaving = false;
            GlobalLoaderService.show(error.data.message || "An error has occurred.", "danger").hide(3500);
        })
    };

    $scope.group.trash = function (groupId, index) {
        AlertService.showConfirm("Are you sure you want to delete this group?", function () {
            GlobalLoaderService.show('Deleting group..','info');
            GroupFactory.trash(groupId).then(function (success) {
                angular.element(".group-item-"+index).addClass('animated flash', function(){
                    $timeout(function(){
                        $scope.groups.splice(index,1);
                    },500);
                });
                GlobalLoaderService.show(success.data.message,'success').hide();
            }, function (error) {
                GlobalLoaderService.show(error.data.message || 'An error has occurred.','danger').hide(4500);
            });
        });
    };

    /**
     * initial data load
     */
    function queryInitialData() {
        GroupFactory.get().then(function (success) {
            $scope.groups = success.data.data;
        }, function (error) {
            GlobalLoaderService.show(error.data.message || "An error has occurred.", "danger").hide(3500);
        });
        UsersFactory.getAvailablePermissions().then(function (success) {
            $scope.permissions = success.data.data;
        });
    }
    queryInitialData();

    // transforms permissions to key,value format so we can manipulate it easily
    function transformPermissionsToRaw(permissions)
    {
        var r = [];

        for (var prop in permissions) {
            if ( permissions.hasOwnProperty(prop) ) {
                r.push({
                    key: prop,
                    value: permissions[prop]
                })
            }
        }

        return r;
    }

}]);