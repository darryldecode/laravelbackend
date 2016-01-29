angular.module('cb.navigation').factory('NavigationFactory',['$http',function($http) {

    /**
     * list navigation, this is the main navigation of the backend builder application
     *
     * @param params
     * @returns {*}
     */
    this.list = function (params) {
        return $http({
            url: ADMIN_URL+'/navigation',
            method: 'GET',
            params: params
        });
    };

    /**
     * list custom navigation, this are the custom navigation created
     *
     * @param params
     * @returns {*}
     */
    this.listCustomNavigation = function (params) {
        return $http({
            url: ADMIN_URL+'/navigation/builder',
            method: 'GET',
            params: params
        });
    };

    /**
     * creates new custom navigation
     *
     * @param data
     * @returns {*}
     */
    this.create = function (data) {
        return $http({
            url: ADMIN_URL+'/navigation/builder',
            method: 'POST',
            data: data
        });
    };

    /**
     * creates new custom navigation
     *
     * @param id
     * @param data
     * @returns {*}
     */
    this.update = function (id, data) {
        return $http({
            url: ADMIN_URL+'/navigation/builder/'+id,
            method: 'PUT',
            data: data
        });
    };

    /**
     * delete custom navigation by ID
     *
     * @param id
     * @returns {*}
     */
    this.trash = function (id) {
        return $http({
            url: ADMIN_URL+'/navigation/builder/'+id,
            method: 'DELETE'
        });
    };

    return this;
}]);