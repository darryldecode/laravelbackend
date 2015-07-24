@extends('backend::layouts.master')

@section('content')

    <div class="row" data-ng-controller="DashboardInfoController">
        <div class="col-lg-6 col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">
                        <i class="fa fa-info-circle"></i> ABOUT
                    </h3>
                </div>
                <div class="panel-body">
                    <table class="table table-bordered">
                        <tr>
                            <td>Version</td>
                            <td>0.1</td>
                        </tr>
                        <tr>
                            <td>Details</td>
                            <td>Initial Release.</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">
                        RELEASES:
                    </h3>
                </div>
                <div class="panel-body">
                    some..
                </div>
            </div>
        </div>
    </div>

@stop