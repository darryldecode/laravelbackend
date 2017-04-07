@extends('backend::layouts.master')

@section('content')
    <div data-ng-controller="MediaController" id="inner-content" class="animated fadeIn">

        <!-- list files types -->
        <div class="panel panel-default">
            <div class="panel-body">
                <div class="row view-header">
                    <div class="col-lg-6 col-md-6">
                        <h3>
                            <i class="fa fa-files-o"></i> MEDIA
                        </h3>
                    </div>
                    <div class="col-lg-6 col-md-6">

                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <div id="elfinder"></div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /list files types -->

    </div>
@stop