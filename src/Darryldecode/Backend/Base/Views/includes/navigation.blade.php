<nav class="navbar navbar-default navbar-fixed-top">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-8">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="{{url(config('backend.backend.base_url').'/dashboard')}}">BACKEND</a>
        </div>

        <!-- Collect the nav links, forms, and other content for toggling -->
        <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-8">
            <ul class="nav navbar-nav navbar-right">
                <li data-ng-repeat="nav in navigation" class="@{{(nav.dropdown) ? 'dropdown' : ''}}">

                    <!-- show if menu is not dropdown -->
                    <a data-ng-if="!nav.dropdown" href="@{{::nav.link}}" popover-placement="bottom" popover="@{{::nav.label}}" popover-trigger="mouseenter"><i class="@{{::nav.icon}}"></i></a>

                    <!-- show if menu is a dropdown -->
                    <a data-ng-if="nav.dropdown" href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">@{{::nav.label}}<span class="caret"></span></a>
                    <ul data-ng-if="nav.dropdown" class="dropdown-menu" role="menu">
                        <li data-ng-if="nav.subMenus.length==0"><a>No available menu..</a></li>
                        <li data-ng-repeat="subNav in nav.subMenus">
                            <a href="@{{::subNav.link}}"><i class="@{{::nav.icon}}"></i> @{{::subNav.label}}</a>
                        </li>
                    </ul>

                </li>
                <li class="bc-user-dashboard-control">
                    <a popover-placement="bottom" popover="Logout" popover-trigger="mouseenter" href="{{url(config('backend.backend.base_url').'/logout')}}"><b>{{Auth::user()->first_name}}</b> <i class="fa fa-power-off"></i></a>
                </li>
            </ul>
        </div><!-- /.navbar-collapse -->
    </div>
</nav>

