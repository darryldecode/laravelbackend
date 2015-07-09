@extends('backend::layouts.master')

@section('content')

    @foreach($widgets as $widget)
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">
                    {{$widget->getWidgetInfo()['name']}}
                    <i><small>{{$widget->getWidgetInfo()['description']}}</small></i>
                </h3>
            </div>
            <div class="panel-body">
                <?php include_once $widget->getWidgetTemplate(); ?>
            </div>
        </div>
    @endforeach

@stop