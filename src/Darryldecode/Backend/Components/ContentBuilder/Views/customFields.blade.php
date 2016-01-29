@extends('backend::layouts.master')

@section('content')
    <div data-ng-controller="ManageCustomFieldsController" id="inner-content" class="animated fadeIn">

        <!-- list custom field groups -->
        <div class="panel panel-default">
            <div class="panel-body">
                <div class="row view-header">
                    <div class="col-lg-6 col-md-6">
                        <h3>
                            <i class="fa fa-list"></i> CUSTOM FIELD GROUPS
                        </h3>
                    </div>
                    <div class="col-lg-6 col-md-6">
                        <div class="control-panel text-right">
                            <button data-ng-click="drawer.show('drawer-create')" class="btn btn-default"><i class="fa fa-list"></i> New Field Group</button>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div data-ng-if="fieldGroups.data.length==0" class="alert text-center">
                        No Items yet..
                    </div>
                    <div data-ng-repeat="group in fieldGroups.data" class="col-lg-3 col-md-4">
                        <div class="grid-repeat field-group-item-@{{$index}}">
                            <div class="panel-box pull-right">
                                <button data-ng-click="fieldGroup.trash(group.id, $index)" class="btn btn-clear"><i class="fa fa-trash"></i></button>
                            </div>
                            <div class="thumb text-center"><i class="fa fa-list"></i></div>
                            <h1>@{{::group.name}}</h1>
                            <div class="content-type-meta-data">
                                <small>
                                    <i>CONTENT TYPE: @{{::group.content_type.type}}</i>
                                </small><br>
                                <button data-ng-click="fieldGroup.configure(group,'drawer-configure')" class="btn btn-clear btn-xs"><i class="fa fa-cog"></i> Configure</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /list custom field groups -->

        <!-- create form group drawer -->
        <div class="drawer drawer-create">
            <div data-ng-if="drawer.mode == 'drawer-create'">
                <div class="control-panel text-right">
                    <button data-ng-click="drawer.hide('drawer-create')" class="btn btn-clear"><i class="fa fa-close"></i> Close</button>
                </div>
                <div class="panel panel-default">
                    <div class="panel-body">
                        <h3 class="text-center drawer-title">
                            NEW FORM GROUP
                        </h3>
                        <div class="row">
                            <div class="col-lg-5 col-md-5 custom-field-left">
                                <h5 class="drawer-title-secondary"><i class="fa fa-info-circle"></i> FORM DETAILS:</h5>
                                <div class="form-group">
                                    <label>Form Display Name:</label>
                                    <input data-ng-model="fieldGroup.name" type="text" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Form Name-Key: <small><i>(This should be unique. You will not be able to change this after.)</i></small></label>
                                    <input data-ng-model="fieldGroup.formName" type="text" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Form For:</label>
                                    <select data-ng-model="fieldGroup.forContentTypeId" data-ng-options="c.id as c.type for c in contentTypes" class="form-control">
                                        <option value="">--select content type--</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <button data-ng-disabled="!fieldGroup.name || fieldGroup.isDoing" data-ng-click="fieldGroup.save()" class="btn btn-default">Save <i data-ng-show="fieldGroup.isDoing" class="fa fa-spinner fa-spin"></i></button>
                                </div>
                            </div>
                            <div class="col-lg-7 col-md-7 custom-field-right">
                                <h5 class="drawer-title-secondary clearfix">
                                    <i class="fa fa-list"></i> FORM FIELDS:
                                    <span class="pull-right">
                                        <select data-ng-model="fieldGroup.fieldToBeAdded" data-ng-options="c.key as c.label for c in fieldGroup.availableFields">
                                            <option value="">--select content type--</option>
                                        </select>
                                        <button data-ng-click="fieldGroup.addField()" class="btn btn-default">Add Field</button>
                                    </span>
                                </h5>
                                <ul ui-sortable="sortableOptions" data-ng-model="fieldGroup.fields" class="list-group custom-fields-form">
                                    <li data-ng-repeat="f in fieldGroup.fields track by $index" class="list-group-item">
                                        <div data-ng-include="f.templateDisplay"></div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- configure form group drawer -->
        <div class="drawer drawer-configure">
            <div data-ng-if="drawer.mode == 'drawer-configure'">
                <div class="control-panel text-right">
                    <button data-ng-click="drawer.hide('drawer-configure')" class="btn btn-clear"><i class="fa fa-close"></i> Close</button>
                </div>
                <div class="panel panel-default">
                    <div class="panel-body">
                        <h3 class="text-center drawer-title">
                            CONFIGURE "@{{fieldGroup.name}}" FORM
                        </h3>
                        <div class="row">
                            <div class="col-lg-5 col-md-5 custom-field-left">
                                <h5 class="drawer-title-secondary"><i class="fa fa-info-circle"></i> FORM DETAILS:</h5>
                                <div class="form-group">
                                    <label>Form Display Name:</label>
                                    <input data-ng-model="fieldGroup.name" type="text" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Form Name-Key:</label>
                                    <input disabled="disabled" value="@{{::fieldGroup.formName}}" type="text" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Form For:</label>
                                    <select data-ng-model="fieldGroup.forContentTypeId" data-ng-options="c.id as c.type for c in contentTypes" class="form-control">
                                        <option value="">--select content type--</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <button data-ng-disabled="!fieldGroup.name || fieldGroup.isDoing" data-ng-click="fieldGroup.update()" class="btn btn-default">Update <i data-ng-show="fieldGroup.isDoing" class="fa fa-spinner fa-spin"></i></button>
                                </div>
                            </div>
                            <div class="col-lg-7 col-md-7 custom-field-right">
                                <h5 class="drawer-title-secondary clearfix">
                                    <i class="fa fa-list"></i> FORM FIELDS:
                                    <span class="pull-right">
                                        <select data-ng-model="fieldGroup.fieldToBeAdded" data-ng-options="c.key as c.label for c in fieldGroup.availableFields">
                                            <option value="">--select content type--</option>
                                        </select>
                                        <button data-ng-click="fieldGroup.addField()" class="btn btn-default">Add Field</button>
                                    </span>
                                </h5>
                                <ul ui-sortable="sortableOptions" data-ng-model="fieldGroup.fields" class="list-group custom-fields-form">
                                    <li data-ng-repeat="f in fieldGroup.fields track by $index" class="list-group-item">
                                        <div data-ng-include="f.templateDisplay"></div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
@stop