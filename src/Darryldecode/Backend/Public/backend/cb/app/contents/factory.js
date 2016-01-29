angular.module('cb.content').factory('ContentFactory',['$http',function($http) {

    /**
     * get content types
     *
     * @returns {*}
     */
    this.getTypes = function (params) {
        return $http({
            url: ADMIN_URL+'/content_types',
            method: 'GET',
            params: params
        });
    };

    /**
     * get contents by type
     *
     * @param type
     * @param params
     * @returns {*}
     */
    this.getContentsByType = function (type, params) {
        return $http({
            url: ADMIN_URL+'/contents/'+type,
            method: 'GET',
            params: params
        });
    };

    /**
     * delete content type
     *
     * @returns {*}
     */
    this.deleteType = function (id) {
        return $http({
            url: ADMIN_URL+'/content_types/'+id,
            method: 'DELETE'
        });
    };

    /**
     * get terms by taxonomyId
     *
     * @returns {*}
     */
    this.getTermsByTaxonomy = function (taxonomyId) {
        return $http({
            url: ADMIN_URL+'/taxonomy_terms',
            method: 'GET',
            params: {
                taxonomyId: taxonomyId
            }
        });
    };

    /**
     * post create content type
     *
     * @returns {*}
     */
    this.createType = function (data) {
        return $http({
            url: ADMIN_URL+'/content_types',
            method: 'POST',
            data: data
        });
    };

    /**
     * create content taxonomy
     *
     * @param data
     * @returns {*}
     */
    this.createTaxonomy = function (data) {
        return $http({
            url: ADMIN_URL+'/content_taxonomies',
            method: 'POST',
            data: data
        });
    };

    /**
     * delete content taxonomy
     *
     * @param taxonomyId int
     * @returns {*}
     */
    this.deleteTaxonomy = function (taxonomyId) {
        return $http({
            url: ADMIN_URL+'/content_taxonomies/'+taxonomyId,
            method: 'DELETE'
        });
    };

    /**
     * create content taxonomy terms
     *
     * @param data
     * @returns {*}
     */
    this.createTaxonomyTerm = function (data) {
        return $http({
            url: ADMIN_URL+'/taxonomy_terms',
            method: 'POST',
            data: data
        });
    };

    /**
     * delete a taxonomy term
     *
     * @param termId
     * @param taxonomyId
     * @returns {*}
     */
    this.deleteTaxonomyTerm = function (termId, taxonomyId) {
        return $http({
            url: ADMIN_URL+'/taxonomy_terms/'+termId+'?taxonomyId='+taxonomyId,
            method: 'DELETE'
        });
    };

    /**
     * create new content entry
     *
     * @param data
     * @returns {*}
     */
    this.createContent = function (data) {
        return $http({
            url: ADMIN_URL+'/contents',
            method: 'POST',
            data: data
        });
    };

    /**
     * updates content entry
     *
     * @param id
     * @param data
     * @returns {*}
     */
    this.updateContent = function (id, data) {
        return $http({
            url: ADMIN_URL+'/contents/'+id,
            method: 'PUT',
            data: data
        });
    };

    /**
     * the content status options, should be in sync with backend App\Backend\Components\ContentBuilder\Models\Content
     *
     * @returns {{label: string, value: string}[]}
     */
    this.getContentStatusOptions = function () {
        return [
            {label: "Published", value: "published"},
            {label: "Draft", value: "draft"}
        ]
    };

    /**
     * delete content by Id
     *
     * @param id
     * @returns {*}
     */
    this.trash = function (id) {
        return $http({
            url: ADMIN_URL+'/contents/'+id,
            method: 'DELETE'
        });
    };

    return this;

}]);
