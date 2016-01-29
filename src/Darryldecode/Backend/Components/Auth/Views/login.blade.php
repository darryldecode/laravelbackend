<!DOCTYPE html>
<html lang="en" data-ng-app="cb">
<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{Config::get('backend.backend.backend_title')}}</title>

    @include('backend::includes.styles')

    <style>
        .lb-login-title {
            font-family: dancing_script, "arial narrow", "Helvetica Neue", Helvetica, Arial, "Lucida Grande", Verdana, "Bitstream Vera Sans", sans-serif;
            margin-top: 50px;
        }
        .lb-login-form {
            max-width: 400px;
            margin: 60px auto 20px auto;
            padding: 20px;
            border: 1px solid #eaeaea;
            border-radius: 5px;
        }
    </style>

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

</head>
<body>
<div class="container">
    <h1 class="text-center lb-login-title">{{Config::get('backend.backend.backend_title')}}</h1>
    <div class="lb-login-form">
        @if($errors->first())
            <div class="alert alert-danger">
                {{$errors->first()}}
            </div>
        @endif
        <form method="POST" action="{{ route('backend.authManager.postLogin') }}">
            {!! csrf_field() !!}
            <div class="form-group">
                Email
                <input class="form-control" type="email" name="email" value="{{ old('email') }}">
            </div>
            <div class="form-group">
                Password
                <input class="form-control" type="password" name="password" id="password">
            </div>
            <div class="form-group">
                <input type="checkbox" name="remember"> Remember Me
            </div>
            <div class="form-group">
                <input class="form-control" type="hidden" name="ru" value="{{(isset($_GET['ru']) ? $_GET['ru'] : '')}}">
                <button class="btn btn-default" type="submit">Login</button>
            </div>
        </form>
    </div>
</div>
</body>
</html>