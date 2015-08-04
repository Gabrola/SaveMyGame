
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title>@yield('title') - SaveMyGa.me</title>

    <!-- CSS  -->
    <link href="{{ elixir('css/app.css') }}" type="text/css" rel="stylesheet" media="screen,projection"/>
</head>
<body>
<nav class="top-nav-bar" role="navigation">
    <div class="nav-wrapper container"><a id="logo-container" href="{{ url('/') }}" class="brand-logo">SaveMyGa.me</a>
        <ul class="right hide-on-med-and-down main-nav">
            @section('nav-items')
                <li><a href="{{ url('faq') }}">FAQ</a></li>
            @endsection

            @yield('nav-items')
        </ul>

        <ul id="nav-mobile" class="side-nav main-nav">
            @yield('nav-items')
        </ul>
        <a href="#" data-activates="nav-mobile" class="button-collapse"><i class="mdi-navigation-menu"></i></a>
    </div>
</nav>
<main class="page-{{ Route::currentRouteName() }}">
    <div class="section no-pad-bot" style="position: relative">
        <div class="container">
            @if(Session::has('message'))
                <div class="row">
                    <div class="col s12">
                        <div class="card-panel {{ Session::get('message-color', 'teal') }}">
                              <span class="white-text">{{ Session::get('message') }}</span>
                        </div>
                    </div>
                </div>
            @endif

            @yield('content')
        </div>
    </div>
</main>
<footer class="page-footer light-blue darken-1">
    <div class="container">
        <div class="row">
            <div class="col s12 center">
                <small class="white-text">SaveMyGa.me isn't endorsed by Riot Games and doesn't reflect the views or opinions of Riot Games or anyone officially involved in producing or managing League of Legends. League of Legends and Riot Games are trademarks or registered trademarks of Riot Games, Inc. League of Legends © Riot Games, Inc.<br>
                Copyright &copy; {{ date('Y') }} SaveMyGa.me </small>
            </div>
        </div>
    </div>
</footer>


<!--  Scripts-->
<script src="{{ elixir('js/all.js') }}"></script>
</body>
</html>
