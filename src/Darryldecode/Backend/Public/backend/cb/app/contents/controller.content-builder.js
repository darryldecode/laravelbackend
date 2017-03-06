angular.module('cb.group').controller('ContentBuilderController', ['$scope','$timeout','$window','$filter','ContentFactory','GlobalLoaderService','AlertService','TaxonomyTreeViewService', function ($scope,$timeout,$window,$filter,ContentFactory,GlobalLoaderService,AlertService,TaxonomyTreeViewService) {

    console.log('ContentBuilderController Init');

    // content type data
    $scope.contentTypes = [];

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
        resetContentTypeObjectState();
        resetTaxonomiesObjectState();
        resetTermsObjectState();
    };

    /**
     * Content Type Creation
     *
     * @type {{}}
     */
    $scope.contentType = {};
    $scope.contentType.type = '';
    $scope.contentType.enableRevision = 'no';
    $scope.contentType.isDoing = false;

    // saves the new content type
    $scope.contentType.save = function () {

        if( $scope.contentType.type == '' ) return false;

        $scope.contentType.isDoing = true;
        ContentFactory.createType({
            type: $scope.contentType.type,
            enableRevision: $scope.contentType.enableRevision
        }).then(function (success) {
            $scope.contentType.isDoing = false;
            GlobalLoaderService.show(success.data.message,'success').hide(4000);
            queryInitialData();
            $scope.drawer.hide($scope.drawer.mode);

            // ask for browser refresh
            AlertService.showConfirm("A page refresh is needed in order for the new Content Type to be visible on Contents Navigation. Refresh now?", function() {
                location.reload();
            });
        }, function (error) {
            $scope.contentType.isDoing = false;
            GlobalLoaderService.show(error.data.message || 'An error has occurred.','danger').hide(4000);
        });
    };

    // close create content type panel
    $scope.contentType.cancel = function () {
        resetContentTypeObjectState();
    };

    // trash a content type
    $scope.contentType.trash = function (id, index) {
        AlertService.showConfirm("Do you really want to delete this Content Type?", function () {
           ContentFactory.deleteType(id).then(function (success) {
               $scope.contentTypes.splice(index,1);
           }, function (error) {
                GlobalLoaderService.show(error.data.message || 'An error has occurred.','danger').hide(3500);
           });
        });
    };

    $scope.$watch('contentType.type', function () {
        $scope.contentType.type = $filter('slugify')($scope.contentType.type,'_');
    });

    /**
     * Taxonomy configure
     *
     * @type {{}}
     */
    $scope.taxonomy = {};
    $scope.taxonomy.cType = null;
    $scope.taxonomy.taxonomies = [];
    $scope.taxonomy.taxName = '';
    $scope.taxonomy.taxDescription = '';
    $scope.taxonomy.isDoing = false;

    // shows the configuration of taxonomy panel
    // this will enable to add/edit/remove terms of a taxonomy
    $scope.taxonomy.configure = function (index, mode) {
        $scope.drawer.show(mode);
        $scope.taxonomy.cType = $scope.contentTypes[index];
        $scope.taxonomy.taxonomies = $scope.contentTypes[index].taxonomies;
    };

    // saves a new taxonomy
    $scope.taxonomy.add = function () {

        if( $scope.taxonomy.taxName == '' ) return false;

        $scope.taxonomy.isDoing = true;

        ContentFactory.createTaxonomy({
            taxonomy: $scope.taxonomy.taxName,
            description: $scope.taxonomy.taxDescription,
            contentTypeId: $scope.taxonomy.cType.id
        }).then(function (success) {
            $scope.taxonomy.taxonomies.push(success.data.data);
            $scope.taxonomy.isDoing = false;
            $scope.taxonomy.taxName = '';
            $scope.taxonomy.taxDescription = '';
            $scope.terms.active = false; // reset term add view
        }, function(error) {
            GlobalLoaderService.show(error.data.message || 'An error has occurred.','danger').hide(4000);
            $scope.taxonomy.isDoing = false;
        });

    };

    // deletes a taxonomy
    $scope.taxonomy.trash = function (taxonomyId, index) {

        AlertService.showConfirm("Are you sure you want to delete this taxonomy?", function () {

            $scope.taxonomy.isDoing = true;

            ContentFactory.deleteTaxonomy(taxonomyId).then(function (success) {
                $scope.taxonomy.isDoing = false;
                $scope.taxonomy.taxonomies.splice(index,1);
                resetTermsObjectState();
            }, function (error) {
                $scope.taxonomy.isDoing = false;
                GlobalLoaderService.show(error.data.message || 'An error has occurred.','danger').hide(4000);
            });

        });
    };

    /**
     * terms
     *
     * @type {{}}
     */
    $scope.terms = {};
    $scope.terms.taxonomy = null;
    $scope.terms.terms = [];
    $scope.terms.isDoing = false;
    $scope.terms.name = '';
    $scope.terms.slug = '';

    // shows control panel where you can add terms to a taxonomy
    $scope.terms.showAdd = function (taxonomy) {

        $scope.terms.taxonomy = taxonomy;
        $scope.terms.isDoing = true;

        ContentFactory.getTermsByTaxonomy(taxonomy.id).then(function (success) {
            $scope.terms.terms = success.data.data;
            $scope.terms.isDoing = false;
        }, function (error) {
            GlobalLoaderService.show(error.data.message || 'An error has occurred.','danger').hide(3500);
        });
    };

    // adds the new term to the specified taxonomy
    $scope.terms.save = function () {

        if( $scope.terms.name == '' || $scope.terms.slug == '' ) return false;

        $scope.terms.isDoing = true;

        ContentFactory.createTaxonomyTerm({
            term: $scope.terms.name,
            slug: $scope.terms.slug,
            contentTypeTaxonomyId: $scope.terms.taxonomy.id
        }).then(function (success) {
            $scope.terms.terms.push(success.data.data);
            $scope.terms.isDoing = false;
            $scope.terms.name = '';
            $scope.terms.slug = '';
        }, function (error) {
            GlobalLoaderService.show(error.data.message || 'An error has occurred.','danger').hide(3500);
        });
    };

    // deletes a term
    $scope.terms.trash = function(termId, index) {
        AlertService.showConfirm("Are you sure you want to delete this term?", function() {
            GlobalLoaderService.show("Deleting term..","info");
            ContentFactory.deleteTaxonomyTerm(termId, $scope.terms.taxonomy.id).then(function(success) {
                $scope.terms.terms.splice(index,1);
                GlobalLoaderService.show(success.data.message,"success").hide(3500);
            }, function(error) {
                GlobalLoaderService.show(error.data.message || 'An error has occurred.','danger').hide(3500);
            });
        });
    };

    // this will watch the term name and automatically produces slug for that term
    $scope.$watch('terms.name', function () {
        $scope.terms.slug = $filter('slugify')($scope.terms.name);
    });

    // resets the fields of the content type
    function resetContentTypeObjectState () {
        $scope.contentType.type = '';
        $scope.contentType.enableRevision = 'no';
        $scope.contentType.isDoing = false;
    }

    // resets the field of the taxonomy
    function resetTaxonomiesObjectState () {
        $scope.taxonomy.cType = null;
        $scope.taxonomy.taxonomies = [];
        $scope.taxonomy.taxName = '';
        $scope.taxonomy.taxDescription = '';
        $scope.taxonomy.isDoing = false;
    }

    // resets the field of a term
    function resetTermsObjectState () {
        $scope.terms.taxonomy = null;
        $scope.terms.terms = [];
        $scope.terms.active = false;
        $scope.terms.isDoing = false;
        $scope.terms.name = '';
        $scope.terms.slug = '';
    }

    // query initial data
    function queryInitialData() {
        ContentFactory.getTypes().then(function (success) {
            $scope.contentTypes = success.data.data;
        }, function (error) {
            GlobalLoaderService.show(error.data.message || 'An error has occurred.','danger').hide(4500);
        });
    }
    queryInitialData();
}]);