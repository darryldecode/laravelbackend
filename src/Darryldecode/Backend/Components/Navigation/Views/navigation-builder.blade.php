@extends('backend::layouts.master')

@section('content')
    <div data-ng-controller="NavigationBuilderController" id="inner-content" class="animated fadeIn">

        <!-- list created custom nav -->
        <div class="panel panel-default">
            <div class="panel-body">
                <div class="row view-header">
                    <div class="col-lg-6 col-md-6">
                        <h3>
                            <i class="fa fa-align-justify"></i> NAVIGATION BUILDER
                        </h3>
                    </div>
                    <div class="col-lg-6 col-md-6">
                        <div class="control-panel text-right">
                            <button data-ng-click="drawer.show('drawer-create')" title="Build New Navigation" class="btn btn-default"><i class="fa fa-plus"></i></button>
                        </div>
                    </div>
                </div>
                <table class="table">
                    <tr>
                        <th>ID</th>
                        <th>Navigation Name</th>
                    </tr>
                    <tr data-ng-repeat="nav in customNavigationList.data">
                        <td>[@{{nav.id}}]</td>
                        <td>
                            <i class="fa fa-flag"></i> @{{nav.name}}
                            <span class="pull-right">
                                <button data-ng-click="navigation.configure(nav, 'drawer-configure')" class="btn btn-clear btn-xs"><i class="fa fa-cog"></i> Configure</button>
                                <button data-ng-click="navigation.trash(nav.id)" data-ng-disabled="navigation.trashing" class="btn btn-clear btn-xs"><i class="fa fa-trash"></i> Delete</button>
                            </span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- create navigation panel -->
        <div class="drawer drawer-create">
            <div data-ng-if="drawer.mode == 'drawer-create'">
                <div class="control-panel text-right">
                    <button data-ng-click="drawer.hide('drawer-create')" class="btn btn-clear"><i class="fa fa-close"></i> Close</button>
                </div>
                <div class="panel panel-default">
                    <div class="panel-body">
                        <div class="row">

                            <div class="col-lg-6 col-md-6">
                                <div class="navigation-builder">
                                    <div ui-tree data-empty-place-holder-enabled="true">
                                        <ol ui-tree-nodes="" data-ng-model="navigation.items">
                                            <li data-ng-repeat="item in navigation.items" ui-tree-node data-ng-include=" '/darryldecode/backend/cb/app/navigation/templates/nav-renderer.html' ">
                                            </li>
                                        </ol>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-6 col-md-6">
                                <div class="form-group">
                                    <input data-ng-model="navigation.name" type="text" class="form-control" placeholder="Navigation Name">
                                </div>
                                <div data-ng-if="navigation.currentWorkingItem && navigation.isEditMode" class="navigation-configure animated fadeInRight">
                                    <div class="panel panel-default">
                                        <div class="panel-heading">Editing Navigation: @{{navigation.currentWorkingItem.title}}</div>
                                        <div class="panel-body">
                                            <div class="nav-panel-add">
                                                <div class="form-group">
                                                    <input data-ng-model="navigation.title" type="text" class="form-control" placeholder="title">
                                                </div>
                                                <div class="form-group">
                                                    <input data-ng-model="navigation.attr.class" type="text" class="form-control" placeholder="class">
                                                </div>
                                                <div class="form-group">
                                                    <input data-ng-model="navigation.attr.id" type="text" class="form-control" placeholder="id">
                                                </div>
                                                <div class="form-group">
                                                    <input data-ng-model="navigation.url" type="text" class="form-control" placeholder="http://example.com">
                                                </div>
                                                <div class="form-group">
                                                    <label>
                                                        Open in new tab?
                                                        <input data-ng-model="navigation.newTab" type="checkbox">
                                                    </label>
                                                </div>
                                                <div class="form-group text-right">
                                                    <button data-ng-click="navigation.update()" class="btn btn-sm btn-default">Update</button>
                                                    <button data-ng-click="navigation.cancel()" class="btn btn-sm btn-warning">Cancel</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group text-right">
                                    <button data-ng-click="navigation.save()" data-ng-disabled="navigation.isDoing" class="btn btn-default">Save <i data-ng-if="navigation.isDoing" class="fa fa-spinner fa-spin"></i></button>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- configure navigation panel -->
        <div class="drawer drawer-configure">
            <div data-ng-if="drawer.mode == 'drawer-configure'">
                <div class="control-panel text-right">
                    <button data-ng-click="drawer.hide('drawer-configure')" class="btn btn-clear"><i class="fa fa-close"></i> Close</button>
                </div>
                <div class="panel panel-default">
                    <div class="panel-body">
                        <div class="row">

                            <div class="col-lg-6 col-md-6">
                                <div class="navigation-builder">
                                    <div ui-tree data-empty-place-holder-enabled="true">
                                        <ol ui-tree-nodes="" data-ng-model="navigation.items">
                                            <li data-ng-repeat="item in navigation.items" ui-tree-node data-ng-include=" '/darryldecode/backend/cb/app/navigation/templates/nav-renderer.html' ">
                                            </li>
                                        </ol>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-6 col-md-6">
                                <div class="form-group">
                                    <input data-ng-model="navigation.name" type="text" class="form-control" placeholder="Navigation Name">
                                </div>
                                <div data-ng-if="navigation.currentWorkingItem && navigation.isEditMode" class="navigation-configure animated fadeInRight">
                                    <div class="panel panel-default">
                                        <div class="panel-heading">Editing Navigation: @{{navigation.currentWorkingItem.title}}</div>
                                        <div class="panel-body">
                                            <div class="nav-panel-add">
                                                <div class="form-group">
                                                    <input data-ng-model="navigation.title" type="text" class="form-control" placeholder="title">
                                                </div>
                                                <div class="form-group">
                                                    <input data-ng-model="navigation.attr.class" type="text" class="form-control" placeholder="class">
                                                </div>
                                                <div class="form-group">
                                                    <input data-ng-model="navigation.attr.id" type="text" class="form-control" placeholder="id">
                                                </div>
                                                <div class="form-group">
                                                    <input data-ng-model="navigation.url" type="text" class="form-control" placeholder="http://example.com">
                                                </div>
                                                <div class="form-group">
                                                    <label>
                                                        Open in new tab?
                                                        <input data-ng-model="navigation.newTab" type="checkbox">
                                                    </label>
                                                </div>
                                                <div class="form-group text-right">
                                                    <button data-ng-click="navigation.update()" class="btn btn-sm btn-default">Update</button>
                                                    <button data-ng-click="navigation.cancel()" class="btn btn-sm btn-warning">Cancel</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group text-right">
                                    <button data-ng-click="navigation.configureUpdate()" data-ng-disabled="navigation.isDoing" class="btn btn-default">Update <i data-ng-if="navigation.isDoing" class="fa fa-spinner fa-spin"></i></button>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
@stop