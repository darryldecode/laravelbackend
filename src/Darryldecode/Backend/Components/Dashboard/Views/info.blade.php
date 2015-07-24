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
                            <td>Current Version</td>
                            <td><label class="label label-info">0.1</label></td>
                        </tr>
                        <tr>
                            <td>Details</td>
                            <td>Pre-Release</td>
                        </tr>
                        <tr>
                            <td>Documentation:</td>
                            <td>
                                <a href="http://laravelbackend.com/docs/1.0/?page=installation.php" target="_blank">http://laravelbackend.com/docs/1.0/?page=installation.php</a>
                            </td>
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
                                        <a href="@{{::r.author.login}}" target="_blank"><small>@{{::r.author.login}}</small></a>
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