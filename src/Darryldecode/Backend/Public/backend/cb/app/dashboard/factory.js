angular.module('cb.dashboard').factory('DashboardFactory',['$http','GITHUB_API', function ($http, GITHUB_API) {

    /**
     * get users
     *
     * @param params
     * @returns {*}
     */
    this.getReleases = function () {

        // we need to remove this so because we are not calling ajax on our
        // own application and GitHub denies this.
        delete $http.defaults.headers.common['X-CSRF-TOKEN'];

        return $http({
            url: GITHUB_API+'/releases',
            method: 'GET'
        });
    };

    return this;
}]);