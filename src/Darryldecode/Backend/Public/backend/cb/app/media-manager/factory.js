angular.module('cb.mediaManager').factory('MediaManagerFactory',['$http','$upload','$window',function($http,$upload,$window) {

    /**
     * list directory
     *
     * @param params
     * @returns {*}
     */
    this.ls = function (params) {
        return $http({
            url: ADMIN_URL+'/media_manager',
            method: 'GET',
            params: params
        });
    };

    /**
     * make directory
     *
     * @param data
     * @returns {*}
     */
    this.mkDir = function (data) {
        return $http({
            url: ADMIN_URL+'/media_manager/mkDir',
            method: 'POST',
            data: data
        });
    };

    /**
     * uploads a file
     *
     * @param file
     * @param fields
     */
    this.upload = function (file, fields) {
        return $upload.upload({
            url: ADMIN_URL+'/media_manager/upload',
            fields: fields,
            file: file
        });
    };

    /**
     * delete a file(s)
     *
     * @param params
     * @returns {*}
     */
    this.rm = function (params) {
        return $http({
            url: ADMIN_URL+'/media_manager/rm',
            method: 'DELETE',
            params: params
        });
    };

    /**
     * delete a file(s)
     *
     * @param params
     * @returns {*}
     */
    this.rmrf = function (params) {
        return $http({
            url: ADMIN_URL+'/media_manager/rmrf',
            method: 'DELETE',
            params: params
        });
    };

    /**
     * move a file to a new location
     *
     * @param data
     * @returns {*}
     */
    this.mv = function (data) {
        return $http({
            url: ADMIN_URL+'/media_manager/mv',
            method: 'POST',
            data: data
        });
    };

    /**
     * download a file
     *
     * @param path
     * @returns {*}
     */
    this.download = function (path) {
        $window.open(
            ADMIN_URL + '/media_manager/download?path=' + path + '&token=' + _CSRF,
            '_blank'
        );
    };

    return this;
}]);