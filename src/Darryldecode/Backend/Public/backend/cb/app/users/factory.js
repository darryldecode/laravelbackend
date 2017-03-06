angular.module('cb.user').factory('UsersFactory',['$http', function ($http) {

    /**
     * the available user permissions value
     *
     * @returns {{label: string, value: number}[]}
     */
    this.getUserPermissionOptions = function () {
        return [
            {label: "Deny", value: -1},
            {label: "Allow", value: 1},
            {label: "Inherit", value: 0}
        ];
    };

    /**
     * get users
     *
     * @param params
     * @returns {*}
     */
    this.get = function (params) {
        return $http({
            url: ADMIN_URL+'/users',
            method: 'GET',
            params: params
        });
    };

    /**
     * get available permissions
     *
     * @returns {*}
     */
    this.getAvailablePermissions = function () {
        return $http({
            url: ADMIN_URL+'/users/available_permissions',
            method: 'GET'
        });
    };

    /**
     * save user
     *
     * @param data
     * @returns {*}
     */
    this.save = function (data) {
        return $http({
            url: ADMIN_URL+'/users',
            method: 'POST',
            data: data
        });
    };

    /**
     * save user
     *
     * @param data
     * @returns {*}
     */
    this.update = function (id, data) {
        return $http({
            url: ADMIN_URL+'/users/'+id,
            method: 'PUT',
            data: data
        });
    };

    /**
     * delete user
     *
     * @param id
     * @returns {*}
     */
    this.trash = function (id) {
        return $http({
            url: ADMIN_URL+'/users/'+id,
            method: 'DELETE'
        });
    };

    return this;
}]);