angular.module('cb.group').factory('GroupFactory',['$http',function($http) {

    /**
     * the available group permissions value
     *
     * @returns {{label: string, value: number}[]}
     */
    this.getGroupPermissionOptions = function () {
        return [
            {label: "Deny", value: -1},
            {label: "Allow", value: 1}
        ];
    };

    /**
     * save group
     *
     * @param data
     * @returns {*}
     */
    this.save = function (data) {
        return $http({
            url: ADMIN_URL+'/groups',
            method: 'POST',
            data: data
        });
    };

    /**
     * update group
     *
     * @param id
     * @param data
     * @returns {*}
     */
    this.update = function (id, data) {
        return $http({
            url: ADMIN_URL+'/groups/'+id,
            method: 'PUT',
            data: data
        });
    };

    /**
     * get groups
     *
     * @returns {*}
     */
    this.get = function (params) {
        return $http({
            url: ADMIN_URL+'/groups',
            method: 'GET',
            params: params
        });
    };

    /**
     * delete group
     *
     * @returns {*}
     */
    this.trash = function (id) {
        return $http({
            url: ADMIN_URL+'/groups/'+id,
            method: 'DELETE'
        });
    };

    return this;

}]);