angular.module('cb.customFields').factory('CustomFieldsFactory',['$http',function($http) {

    /**
     * the available fields
     *
     * @returns {{label: string, key: string, template: string}[]}
     */
    this.getAvailableFields = function () {
        return [
            {
                label: "Input Text",
                key: "text",
                templateCreate: "/darryldecode/backend/cb/app/custom-fields/fields/create/input.html",
                templateDisplay: "/darryldecode/backend/cb/app/custom-fields/fields/display/input.html",
                data: {
                    label: "My Input Text",
                    type: "text",
                    placeHolder: "Some placeholder..",
                    name: '',
                    value: '',
                    disabled: false,
                    required: true
                }
            },
            {
                label: "Date Picker",
                key: "date_picker",
                templateCreate: "/darryldecode/backend/cb/app/custom-fields/fields/create/input-date-picker.html",
                templateDisplay: "/darryldecode/backend/cb/app/custom-fields/fields/display/input-date-picker.html",
                data: {
                    label: "My Date Picker",
                    type: "text",
                    placeHolder: "Some placeholder..",
                    name: '',
                    value: '',
                    disabled: false,
                    required: true
                }
            },
            {
                label: "Text Area",
                key: "textarea",
                templateCreate: "/darryldecode/backend/cb/app/custom-fields/fields/create/textarea.html",
                templateDisplay: "/darryldecode/backend/cb/app/custom-fields/fields/display/textarea.html",
                data: {
                    label: "My Textarea",
                    placeHolder: "Some placeholder..",
                    rows: 6,
                    name: '',
                    value: '',
                    disabled: false,
                    required: true
                }
            },
            {
                label: "Email",
                key: "email",
                templateCreate: "/darryldecode/backend/cb/app/custom-fields/fields/create/email.html",
                templateDisplay: "/darryldecode/backend/cb/app/custom-fields/fields/display/email.html",
                data: {
                    label: "My Input Text Email",
                    type: "email",
                    placeHolder: "Some placeholder..",
                    name: '',
                    value: '',
                    disabled: false,
                    required: true
                }
            },
            {
                label: "Password",
                key: "password",
                templateCreate: "/darryldecode/backend/cb/app/custom-fields/fields/create/password.html",
                templateDisplay: "/darryldecode/backend/cb/app/custom-fields/fields/display/password.html",
                data: {
                    label: "My Input Text Password",
                    type: "password",
                    placeHolder: "Some placeholder..",
                    name: '',
                    value: '',
                    disabled: false,
                    required: true
                }
            },
            {
                label: "Dropdown",
                key: "dropdown",
                templateCreate: "/darryldecode/backend/cb/app/custom-fields/fields/create/dropdown.html",
                templateDisplay: "/darryldecode/backend/cb/app/custom-fields/fields/display/dropdown.html",
                data: {
                    label: "My Dropdown",
                    displayText: "--display text--",
                    name: '',
                    disabled: false,
                    required: true,
                    options: [
                        {value: "1", label: "Sample 1"},
                        {value: "2", label: "Sample 2"}
                    ]
                }
            },
            {
                label: "Dropdown Multiple",
                key: "dropdown_multiple",
                templateCreate: "/darryldecode/backend/cb/app/custom-fields/fields/create/dropdown-multiple.html",
                templateDisplay: "/darryldecode/backend/cb/app/custom-fields/fields/display/dropdown-multiple.html",
                data: {
                    label: "My Multiple Select Dropdown",
                    displayText: "--display text--",
                    name: '',
                    disabled: false,
                    required: true,
                    options: [
                        {value: "1", label: "Sample 1"},
                        {value: "2", label: "Sample 2"}
                    ]
                }
            },
            {
                label: "Image Field",
                key: "image",
                templateCreate: "/darryldecode/backend/cb/app/custom-fields/fields/create/image.html",
                templateDisplay: "/darryldecode/backend/cb/app/custom-fields/fields/display/image.html",
                data: {
                    label: "Image",
                    description: 'Some field description here..',
                    name: '',
                    value: '',
                    disabled: false,
                    required: true
                }
            },
            {
                label: "Gallery Field",
                key: "gallery",
                templateCreate: "/darryldecode/backend/cb/app/custom-fields/fields/create/gallery.html",
                templateDisplay: "/darryldecode/backend/cb/app/custom-fields/fields/display/gallery.html",
                data: {
                    label: "My Gallery",
                    description: 'Some field description here..',
                    name: '',
                    required: true,
                    items: []
                }
            },
            {
                label: "Editor",
                key: "editor",
                templateCreate: "/darryldecode/backend/cb/app/custom-fields/fields/create/editor.html",
                templateDisplay: "/darryldecode/backend/cb/app/custom-fields/fields/display/editor.html",
                data: {
                    label: "My Editor",
                    name: '',
                    value: '',
                    required: true,
                    type: 'mce',
                    types: [
                        {value:"mce", label:"WYSIWYG"},
                        {value:"ace", label:"Code Editor"},
                        {value:"md", label:"Mark Down"}
                    ]
                }
            }
        ];
    };

    /**
     * get all forms
     *
     * @param params
     * @returns {*}
     */
    this.getFormGroups = function (params) {
        return $http({
            url: ADMIN_URL+'/custom_fields',
            method: 'GET',
            params: params
        });
    };

    /**
     * create form group & its custom fields
     *
     * @param data
     * @returns {*}
     */
    this.createFormGroup = function (data) {
        return $http({
            url: ADMIN_URL+'/custom_fields',
            method: 'POST',
            data: data
        });
    };

    /**
     * update form group & its custom fields
     *
     * @param id
     * @param data
     * @returns {*}
     */
    this.updateFormGroup = function (id, data) {
        return $http({
            url: ADMIN_URL+'/custom_fields/'+id,
            method: 'PUT',
            data: data
        });
    };

    /**
     * delete a custom field group
     *
     * @param id
     * @returns {*}
     */
    this.trash = function (id) {
        return $http({
            url: ADMIN_URL+'/custom_fields/'+id,
            method: 'DELETE'
        });
    };

    return this;
}]);