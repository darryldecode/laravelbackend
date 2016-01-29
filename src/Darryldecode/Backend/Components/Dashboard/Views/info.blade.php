@extends('backend::layouts.master')

@section('content')

    <div class="row" data-ng-controller="DashboardInfoController">
        <div class="col-lg-12 col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">
                        <i class="fa fa-info-circle"></i> ABOUT
                    </h3>
                </div>
                <div class="panel-body">
                    <table class="table table-bordered">
                        <tr>
                            <td>Current Version</td>
                            <td><label class="label label-info">{{$version['version']}}</label></td>
                        </tr>
                        <tr>
                            <td>Details</td>
                            <td>{{$version['name']}}</td>
                        </tr>
                        <tr>
                            <td>Documentation:</td>
                            <td>
                                <a href="http://laravelbackend.com/docs/1.0/?page=installation.php" target="_blank">Go to docs →</a>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-12 col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">
                        RELEASES:
                    </h3>
                </div>
                <div class="panel-body released-body-list">
                    <ul class="list-group">
                        <li data-ng-repeat="r in releases" class="list-group-item">
                            <table class="table table-striped">
                                <tr>
                                    <td>Version:</td>
                                    <td>
                                        <label class="label label-info">@{{::r.tag_name}}</label> | <b>@{{::r.name}}</b>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Details:</td>
                                    <td>@{{::r.body}}</td>
                                </tr>
                                <tr>
                                    <td>Author:</td>
                                    <td>
                                        <img class="img-circle" data-ng-src="@{{::r.author.avatar_url}}" style="width: 20px;">
                                        <a href="@{{::r.author.html_url}}" target="_blank"><small>@{{::r.author.login}}</small></a>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Release Link:</td>
                                    <td><a href="@{{::r.html_url}}" target="_blank">@{{::r.html_url}}</a></td>
                                </tr>
                            </table>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

@stop