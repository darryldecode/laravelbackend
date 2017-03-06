@extends('backend::layouts.master')

@section('content')

    @forelse($widgets as $widget)
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
    @empty
        <div class="panel panel-default">
            <div class="panel-body text-center" style="min-height: 230px; padding-top: 100px;">
                <i>No widgets yet..</i>
            </div>
        </div>
    @endforelse

    <div class="text-center">
        <small>© Laravel Backend | <a href="{{route('dashboard.info')}}">ABOUT <i class="fa fa-info-circle"></i></a></small>
    </div>
@stop