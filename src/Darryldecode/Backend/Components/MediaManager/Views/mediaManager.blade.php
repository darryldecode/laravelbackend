@extends('backend::layouts.master')

@section('content')
    <div data-ng-controller="MediaManagerController" id="inner-content" class="animated fadeIn">

        <!-- list files types -->
        <div class="panel panel-default">
            <div class="panel-body">
                <div class="row view-header">
                    <div class="col-lg-6 col-md-6">
                        <h3>
                            <i class="fa fa-files-o"></i> MEDIA MANAGER
                        </h3>
                    </div>
                    <div class="col-lg-6 col-md-6">
                        <div class="control-panel text-right">
                            <button data-ng-click="mediaManager.mkDir()" class="btn btn-default">
                                <span class="fa-stack fa-lg">
                                  <i class="fa fa-folder-o fa-stack-2x"></i>
                                  <i class="fa fa-plus fa-stack-1x"></i>
                                </span>
                            </button>
                            <div class="button btn btn-default" data-ng-file-select data-ng-model="uploader.files" data-ng-multiple="true">
                                <i class="fa fa-upload"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row view-header-secondary">
                    <div class="col-lg-6 col-md-6">
                        <ol class="breadcrumb">
                            <li>
                                <a data-ng-click="mediaManager.ls('/')" href=""><i class="fa fa-home"></i></a>
                            </li>
                            <li data-ng-repeat="path in files.paths" data-ng-if="$index!=0" class="bc-item">
                                <a data-ng-click="mediaManager.lsBc($index)" href="">@{{path}}</a>
                            </li>
                        </ol>
                    </div>
                    <div class="col-lg-6 col-md-6">
                    </div>
                </div>
                <div class="row">
                    <div data-ng-if="mediaManager.isEmpty" class="alert text-center">
                        No files..
                    </div>
                    <div data-ng-repeat="f in files.directories" class="col-lg-2 col-md-2 col-sm-4">
                        <div class="file-thumb">
                            <i class="fa fa-folder fa-thumb-icon"></i>
                            <div class="text-wrap meta-data-holder trim-info-xs">
                                <a data-ng-click="mediaManager.ls(f)" href="" class="file-name">@{{getLastSegment(f)}}</a>
                            </div>
                            <div class="file-control text-center">
                                <a data-ng-click="mediaManager.mv(f)" href=""><i class="fa fa-pencil"></i></a>
                                <a data-ng-click="mediaManager.rmrf(f)" href=""><i class="fa fa-trash"></i></a>
                            </div>
                        </div>
                    </div>
                    <div data-ng-repeat="f in files.files" class="col-lg-2 col-md-2 col-sm-4">
                        <div class="file-thumb">
                            <label class="label label-info pull-left label-file-size">Size: @{{::getSizeName(f)}}</label>
                            <file-preview file-source="@{{f}}"></file-preview>
                            <div class="text-wrap meta-data-holder trim-info-xs">
                                <a href="" data-toggle="tooltip" data-placement="bottom" title="@{{f}}" class="file-name">@{{::getLastSegment(f)}}</a>
                            </div>
                            <div class="file-control text-center">
                                <a data-ng-click="mediaManager.download(f)" href=""><i class="fa fa-download"></i></a>
                                <a data-ng-click="mediaManager.showLink(f)" href=""><i class="fa fa-link"></i></a>
                                <a data-ng-click="mediaManager.mv(f)" href=""><i class="fa fa-pencil"></i></a>
                                <a data-ng-click="mediaManager.rm(f)" href=""><i class="fa fa-trash"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /list files types -->

    </div>
@stop