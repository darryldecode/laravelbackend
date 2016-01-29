angular.module('cb.group').controller('ContentsController', ['$scope','$timeout','$window','$filter','$modal','ContentFactory','GlobalLoaderService','AlertService','USER_ID', function ($scope,$timeout,$window,$filter,$modal,ContentFactory,GlobalLoaderService,AlertService,USER_ID) {

    console.log('ContentsController Init');

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

        // lets give about 2 secs to wait before we init textarea that
        // are meant for mark down
        $timeout(function () {
            angular.element(".textarea-md").markdown({
                autofocus: false,
                savable: false,
                iconlibrary: 'fa'
            });
        }, 2000);
    };
    $scope.drawer.hide = function (mode) {
        $scope.drawer.mode = null;
        $window.cb.drawer.hide(mode);
        resetContentState();
    };

    // the content type
    $scope.contentType = {};
    $scope.contentType.id = angular.element("#inner-content").data("content-type");

    // the content entries
    $scope.contents = {};

    // the content status options
    $scope.contentStatusOptions = ContentFactory.getContentStatusOptions();

    // pagination
    $scope.pagination = {};
    $scope.pagination.page = 1;
    $scope.pagination.next = function () {
        queryContentsByType($scope.contentType.id, {page: $scope.pagination.page});
    };

    /**
     * Content
     *
     * This object is used when creating and editing a content
     *
     * @type {{}}
     */
    $scope.content = {};
    $scope.content.id = null;
    $scope.content.title = '';
    $scope.content.slug = '';
    $scope.content.body = '';
    $scope.content.status = 'published';
    $scope.content.permissionRequirements = '';
    $scope.content.taxonomies = {};
    $scope.content.customFields = {};
    $scope.content.isDoing = false;

    // saves new content
    $scope.content.save = function () {

        var validation = checkRequiredFields();

        if( ! validation.success ) {
            AlertService.showAlert(validation.message);
            return false;
        }

        var dataToBeSave = {
            title: $scope.content.title,
            slug: $scope.content.slug,
            body: $scope.content.body,
            status: $scope.content.status,
            permissionRequirements: $scope.content.permissionRequirements,
            contentTypeId: $scope.contentType.id,
            authorId: USER_ID,
            taxonomies: $scope.content.taxonomies,
            metaData: $scope.content.customFields,
            miscData: {
                editor: $scope.editor.mode
            }
        };

        $scope.content.isDoing = true;

        ContentFactory.createContent(dataToBeSave).then(function(success) {
            location.reload();
        }, function (error) {
            GlobalLoaderService.show(error.data.message || 'An error has occurred.','danger').hide(3500);
            $scope.content.isDoing = false;
        });
    };

    // show update panel
    $scope.content.update = function (content, drawer) {
        $scope.content.id = content.id;
        $scope.content.title = content.title;
        $scope.content.slug = content.slug;
        $scope.content.body = content.body;
        $scope.content.status = content.status;
        $scope.content.permissionRequirements = content.permission_requirements;
        $scope.content.taxonomies = parseTaxonomiesForEdit(content.terms);
        $scope.content.customFields = parseCustomFieldsForEdit(content.meta_data);
        $scope.editor.mode = content.misc_data.editor;
        $scope.drawer.show(drawer);
    };

    // update content
    $scope.content.updateSave = function () {

        var validation = checkRequiredFields();

        if( ! validation.success ) {
            AlertService.showAlert(validation.message);
            return false;
        }

        var dataToBeUpdated = {
            title: $scope.content.title,
            slug: $scope.content.slug,
            body: $scope.content.body,
            status: $scope.content.status,
            permissionRequirements: $scope.content.permissionRequirements,
            contentTypeId: $scope.contentType.id,
            authorId: USER_ID,
            taxonomies: $scope.content.taxonomies,
            metaData: $scope.content.customFields,
            miscData: {
                editor: $scope.editor.mode
            }
        };

        $scope.content.isDoing = true;

        ContentFactory.updateContent($scope.content.id, dataToBeUpdated).then(function (success) {
            resetContentState();
            queryContentsByType($scope.contentType.id, {});
            $scope.drawer.hide($scope.drawer.mode);
            $scope.content.isDoing = false;
        }, function (error) {
            GlobalLoaderService.show(error.data.message || 'An error has occurred.','danger').hide(3500);
            $scope.content.isDoing = false;
        });
    };

    // delete a content
    $scope.content.trash = function (id, index) {
        AlertService.showConfirm("Are you sure you want to delete this entry?", function () {
            ContentFactory.trash(id).then(function (success) {
                angular.element(".content-item-"+index).addClass('animated fadeOutRight', function () {
                    $timeout(function(){
                        $scope.contents.data.splice(index,1);
                    },500);
                });
            }, function (error) {
                GlobalLoaderService.show(error.data.message || 'An error has occurred.','danger').hide(3500);
            });
        });
    };

    $scope.$watch('content.title', function () {
        $scope.content.slug = $filter('slugify')($scope.content.title);
    });

    /**
     * Gallery Custom Field
     *
     * if there is a gallery custom field to the current content,
     * below are the controls to show the media manager modal to choose
     * images from and to be added on this field
     *
     * @type {{}}
     */
    $scope.gallery = {};

    // shows the media modal so we can select files to be place on the gallery field
    $scope.gallery.showMediaModal = function (formGroup, galleryFieldName) {

        // lets define if its not yet define so we don't get errors
        if( ! $scope.content.customFields[formGroup] ) {
            $scope.content.customFields[formGroup] = {};
        }

        // if the field is not yet filled or being initialize, this is probably a fresh field added to this content
        // so we will define it as an empty array so we can push items on it.
        if( (!$scope.content.customFields[formGroup][galleryFieldName]) || ($scope.content.customFields[formGroup][galleryFieldName].length==0) ) {
            $scope.content.customFields[formGroup][galleryFieldName] = [];
        }

        var m = $modal.open({
            templateUrl: BASE_URL + '/darryldecode/backend/cb/app/contents/modals/gallery.html',
            controller: 'GalleryModalController',
            size: 'lg'
        });

        m.result.then(function (selectedFiles) {

            for(var prop in selectedFiles) {
                if( selectedFiles[prop] == true ) {
                    var imageSrc = STORAGE_URL + prop;
                    $scope.content.customFields[formGroup][galleryFieldName].push({
                        relativePath: prop,
                        fullPath: imageSrc
                    });
                }
            }

        });
    };

    // remove an image on a specific gallery
    $scope.gallery.remove = function (index, formGroup, galleryFieldName) {
        $scope.content.customFields[formGroup][galleryFieldName].splice(index,1);
    };


    /**
     * Image Custom Field
     *
     * if there is an image custom field to the current content,
     * below are the controls to show the media manager modal to choose
     * images from and to be added on this field
     *
     * @type {{}}
     */
    $scope.image = {};
    $scope.image.limit = 1;

    // shows the media modal so we can select files to be place on the image field
    $scope.image.showMediaModal = function (formGroup, imageFieldName) {

        // lets define if its not yet define so we don't get errors
        if( ! $scope.content.customFields[formGroup] ) {
            $scope.content.customFields[formGroup] = {};
        }

        // if the field is not yet filled or being initialize, this is probably a fresh field added to this content
        // so we will define it as an empty array so we can push items on it.
        if( (!$scope.content.customFields[formGroup][imageFieldName]) || ($scope.content.customFields[formGroup][imageFieldName].length==0) ) {
            $scope.content.customFields[formGroup][imageFieldName] = [];
        }

        var m = $modal.open({
            templateUrl: BASE_URL + '/darryldecode/backend/cb/app/contents/modals/gallery.html',
            controller: 'GalleryModalController',
            size: 'lg'
        });

        m.result.then(function (selectedFiles) {
            for(var prop in selectedFiles) {

                if( $scope.image.limit > 1 ) continue;

                if( selectedFiles[prop] == true ) {
                    var imageSrc = STORAGE_URL + prop;
                    $scope.content.customFields[formGroup][imageFieldName].push({
                        relativePath: prop,
                        fullPath: imageSrc
                    });
                    $scope.image.limit++;
                }
            }
        });
    };

    // remove an image on a specific gallery
    $scope.image.remove = function (formGroup, imageFieldName) {
        $scope.content.customFields[formGroup][imageFieldName] = [];
        $scope.image.limit = 1;
    };

    /**
     * Editor
     *
     * @type {{}}
     */
    $scope.editor = {};
    $scope.editor.aceConfig = {
        useWrapMode : true,
        showGutter: true,
        theme:'monokai',
        mode: 'html',
        firstLineNumber: 5,
        require: ['ace/ext/language_tools'],
        advanced: {
            enableSnippets: true,
            enableBasicAutocompletion: true,
            enableLiveAutocompletion: true
        },
        onLoad: function () {},
        onChange: function () {}
    };
    $scope.editor.mceConfig = {
        theme: "modern",
        skin: "light",
        plugins: ["autoresize","image"],
        toolbar: 'undo redo | bold italic underline | alignleft aligncenter alignright | bullist numlist | BrowseImage',
        setup : function(ed) {
            ed.addButton('BrowseImage', {
                title : 'Browse Uploads',
                image : '/darryldecode/backend/cb/images/icons/file-browse-icon.png',
                onclick : function() {

                    var m = $modal.open({
                        templateUrl: BASE_URL + '/darryldecode/backend/cb/app/contents/modals/gallery.html',
                        controller: 'GalleryModalController',
                        size: 'lg'
                    });

                    m.result.then(function (selectedFiles) {
                        for(var prop in selectedFiles) {
                            if( selectedFiles[prop] == true ) {
                                var imageSrc = STORAGE_URL + prop;
                                ed.execCommand('mceInsertContent', false, "<img src='"+imageSrc+"'>");
                            }
                        }
                    });
                }
            });
        }
    };
    $scope.editor.mode = 'mce'; // ace | mce | md
    $scope.editor.changeEditor = function (editor) {
        $scope.editor.mode = editor;
        if( editor == 'md' ) {
            $timeout(function () {
                angular.element(".textarea-md").markdown({
                    autofocus: false,
                    savable: false,
                    iconlibrary: 'fa'
                });
            }, 900);
        }
    };

    /**
     * Term
     *
     * @type {{}}
     */
    $scope.term = {};
    $scope.term.taxonomy = null;
    $scope.term.name = '';
    $scope.term.slug = '';
    $scope.term.isDoing = false;

    // show the field so a user can add new term to a taxonomy
    $scope.term.showAdd = function (taxonomy) {
        $scope.term.taxonomy = taxonomy;
        angular.element(".term-add-"+taxonomy.id).toggle();
    };

    // saves the new term to a specified taxonomy
    $scope.term.add = function () {
        if( $scope.term.name == '' || $scope.term.slug == '' ) return false;

        $scope.term.isDoing = true;

        ContentFactory.createTaxonomyTerm({
            term: $scope.term.name,
            slug: $scope.term.slug,
            contentTypeTaxonomyId: $scope.term.taxonomy.id
        }).then(function (success) {
            $scope.term.taxonomy.terms.push(success.data.data);
            $scope.term.isDoing = false;
            $scope.term.name = '';
            $scope.term.slug = '';
            angular.element(".term-add-"+$scope.term.taxonomy.id).hide();
        }, function (error) {
            $scope.term.isDoing = false;
            GlobalLoaderService.show(error.data.message || 'An error has occurred.','danger').hide(3500);
        });
    };

    // watch the term name value so we can automatically produce slug version of it
    $scope.$watch('term.name', function () {
        $scope.term.slug = $filter('slugify')($scope.term.name);
    });

    /**
     * Filter Query
     *
     * @type {{}}
     */
    $scope.qFilter = {};
    $scope.qFilter.show = false;
    $scope.qFilter.isDoing = false;
    $scope.qFilter.availableItems = $scope.contentType.terms;
    $scope.qFilter.status = '';
    $scope.qFilter.selectedTerms = [];

    // produces grouping to our select drop-down filter
    $scope.qFilter.group = function (item) {
        if( item.content_type_taxonomy_id == item.taxonomy.id )
        {
            return item.taxonomy.taxonomy;
        }
    };

    // show the filter panel
    $scope.qFilter.showClick = function () {
        $scope.qFilter.show = !$scope.qFilter.show;
        // if filter panel is being close, reset query
        if( ! $scope.qFilter.show ) {
            queryContentsByType($scope.contentType.id, {});
        }
    };

    // query filter
    $scope.qFilter.query = function () {
        var terms = extractTermsFromSelectedFilterObjects($scope.qFilter.selectedTerms);
        $scope.qFilter.isDoing = true;
        queryContentsByType($scope.contentType.id, {
            terms: terms,
            status: $scope.qFilter.status
        }, function () {
            $scope.qFilter.isDoing = false;
        });
    };

    // parses taxonomies to a format that is compatible for edit
    function parseTaxonomiesForEdit (taxonomies) {

        var taxo = [];

        angular.forEach(taxonomies, function (term) {
            taxo[term.id] = true;
        });

        return taxo;
    }

    // parses custom fields to a format compatible for edit
    function parseCustomFieldsForEdit (meta) {

        var cf = {};
        var prop = {};

        angular.forEach(meta, function (field) {
            prop[field.key] = field.value;
            cf[field.form_group_name] = prop;
        });

        return cf;
    }

    // reset content object state
    function resetContentState () {
        $scope.content.title = '';
        $scope.content.slug = '';
        $scope.content.body = '';
        $scope.content.status = '';
        $scope.content.permissionRequirements = '';
        $scope.content.taxonomies = {};
        $scope.content.miscData = {};
        $scope.content.customFields = {};
        $scope.content.isDoing = false;
    }

    // query contents by type
    function queryContentsByType (type, params, callback) {
        ContentFactory.getContentsByType(type, params).then(function(success) {
            $scope.contents = success.data.data;
            if( callback instanceof Function ) {
                callback();
            }
        });
    }
    queryContentsByType($scope.contentType.id, {});

    // query content type
    function queryContentType (type) {
        ContentFactory.getTypes({type: type}).then(function (success) {
            $scope.contentType = success.data.data[0]; console.log($scope.contentType.terms);
        });
    }
    queryContentType($scope.contentType.id);

    // extracts selected terms use in filter
    function extractTermsFromSelectedFilterObjects(selected) {
        var taxString = '';
        angular.forEach(selected, function(term,i) {
            taxString += ':'+term.taxonomy.taxonomy+'|'+term.slug+'';
        });
        return taxString;
    }

    // this will check all custom fields that are required
    function checkRequiredFields() {

        var result = {
            success: true,
            message: ''
        };

        angular.forEach($scope.contentType.form_groups, function(group, i) {
            angular.forEach(group.fields, function(field, i) {
                if( field.data.required == true ) {

                    if( ! $scope.content.customFields.hasOwnProperty(group.form_name) ) {
                        result.success = false;
                        result.message = field.data.label + ' is required!';
                        return;
                    }

                    if( (!$scope.content.customFields[group.form_name].hasOwnProperty(field.data.name)) || ($scope.content.customFields[group.form_name][field.data.name].length == 0) ) {
                        result.success = false;
                        result.message = field.data.label + ' is required!';
                    }
                }
            });
        });

        return result;
    }
}]);