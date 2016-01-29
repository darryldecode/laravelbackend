@extends('backend::layouts.master')

@section('content')

    <div data-ng-controller="ManageGroupsController" id="inner-content" class="animated fadeIn">
        <div class="panel panel-default">
            <div class="panel-body">
                <div class="row view-header">
                    <div class="col-lg-6 col-md-6">
                        <h3>
                            <i class="fa fa-wrench"></i> Manage User Groups
                        </h3>
                    </div>
                    <div class="col-lg-6 col-md-6">
                        <div class="control-panel text-right">
                            <a href="{{route('backend.users')}}" class="btn btn-clear"><i class="fa fa-arrow-left"></i> Back to users</a>
                        </div>
                    </div>
                </div>
                <div class="row">

                    <div class="col-lg-6 col-md-6">
                        <div class="form-group">
                            <label>Group Name</label>
                            <input data-ng-model="group.name" type="text" class="form-control" placeholder="Group name">
                        </div>
                        <div class="form-group">
                            <label>Permissions</label>
                            <table class="table">
                                <tr>
                                    <td colspan="2">
                                        <div class="clearfix">
                                            <label data-ng-repeat="permission in group.permissions" class="permission pull-left label label-@{{(permission.value==-1) ? 'danger' : 'success'}}">
                                                @{{permission.key}}
                                                <a data-ng-click="group.remove($index)" href="" title="Remove permission"><i class="fa fa-close"></i></a>
                                            </label>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <select data-ng-model="group.permissionKey" data-ng-options="p.permission as p.title for p in permissions" class="form-control">
                                            <option value="">--Permission Key--</option>
                                        </select>
                                    </td>
                                    <td>
                                        <select data-ng-model="group.permissionValue" data-ng-options="o.value as o.label for o in group.permissionsOptions" class="form-control">
                                            <option value="">--Permission Value--</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2" class="text-right">
                                        <button data-ng-click="group.add()" class="btn btn-default"><i class="fa fa-plus-circle"></i> Add</button>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <button data-ng-if="!edit.isEditMode" data-ng-click="group.save()" type="submit" class="btn btn-default">Save Group <i data-ng-if="group.isSaving" class="fa fa-spinner fa-spin"></i></button>
                        <button data-ng-if="edit.isEditMode" data-ng-click="edit.update()" type="submit" class="btn btn-default">Update Group <i data-ng-if="edit.isUpdating" class="fa fa-spinner fa-spin"></i></button>
                        <button data-ng-if="edit.isEditMode" data-ng-click="edit.cancel()" type="submit" class="btn btn-default">Cancel</button>
                    </div>

                    <div class="col-lg-6 col-md-6">
                        <div class="panel panel-default">
                            <div class="panel-body">
                                <table class="table">
                                    <tr>
                                        <td><h2>Available Groups:</h2></td>
                                        <td>
                                            <table>
                                                <tr>
                                                    <td>Deny</td>
                                                    <td><label class="label label-danger">Deny Color</label></td>
                                                </tr>
                                                <tr>
                                                    <td>Allow</td>
                                                    <td><label class="label label-success">Allow Color</label></td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>
                                <ul class="list-group">
                                    <li data-ng-if="!groups.length" class="list-group-item text-center">Loading groups <i class="fa fa-spinner fa-spin"></i></li>
                                    <li data-ng-repeat="g in groups" class="list-group-item group-item-@{{$index}}">
                                        <p>
                                            Group Name: <b>@{{g.name}}</b><br>
                                            Group ID: <b>[@{{g.id}}]</b>
                                            <span class="pull-right">
                                                <button data-ng-click="edit.edit(g)" class="btn btn-default btn-xs"><i class="fa fa-pencil"></i> Edit</button>
                                                <button data-ng-click="group.trash(g.id, $index)" class="btn btn-warning btn-xs"><i class="fa fa-trash-o"></i> Delete</button>
                                            </span>
                                        </p>
                                        <p>
                                            <i class="fa fa-lock"></i> Permissions:
                                            <div class="clearfix">
                                                <label data-ng-repeat="(k,v) in g.permissions" class="permission pull-left label label-@{{(v==-1) ? 'danger' : 'success'}}">
                                                    @{{k}}
                                                </label>
                                            </div>
                                        </p>
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