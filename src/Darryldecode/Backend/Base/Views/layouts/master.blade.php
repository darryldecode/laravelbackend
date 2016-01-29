<!DOCTYPE html>
<html lang="en" data-ng-app="cb">
<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{Config::get('backend.backend.backend_title')}}</title>

    @include('backend::includes.styles')

    <script type="text/javascript">
        var BASE_URL  = '{{config('app.url')}}',
            ADMIN_URL = '{{config('app.url').'/'.config('backend.backend.base_url')}}',
            STORAGE_URL = '{{config('app.url').'/uploads/'}}',
                _CSRF = '{{csrf_token()}}';
    </script>

    <!-- added header scripts-->
    @foreach($app['backend']->getAddedHeaderScripts() as $links)
        @foreach($links as $linkType => $links)
            @if($linkType == 'css')
                @foreach($links as $link)
                    <link type="text/css" rel="stylesheet" href="{{$link}}"/>
                @endforeach
            @endif
            @if($linkType == 'js')
                @foreach($links as $link)
                    <script src="{{$link}}" type="text/javascript"></script>
                @endforeach
            @endif
        @endforeach
    @endforeach
    <!-- /added header scripts-->

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

</head>
<body id="backend">

    <div data-ng-controller="MasterController" class="container" id="backend-wrapper" data-user-id="{{Auth::user()->id}}">

        @include('backend::includes.navigation')

        <div id="content">
            @yield('content')
        </div>

    </div>

    @include('backend::includes.scripts')

    <!-- added footer scripts-->
    @foreach($app['backend']->getAddedFooterScripts() as $links)
        @foreach($links as $linkType => $links)
            @if($linkType == 'css')
                @foreach($links as $link)
                    <link type="text/css" rel="stylesheet" href="{{$link}}"/>
                @endforeach
            @endif
            @if($linkType == 'js')
                @foreach($links as $link)
                    <script src="{{$link}}" type="text/javascript"></script>
                @endforeach
            @endif
        @endforeach
    @endforeach
    <!-- /added footer scripts-->

</body>

</html>