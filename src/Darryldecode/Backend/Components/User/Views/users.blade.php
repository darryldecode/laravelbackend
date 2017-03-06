@extends('backend::layouts.master')

@section('content')
    <div data-ng-controller="ManageUsersController" id="inner-content" class="animated fadeIn">

        <!-- list users -->
        <div class="panel panel-default">
            <div class="panel-body">
                <div class="row view-header">
                    <div class="col-lg-6 col-md-6">
                        <h3>
                            <i class="fa fa-users"></i> USERS
                        </h3>
                    </div>
                    <div class="col-lg-6 col-md-6">
                        <div class="control-panel text-right">
                            <button data-ng-click="drawer.show('drawer-create')" class="btn btn-default"><i class="fa fa-plus"></i> New User</button>
                            <a href="{{route('backend.groups')}}" class="btn btn-default"><i class="fa fa-wrench"></i> Manage Groups</a>
                            <button data-ng-click="filter.toggle()" class="btn btn-default" title="Open Query Filter"><i class="fa fa-filter"></i></button>
                        </div>
                    </div>
                </div>
                <div class="row" data-ng-if="filter.isOpen">
                    <div class="col-lg-12 col-md-12">
                        <table class="table">
                            <tr>
                                <td><input data-ng-model="filter.firstName" type="text" class="form-control" placeholder="First Name"></td>
                                <td><input data-ng-model="filter.lastName" type="text" class="form-control" placeholder="Last Name"></td>
                                <td><input data-ng-model="filter.email" type="text" class="form-control" placeholder="Email"></td>
                                <td>
                                    <select data-ng-model="filter.group" data-ng-options="g.id as g.name for g in groups">
                                        <option value="">--group--</option>
                                    </select>
                                </td>
                                <td><button class="btn btn-default" data-ng-click="filter.filter()" data-ng-disabled="filter.isQuerying">Filter</button></td>
                            </tr>
                        </table>
                    </div>
                </div>
                <div class="table-responsive animated fadeIn">
                    <table class="table table-hover">
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th colspan="2" class="action text-center"><i class="fa fa-bolt"></i></th>
                        </tr>
                        <tr data-ng-show="!users.data.length">
                            <td colspan="4" class="text-center">No Results Found.</td>
                        </tr>
                        <tr data-ng-repeat="u in users.data" class="user-item-@{{::$index}}" >
                            <td>[@{{::u.id}}]</td>
                            <td><i class="fa fa-user"></i> @{{::u.first_name}} @{{::u.last_name}}</td>
                            <td>@{{::u.email}}</td>
                            <td class="action text-center">
                                <button data-ng-click="user.edit('drawer-edit', u, $index)" class="btn btn-clear"><i class="fa fa-search-plus"></i></button>
                                <button data-ng-click="user.trash(u.id, $index)" class="btn btn-clear"><i class="fa fa-trash-o"></i></button>
                            </td>
                        </tr>
                    </table>
                </div>
                <pagination total-items="users.total" items-per-page="pagination.perPage" data-ng-model="pagination.current" data-ng-change="pagination.next()" max-size="5" class="pagination-sm" boundary-links="true"></pagination>
            </div>
        </div>
        <!-- /list users -->

        <!-- create user drawer -->
        <div class="drawer drawer-create">
            <div data-ng-if="drawer.mode == 'drawer-create'">
                <div class="control-panel text-right">
                    <button data-ng-click="drawer.hide('drawer-create')" class="btn btn-clear"><i class="fa fa-close"></i> Close</button>
                </div>
                <div class="panel panel-default">
                    <div class="panel-body">
                        <h3 class="text-center drawer-title">
                            Add New User
                        </h3>
                        <div class="row">
                            <div class="col-lg-6 col-md-6">
                                <div class="form-group">
                                    <label>First Name</label>
                                    <input data-ng-model="user.firstName" type="text" class="form-control" placeholder="First name">
                                </div>
                                <div class="form-group">
                                    <label>Last Name</label>
                                    <input data-ng-model="user.lastName" type="text" class="form-control" placeholder="Last name">
                                </div>
                                <div class="form-group">
                                    <label>Email</label>
                                    <input data-ng-model="user.email" type="email" class="form-control" placeholder="Email">
                                </div>
                                <div class="form-group">
                                    <label>Password</label>
                                    <input data-ng-model="user.password" type="password" class="form-control" placeholder="Password">
                                </div>
                                <div class="form-group">
                                    <label>Confirm Password</label>
                                    <label data-ng-show="user.passwordConfirmMatched && user.password.length" class="label label-success"><i class="fa fa-thumbs-up"></i></label>
                                    <label data-ng-show="!user.passwordConfirmMatched && user.password.length" class="label label-danger"><i class="fa fa-thumbs-down"></i> Not matched!</label>
                                    <input data-ng-model="user.passwordConfirm" type="password" class="form-control" placeholder="Confirm Password">
                                </div>
                                <div class="form-group">
                                    <button data-ng-click="user.save()" data-ng-disabled="user.isDoing" class="btn btn-default">Save User <i data-ng-show="user.isDoing" class="fa fa-spinner fa-spin"></i></button>
                                    <button data-ng-click="user.cancel()" class="btn btn-danger">Cancel</button>
                                </div>
                            </div>
                            <div class="col-lg-6 col-md-6">
                                <div class="panel panel-default">
                                    <div class="panel-body">

                                        <!-- groups -->
                                        <h4>Groups: <label class="label label-danger"><i class="fa fa-ban"></i> Denied</label>&nbsp;<label class="label label-success"><i class="fa fa-thumbs-up"></i> Allowed</label></h4>
                                        <ul class="list-group">
                                            <li data-ng-if="!groups.length" class="list-group-item text-center">Loading groups <i class="fa fa-spinner fa-spin"></i></li>
                                            <li data-ng-repeat="group in groups" class="list-group-item">
                                                <p>
                                                    <b>Group:</b> @{{group.name}}

                                                    <span class="pull-right">
                                                    <input switch-size="mini"
                                                           bs-switch
                                                           ng-model="groups[$index].assigned"
                                                           type="checkbox">
                                                </span>
                                                </p>
                                                <p>
                                                    <b><i class="fa fa-lock"></i> Permissions:</b>
                                                    <div class="clearfix">
                                                        <label data-ng-repeat="(k,v) in group.permissions" class="permission pull-left label label-@{{(v==-1) ? 'danger' : 'success'}}">
                                                            @{{k}}
                                                        </label>
                                                    </div>
                                                </p>
                                            </li>
                                        </ul>

                                        <!-- permissions -->
                                        <h4>Permissions: <label class="label label-danger"><i class="fa fa-ban"></i> Denied</label>&nbsp;<label class="label label-success"><i class="fa fa-thumbs-up"></i> Allowed</label></h4>
                                        <table class="table">
                                            <tr>
                                                <td colspan="2">
                                                    <div class="clearfix">
                                                        <label data-ng-repeat="permission in user.permissions" class="permission pull-left label label-@{{(permission.value==-1) ? 'danger' : 'success'}}">
                                                            @{{permission.key}}
                                                            <a data-ng-click="user.removePermission($index)" href="" title="Remove permission"><i class="fa fa-close"></i></a>
                                                        </label>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <select data-ng-model="user.permissionKey" data-ng-options="p.permission as p.title for p in permissions" class="form-control">
                                                        <option value="">--Permission Key--</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <select data-ng-model="user.permissionValue" data-ng-options="o.value as o.label for o in user.permissionsOptions" class="form-control">
                                                        <option value="">--Permission Value--</option>
                                                    </select>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="2" class="text-right">
                                                    <button data-ng-click="user.addPermission()" class="btn btn-default"><i class="fa fa-plus-circle"></i> Add</button>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- edit user drawer -->
        <div class="drawer drawer-edit">
            <div data-ng-if="drawer.mode == 'drawer-edit'">
                <div class="control-panel text-right">
                    <button data-ng-click="drawer.hide('drawer-edit')" class="btn btn-clear"><i class="fa fa-close"></i> Close</button>
                </div>
                <div class="panel panel-default">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-lg-6 col-md-6">
                                <div class="form-group">
                                    <label>First Name</label>
                                    <input data-ng-model="user.firstName" type="text" class="form-control" placeholder="First name">
                                </div>
                                <div class="form-group">
                                    <label>Last Name</label>
                                    <input data-ng-model="user.lastName" type="text" class="form-control" placeholder="Last name">
                                </div>
                                <div class="form-group">
                                    <label>Email</label>
                                    <input data-ng-model="user.email" type="email" class="form-control" placeholder="Email">
                                </div>
                                <div class="form-group">
                                    <label>New Password <small><i>(Leave blank if no password changes)</i></small></label>
                                    <input data-ng-model="user.password" type="password" class="form-control" placeholder="Password">
                                </div>
                                <div class="form-group">
                                    <label>Confirm Password</label>
                                    <label data-ng-show="user.passwordConfirmMatched && user.password.length" class="label label-success"><i class="fa fa-thumbs-up"></i></label>
                                    <label data-ng-show="!user.passwordConfirmMatched && user.password.length" class="label label-danger"><i class="fa fa-thumbs-down"></i> Not matched!</label>
                                    <input data-ng-model="user.passwordConfirm" type="password" class="form-control" placeholder="Confirm Password">
                                </div>
                                <div class="form-group">
                                    <button data-ng-click="user.update()" data-ng-disabled="user.isDoing" class="btn btn-default">Update User <i data-ng-show="user.isDoing" class="fa fa-spinner fa-spin"></i></button>
                                </div>
                            </div>
                            <div class="col-lg-6 col-md-6">
                                <div class="panel panel-default">
                                    <div class="panel-body">

                                        <!-- groups -->
                                        <h4>Groups: <label class="label label-danger"><i class="fa fa-ban"></i> Denied</label>&nbsp;<label class="label label-success"><i class="fa fa-thumbs-up"></i> Allowed</label></h4>
                                        <ul class="list-group">
                                            <li data-ng-if="!groups.length" class="list-group-item text-center">Loading groups <i class="fa fa-spinner fa-spin"></i></li>
                                            <li data-ng-repeat="group in groups" class="list-group-item">
                                                <p>
                                                    <b>Group:</b> @{{group.name}}

                                                    <span class="pull-right">
                                                    <input switch-size="mini"
                                                           bs-switch
                                                           ng-model="groups[$index].assigned"
                                                           type="checkbox">
                                                </span>
                                                </p>
                                                <p>
                                                    <b><i class="fa fa-lock"></i> Permissions:</b>
                                                    <div class="clearfix">
                                                        <label data-ng-repeat="(k,v) in group.permissions" class="permission pull-left label label-@{{(v==-1) ? 'danger' : 'success'}}">
                                                            @{{k}}
                                                        </label>
                                                    </div>
                                                </p>
                                            </li>
                                        </ul>

                                        <!-- permissions -->
                                        <h4>Permissions: <label class="label label-danger"><i class="fa fa-ban"></i> Denied</label>&nbsp;<label class="label label-success"><i class="fa fa-thumbs-up"></i> Allowed</label></h4>
                                        <table class="table">
                                            <tr data-ng-if="user.permissions.length>0">
                                                <td colspan="2">
                                                    <div class="clearfix">
                                                        <label data-ng-repeat="permission in user.permissions" class="permission pull-left label label-@{{(permission.value==-1) ? 'danger' : 'success'}}">
                                                            @{{permission.key}}
                                                            <a data-ng-click="user.removePermission($index)" href="" title="Remove permission"><i class="fa fa-close"></i></a>
                                                        </label>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <select data-ng-model="user.permissionKey" data-ng-options="p.permission as p.title for p in permissions" class="form-control">
                                                        <option value="">--Permission Key--</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <select data-ng-model="user.permissionValue" data-ng-options="o.value as o.label for o in user.permissionsOptions" class="form-control">
                                                        <option value="">--Permission Value--</option>
                                                    </select>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="2" class="text-right">
                                                    <button data-ng-click="user.addPermission()" class="btn btn-default"><i class="fa fa-plus-circle"></i> Add</button>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
@stop