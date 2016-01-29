@extends('backend::layouts.master')

@section('content')
    <div data-ng-controller="ContentsController" data-content-type="{{$contentType}}" id="inner-content" class="animated fadeIn">

        <!-- list content types -->
        <div class="panel panel-default">
            <div class="panel-body">
                <div class="row view-header">
                    <div class="col-lg-6 col-md-6">
                        <h3>
                            <i class="fa fa-th-large"></i> @{{contentType.type}}
                        </h3>
                    </div>
                    <div class="col-lg-6 col-md-6">
                        <div class="control-panel text-right">
                            <button data-ng-click="drawer.show('drawer-create')" title="New Entry" class="btn btn-default"><i class="fa fa-plus"></i></button>
                            <button data-ng-click="qFilter.showClick()" title="Open Query Filter" class="btn btn-default"><i class="fa fa-filter"></i></button>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12">

                        <!-- filters -->
                        <table data-ng-if="qFilter.show" class="table q-filter">
                            <tr>
                                <td class="td-col-md">
                                    <ui-select multiple data-ng-model="qFilter.selectedTerms" theme="bootstrap" data-ng-disabled="disabled" close-on-select="false" style="width: 100%;" title="Choose a category">
                                        <ui-select-match placeholder="Category..">@{{$item.term}}</ui-select-match>
                                        <ui-select-choices group-by="qFilter.group" repeat="term in contentType.terms | propsFilter: {term: $select.search}">
                                            <div ng-bind-html="term.term | highlight: $select.search"></div>
                                        </ui-select-choices>
                                    </ui-select>
                                </td>
                                <td class="td-col-sm">
                                    <select data-ng-model="qFilter.status" data-ng-options="o.value as o.label for o in contentStatusOptions" class="form-control">
                                        <option value="">--status--</option>
                                    </select>
                                </td>
                                <td class="text-right">
                                    <button data-ng-disabled="qFilter.isDoing" data-ng-click="qFilter.query()" class="btn btn-default"><i class="fa fa-filter"></i> Filter</button>
                                </td>
                            </tr>
                        </table>

                        <div data-ng-if="contents.data.length==0" class="alert text-center">
                            No Items yet..
                        </div>

                        <table data-ng-if="contents.data.length>0" class="table">
                            <tr>
                                <th>ID</th>
                                <th>Title</th>
                                <th>Status</th>
                                <th><i class="fa fa-user"></i> Author</th>
                                <th><i class="fa fa-clock-o"></i> Date Created</th>
                                <th><i class="fa fa-clock-o"></i> Last Modified</th>
                                <th class="action text-center"><i class="fa fa-bolt"></i></th>
                            </tr>
                            <tr data-ng-repeat="c in contents.data" class="content-item-@{{$index}}">
                                <td>[@{{c.id}}]</td>
                                <td>@{{c.title}}</td>
                                <td>@{{c.status}}</td>
                                <td>@{{c.author.first_name}}</td>
                                <td>@{{c.created_at | readableDate:'medium'}}</td>
                                <td>@{{c.updated_at | readableDate:'medium'}}</td>
                                <td class="action text-center">
                                    <button data-ng-click="content.update(c,'drawer-update')" class="btn btn-clear"><i class="fa fa-search-plus"></i></button>
                                    <button data-ng-click="content.trash(c.id, $index)" class="btn btn-clear"><i class="fa fa-trash"></i></button>
                                </td>
                            </tr>
                        </table>
                        <pagination total-items="contents.total" items-per-page="contents.per_page" data-ng-model="pagination.page" data-ng-change="pagination.next()" max-size="5" class="pagination-sm" boundary-links="true"></pagination>
                    </div>
                </div>
            </div>
        </div>
        <!-- /list content types -->

        <!-- create content drawer -->
        <div class="drawer drawer-create">
            <div data-ng-if="drawer.mode == 'drawer-create'">
                <div class="control-panel text-right">
                    <button data-ng-click="drawer.hide('drawer-create')" class="btn btn-clear"><i class="fa fa-close"></i> Close</button>
                </div>
                <div class="panel panel-default">
                    <div class="panel-body">
                        <h3 class="drawer-title">
                            Content / @{{contentType.type}} / Create
                        </h3>
                        <div class="row">
                            <div class="col-lg-8 col-md-8">
                                <div class="content-data">
                                    <div class="form-group">
                                        <label>Title:</label>
                                        <input data-ng-model="content.title" type="text" class="form-control" placeholder="title">
                                    </div>
                                    <div class="form-group">
                                        <label>Slug:</label>
                                        <input data-ng-model="content.slug" type="text" class="form-control" placeholder="slug">
                                    </div>
                                    <div class="form-group">
                                        <label>Body:</label>
                                        <div class="btn-group btn-group-sm pull-right" role="group">
                                            <button data-ng-click="editor.changeEditor('md')" type="button" class="btn btn-default @{{(editor.mode=='md') ? 'active' : ''}}"><i class="fa fa-columns"></i> Mark Down</button>
                                            <button data-ng-click="editor.changeEditor('mce')" type="button" class="btn btn-default @{{(editor.mode=='mce') ? 'active' : ''}}"><i class="fa fa-list-alt"></i> WYSIWIG</button>
                                            <button data-ng-click="editor.changeEditor('ace')" type="button" class="btn btn-default @{{(editor.mode=='ace') ? 'active' : ''}}"><i class="fa fa-code"></i> Code</button>
                                        </div>
                                        <div class="editor-wrap">

                                            <div data-ng-if=" editor.mode == 'ace' " class="editor-holder">
                                                <div data-ng-model="content.body" ui-ace="editor.aceConfig"></div>
                                            </div>

                                            <div data-ng-if=" editor.mode == 'mce' " class="editor-holder">
                                                <textarea ui-tinymce="editor.mceConfig" data-ng-model="content.body"></textarea>
                                            </div>

                                            <div data-ng-if=" editor.mode == 'md' " class="editor-holder">
                                                <textarea class="textarea-md" data-ng-model="content.body" data-provide="markdown" rows="10"></textarea>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                                <div class="content-forms">
                                    <div data-ng-repeat="group in contentType.form_groups" class="panel panel-default">
                                        <div class="panel-heading"><i class="fa fa-list"></i> @{{group.name}}</div>
                                        <div class="panel-body">
                                            <ul class="list-group custom-fields-form">
                                                <li data-ng-repeat="f in group.fields track by $index" class="list-group-item">
                                                    <div data-ng-include="f.templateCreate"></div>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-4">
                                <div class="content-taxonomy">
                                    <div class="panel panel-default">
                                        <div class="panel-body">
                                            <table class="table">
                                                <tr>
                                                    <td>
                                                        <select data-ng-model="content.status" data-ng-options="o.value as o.label for o in contentStatusOptions" class="form-control">
                                                            <option value="">--status--</option>
                                                        </select>
                                                    </td>
                                                    <td class="text-center">
                                                        <div class="btn-group btn-group-md" role="group" aria-label="">
                                                            <button data-ng-click="content.save()" data-ng-disabled="content.isDoing" type="button" class="btn btn-default"><i class="fa fa-check"></i> Save</button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                    <div data-ng-repeat="taxonomy in contentType.taxonomies" class="panel panel-default">
                                        <div class="panel-heading"><i class="fa fa-sitemap"></i> @{{taxonomy.taxonomy}}</div>
                                        <div class="panel-body">
                                            <ul class="list-group">
                                                <li data-ng-repeat="term in taxonomy.terms" class="list-group-item">
                                                    <label>
                                                        <input data-ng-model="content.taxonomies[term.id]" type="checkbox"> @{{term.term}}
                                                    </label>
                                                </li>
                                            </ul>
                                            <div class="text-right">
                                                <button data-ng-click="term.showAdd(taxonomy)" class="btn btn-clear btn-sm">Add New @{{taxonomy.taxonomy}}</button>
                                            </div>
                                            <div class="form-group term-add term-add-@{{taxonomy.id}}">
                                                <div class="input-group">
                                                    <input data-ng-model="term.name" type="text" class="form-control" aria-describedby="basic-addon2">
                                                    <span class="input-group-addon" id="basic-addon2">
                                                        <a data-ng-click="term.add()">Add</a> <i data-ng-show="term.isDoing" class="fa fa-spinner fa-spin"></i>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- update content drawer -->
        <div class="drawer drawer-update">
            <div data-ng-if="drawer.mode == 'drawer-update'">
                <div class="control-panel text-right">
                    <button data-ng-click="drawer.hide('drawer-update')" class="btn btn-clear"><i class="fa fa-close"></i> Close</button>
                </div>
                <div class="panel panel-default">
                    <div class="panel-body">
                        <h3 class="drawer-title">
                            Content / @{{contentType.type}} / @{{content.title}} / Edit
                        </h3>
                        <div class="row">
                            <div class="col-lg-8 col-md-8">
                                <div class="content-data">
                                    <div class="form-group">
                                        <label>Title:</label>
                                        <input data-ng-model="content.title" type="text" class="form-control" placeholder="title">
                                    </div>
                                    <div class="form-group">
                                        <label>Slug:</label>
                                        <input data-ng-model="content.slug" type="text" class="form-control" placeholder="slug">
                                    </div>
                                    <div class="form-group">
                                        <label>Body:</label>
                                        <div class="btn-group btn-group-sm pull-right" role="group">
                                            <button data-ng-click="editor.changeEditor('md')" type="button" class="btn btn-default @{{(editor.mode=='md') ? 'active' : ''}}"><i class="fa fa-columns"></i> Mark Down</button>
                                            <button data-ng-click="editor.changeEditor('mce')" type="button" class="btn btn-default @{{(editor.mode=='mce') ? 'active' : ''}}"><i class="fa fa-list-alt"></i> WYSIWIG</button>
                                            <button data-ng-click="editor.changeEditor('ace')" type="button" class="btn btn-default @{{(editor.mode=='ace') ? 'active' : ''}}"><i class="fa fa-code"></i> Code</button>
                                        </div>
                                        <div class="editor-wrap">

                                            <div data-ng-if=" editor.mode == 'ace' " class="editor-holder">
                                                <div data-ng-model="content.body" ui-ace="editor.aceConfig"></div>
                                            </div>

                                            <div data-ng-if=" editor.mode == 'mce' " class="editor-holder">
                                                <textarea ui-tinymce="editor.mceConfig" data-ng-model="content.body"></textarea>
                                            </div>

                                            <div data-ng-if=" editor.mode == 'md' " class="editor-holder">
                                                <textarea class="textarea-md" data-ng-model="content.body" data-provide="markdown" rows="10"></textarea>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                                <div class="content-forms">
                                    <div data-ng-repeat="group in contentType.form_groups" class="panel panel-default">
                                        <div class="panel-heading"><i class="fa fa-list"></i> @{{group.name}}</div>
                                        <div class="panel-body">
                                            <ul class="list-group custom-fields-form">
                                                <li data-ng-repeat="f in group.fields track by $index" class="list-group-item">
                                                    <div data-ng-include="f.templateCreate"></div>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-4">
                                <div class="content-taxonomy">
                                    <div class="panel panel-default">
                                        <div class="panel-body text-center">
                                            <table class="table">
                                                <tr>
                                                    <td>
                                                        <select data-ng-model="content.status" data-ng-options="o.value as o.label for o in contentStatusOptions" class="form-control"></select>
                                                    </td>
                                                    <td class="text-center">
                                                        <div class="btn-group btn-group-md" role="group" aria-label="">
                                                            <button data-ng-click="content.updateSave()" data-ng-disabled="content.isDoing" type="button" class="btn btn-default"><i class="fa fa-check"></i> Update</button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                    <div data-ng-repeat="taxonomy in contentType.taxonomies" class="panel panel-default">
                                        <div class="panel-heading"><i class="fa fa-sitemap"></i> @{{taxonomy.taxonomy}}</div>
                                        <div class="panel-body">
                                            <ul class="list-group">
                                                <li data-ng-repeat="term in taxonomy.terms" class="list-group-item">
                                                    <label>
                                                        <input data-ng-model="content.taxonomies[term.id]" type="checkbox"> @{{term.term}}
                                                    </label>
                                                </li>
                                            </ul>
                                            <div class="text-right">
                                                <button data-ng-click="term.showAdd(taxonomy)" class="btn btn-clear btn-sm">Add New @{{taxonomy.taxonomy}}</button>
                                            </div>
                                            <div class="form-group term-add term-add-@{{taxonomy.id}}">
                                                <div class="input-group">
                                                    <input data-ng-model="term.name" type="text" class="form-control" aria-describedby="basic-addon2">
                                                    <span class="input-group-addon" id="basic-addon2">
                                                        <a data-ng-click="term.add()">Add</a> <i data-ng-show="term.isDoing" class="fa fa-spinner fa-spin"></i>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
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