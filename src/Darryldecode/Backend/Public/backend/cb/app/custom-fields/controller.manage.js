angular.module('cb.customFields').controller('ManageCustomFieldsController', ['$scope','$timeout','$window','$filter','ContentFactory','GlobalLoaderService','AlertService','CustomFieldsFactory', function ($scope,$timeout,$window,$filter,ContentFactory,GlobalLoaderService,AlertService,CustomFieldsFactory) {

    console.log('ManageCustomFieldsController Init');

    // the custom field groups
    $scope.fieldGroups = [];
    $scope.contentTypes = [];

    // the sort config
    $scope.sortableOptions = {
        handle: 'div.sort-handle'
    };

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
    };

    /**
     * Field Group
     *
     * @type {{}}
     */
    $scope.fieldGroup = {};
    $scope.fieldGroup.id = null;
    $scope.fieldGroup.name = '';
    $scope.fieldGroup.formName = '';
    $scope.fieldGroup.conditions = [];
    $scope.fieldGroup.forContentTypeId = '';
    $scope.fieldGroup.availableFields = CustomFieldsFactory.getAvailableFields();
    $scope.fieldGroup.fieldToBeAdded = '';
    $scope.fieldGroup.fieldToBeAddedModel = {};
    $scope.fieldGroup.fields = [];
    $scope.fieldGroup.lastAddedFieldId = 0;
    $scope.fieldGroup.isDoing = false;

    // adds a new custom field
    $scope.fieldGroup.addField = function () {

        // increment the last field added ID
        // this is used as to be appended to the field key
        // so it will maintain a unique value
        $scope.fieldGroup.lastAddedFieldId++;

        // we will iterate through all the available fields and check if the
        // field is has the key the user wants to field to be added so we can get it
        // and then push it on our field group
        angular.forEach($scope.fieldGroup.availableFields, function(field, i) {

            if( field.key == $scope.fieldGroup.fieldToBeAdded ) {

                var fieldCopy = angular.copy(field);

                // the name of the custom field, this will be the key for this custom field
                // when save on our database and is used for queries
                fieldCopy.data.name = field.key + '_' + $scope.fieldGroup.lastAddedFieldId;

                // the key of the field, this is only used to hold a unique value of the field
                // so we can avoid duplicate error when a same field is added to a form group
                fieldCopy.key  = field.key + '_' + $scope.fieldGroup.lastAddedFieldId;

                // copy it to avoid reference effect
                $scope.fieldGroup.fields.push(fieldCopy);
            }

        });

        // reset the field group to be added
        $scope.fieldGroup.fieldToBeAdded = '';
    };

    // open the editing control panel of an added custom field
    $scope.fieldGroup.editField = function (field, index) {
        angular.element(".edit-field-"+field+'-'+index).slideToggle();
    };

    // deletes a field
    $scope.fieldGroup.deleteField = function (index) {
        $scope.fieldGroup.fields.splice(index,1);
    };

    // handles options if field type is a select drop-down
    $scope.fieldGroup.optionLabel = '';
    $scope.fieldGroup.optionValue = '';
    $scope.fieldGroup.addOption = function (index) {
        $scope.fieldGroup.fields[index].data.options.push({
            label: $scope.fieldGroup.optionLabel,
            value: $scope.fieldGroup.optionValue
        });
        $scope.fieldGroup.optionLabel = '';
        $scope.fieldGroup.optionValue = '';
    };

    // handles removing an options an a select field type
    $scope.fieldGroup.removeOption = function (parentIndex, index) {
        $scope.fieldGroup.fields[parentIndex].data.options.splice(index,1);
    };

    // saves the form group
    $scope.fieldGroup.save = function () {

        $scope.fieldGroup.isDoing = true;

        CustomFieldsFactory.createFormGroup({
            name: $scope.fieldGroup.name,
            formName: $scope.fieldGroup.formName,
            conditions: $scope.fieldGroup.conditions,
            contentTypeId: $scope.fieldGroup.forContentTypeId,
            fields: $scope.fieldGroup.fields
        }).then(function (success) {
            resetNewFormGroupState();
            loadFieldGroups();
            $scope.fieldGroup.isDoing = false;
            $scope.drawer.hide($scope.drawer.mode);
        }, function (error) {
            $scope.fieldGroup.isDoing = false;
            GlobalLoaderService.show(error.data.message || 'An error has occurred','danger').hide(3500);
        });

    };

    // updates the form group
    $scope.fieldGroup.update = function () {

        $scope.fieldGroup.isDoing = true;

        CustomFieldsFactory.updateFormGroup($scope.fieldGroup.id, {
            name: $scope.fieldGroup.name,
            formName: $scope.fieldGroup.formName,
            conditions: $scope.fieldGroup.conditions,
            contentTypeId: $scope.fieldGroup.forContentTypeId,
            fields: $scope.fieldGroup.fields
        }).then(function (success) {
            resetNewFormGroupState();
            loadFieldGroups();
            $scope.fieldGroup.isDoing = false;
            $scope.drawer.hide($scope.drawer.mode);
        }, function (error) {
            $scope.fieldGroup.isDoing = false;
            GlobalLoaderService.show(error.data.message || 'An error has occurred','danger').hide(3500);
        });

    };

    // I will show the update/edit panel for a field group
    $scope.fieldGroup.configure = function (fieldGroup, drawerMode) {
        $scope.fieldGroup.id = fieldGroup.id;
        $scope.fieldGroup.name = fieldGroup.name;
        $scope.fieldGroup.formName = fieldGroup.form_name;
        $scope.fieldGroup.conditions = fieldGroup.conditions;
        $scope.fieldGroup.forContentTypeId = fieldGroup.content_type_id;
        $scope.fieldGroup.fields = fieldGroup.fields;
        $scope.fieldGroup.lastAddedFieldId = fieldGroup.fields.length;
        $scope.drawer.show(drawerMode);
    };

    // trash
    $scope.fieldGroup.trash = function (id, index) {
        AlertService.showConfirm("Do you really want to delete this group field?", function () {
            CustomFieldsFactory.trash(id).then(function (success) {
                $scope.fieldGroups.data.splice(index,1);
            }, function (error) {
                GlobalLoaderService.show(error.data.message || 'An error has occurred','danger').hide(3500);
            });
        });
    };

    // watch the field group name so we can produced a valid form name
    $scope.$watch('fieldGroup.name', function () {
        $scope.fieldGroup.formName = $filter('slugify')($scope.fieldGroup.name,"_");
    });

    // resets the form group fields
    function resetNewFormGroupState () {
        $scope.fieldGroup.name = '';
        $scope.fieldGroup.formName = '';
        $scope.fieldGroup.conditions = [];
        $scope.fieldGroup.forContentTypeId = '';
        $scope.fieldGroup.fieldToBeAdded = '';
        $scope.fieldGroup.fieldToBeAddedModel = {};
        $scope.fieldGroup.fields = [];
        $scope.fieldGroup.lastAddedFieldId = 0;
        $scope.fieldGroup.isDoing = false;
    }

    // load form field groups
    function loadFieldGroups () {
        CustomFieldsFactory.getFormGroups({}).then(function (success) {
            $scope.fieldGroups = success.data.data;
        }, function (error) {
            GlobalLoaderService.show(error.data.message || 'An error has occurred.','danger').hide(4500);
        });
    }

    // load content types to be used as selections for the form group to belong
    function loadContentTypes () {
        ContentFactory.getTypes().then(function (success) {
            $scope.contentTypes = success.data.data;
        }, function (error) {
            GlobalLoaderService.show(error.data.message || 'An error has occurred.','danger').hide(4500);
        });
    }

    loadContentTypes();
    loadFieldGroups();
}]);