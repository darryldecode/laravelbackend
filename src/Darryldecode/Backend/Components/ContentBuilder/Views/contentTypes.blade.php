@extends('backend::layouts.master')

@section('content')
    <div data-ng-controller="ContentBuilderController" id="inner-content" class="animated fadeIn">

        <!-- list content types -->
        <div class="panel panel-default">
            <div class="panel-body">
                <div class="row view-header">
                    <div class="col-lg-6 col-md-6">
                        <h3>
                            <i class="fa fa-th-large"></i> CONTENT TYPES
                        </h3>
                    </div>
                    <div class="col-lg-6 col-md-6">
                        <div class="control-panel text-right">
                            <button data-ng-click="drawer.show('drawer-create')" class="btn btn-default"><i class="fa fa-plus"></i> New Content Type</button>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div data-ng-if="contentTypes.length==0" class="alert text-center">
                        No Items yet..
                    </div>
                    <div data-ng-repeat="cType in contentTypes" class="col-lg-3 col-md-4">
                        <div class="grid-repeat content-type-item-@{{$index}}">
                            <div class="panel-box pull-right">
                                <button data-ng-click="contentType.trash(cType.id, $index)" class="btn btn-clear"><i class="fa fa-trash"></i></button>
                            </div>
                            <div class="thumb text-center"><i class="fa fa-th-large"></i></div>
                            <h1>@{{::cType.type}}</h1>
                            <div class="content-type-meta-data">
                                <small>
                                    <i>Enable Revisions: @{{::(cType.enable_revisions==1) ? 'Yes' : 'No'}}</i>
                                </small><br>
                                <button data-ng-click="taxonomy.configure($index,'drawer-configure')" class="btn btn-clear btn-xs"><i class="fa fa-cog"></i> Configure</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /list content types -->

        <!-- create content type drawer -->
        <div class="drawer drawer-create">
            <div data-ng-if="drawer.mode == 'drawer-create'">
                <div class="control-panel text-right">
                    <button data-ng-click="drawer.hide('drawer-create')" class="btn btn-clear"><i class="fa fa-close"></i> Close</button>
                </div>
                <div class="panel panel-default">
                    <div class="panel-body">
                        <h3 class="text-center drawer-title">
                            CREATE NEW CONTENT TYPE
                        </h3>
                        <div class="row">
                            <div class="col-lg-6 col-md-6">
                                <table class="table">
                                    <tr>
                                        <td>Content Type</td>
                                        <td>
                                            <input data-ng-model="contentType.type" type="text" class="form-control" placeholder="blog">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Enable Revisions?</td>
                                        <td>
                                            <input switch-size="mini"
                                                   bs-switch
                                                   data-ng-model="contentType.enableRevision"
                                                   data-ng-true-value="'yes'"
                                                   data-ng-false-value="'no'"
                                                   type="checkbox">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="2">
                                            <button data-ng-click="contentType.save()" data-ng-disabled="contentType.isDoing" class="btn btn-default">Save Content Type <i data-ng-show="contentType.isDoing" class="fa fa-spinner fa-spin"></i></button>
                                            <button data-ng-click="drawer.hide('drawer-create')" class="btn btn-danger">Cancel</button>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-lg-6 col-md-6">
                                <div class="panel panel-default">
                                    <div class="panel-body">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- configure content type drawer -->
        <div class="drawer drawer-configure">
            <div data-ng-if="drawer.mode == 'drawer-configure'">
                <div class="control-panel text-right">
                    <button data-ng-click="drawer.hide('drawer-configure')" class="btn btn-clear"><i class="fa fa-close"></i> Close</button>
                </div>
                <div class="panel panel-default">
                    <div class="panel-body">
                        <h3 class="text-center drawer-title">Configure "@{{taxonomy.cType.type}}" Taxonomy & Terms</h3>
                        <div class="row">

                            <!-- taxonomies -->
                            <div class="col-lg-6 col-md-6">
                                <h5 class="drawer-title-secondary"><i class="fa fa-sitemap"></i> TAXONOMIES:</h5>
                                <ul class="list-group">
                                    <li data-ng-show="taxonomy.taxonomies.length==0" class="list-group-item text-center">
                                        No taxonomies yet..
                                    </li>
                                    <li data-ng-repeat="t in taxonomy.taxonomies" class="list-group-item">
                                        <i class="fa fa-square"></i> @{{::t.taxonomy}}
                                        <span class="pull-right">
                                            <button data-ng-click="terms.showAdd(t)" class="btn btn-default btn-xs"><i class="fa fa-cubes"></i> Edit Terms</button>
                                            <button data-ng-click="taxonomy.trash(t.id, $index)" class="btn btn-default btn-xs"><i class="fa fa-trash"></i> Delete</button>
                                        </span>
                                    </li>
                                </ul>
                                <table class="table">
                                    <tr>
                                        <td>Taxonomy Name:</td>
                                        <td><input data-ng-model="taxonomy.taxName" type="text" class="form-control" placeholder="Taxonomy name" aria-describedby="basic-addon2"></td>
                                    </tr>
                                    <tr>
                                        <td>Description:</td>
                                        <td><textarea data-ng-model="taxonomy.taxDescription" class="form-control"></textarea></td>
                                    </tr>
                                    <tr>
                                        <td colspan="2" class="text-right">
                                            <button data-ng-click="taxonomy.add()" data-ng-disabled="taxonomy.isDoing" class="btn btn-default"><i class="fa fa-plus"></i> Add <i data-ng-show="taxonomy.isDoing" class="fa fa-spinner fa-spin"></i></button>
                                        </td>
                                    </tr>
                                </table>
                            </div>

                            <!-- terms -->
                            <div class="col-lg-6 col-md-6">
                                <h5 class="clearfix drawer-title-secondary">
                                    <i class="fa fa-cubes"></i> TERMS FOR: <b>"@{{terms.taxonomy.taxonomy || "No Selected"}}"</b> <span data-ng-if="terms.isDoing"><i class="fa fa-spin fa-spinner"></i></span>
                                </h5>
                                <div data-ng-if="terms.taxonomy">
                                    <ul class="list-group">
                                        <li data-ng-if="!terms.isDoing && terms.terms.length==0" class="list-group-item text-center">No terms yet..</li>
                                        <li data-ng-repeat="t in terms.terms" class="list-group-item"><i class="fa fa-cube"></i> @{{t.term}} <i data-ng-click="terms.trash(t.id, $index)" class="cursor-pointer fa fa-trash-o pull-right"></i></li>
                                    </ul>
                                    <table class="table">
                                        <tr>
                                            <td>
                                                <input data-ng-model="terms.name" type="text" class="form-control" placeholder="Term name">
                                            </td>
                                            <td>
                                                <input data-ng-model="terms.slug" type="text" class="form-control" placeholder="term-name-slug">
                                            </td>
                                            <td>
                                                <button data-ng-click="terms.save()" data-ng-disabled="terms.isDoing" class="btn btn-default"><i class="fa fa-plus"></i> Add</button>
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
@stop