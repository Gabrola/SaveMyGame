<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title>@yield('title') - SaveMyGame</title>

    <!-- CSS  -->
    <link href="{{ elixir('css/app.css') }}" type="text/css" rel="stylesheet" media="screen,projection"/>

    <link rel="apple-touch-icon" sizes="57x57" href="/apple-touch-icon-57x57.png">
    <link rel="apple-touch-icon" sizes="60x60" href="/apple-touch-icon-60x60.png">
    <link rel="apple-touch-icon" sizes="72x72" href="/apple-touch-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="76x76" href="/apple-touch-icon-76x76.png">
    <link rel="apple-touch-icon" sizes="114x114" href="/apple-touch-icon-114x114.png">
    <link rel="apple-touch-icon" sizes="120x120" href="/apple-touch-icon-120x120.png">
    <link rel="apple-touch-icon" sizes="144x144" href="/apple-touch-icon-144x144.png">
    <link rel="apple-touch-icon" sizes="152x152" href="/apple-touch-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon-180x180.png">
    <link rel="icon" type="image/png" href="/favicon-32x32.png" sizes="32x32">
    <link rel="icon" type="image/png" href="/android-chrome-192x192.png" sizes="192x192">
    <link rel="icon" type="image/png" href="/favicon-96x96.png" sizes="96x96">
    <link rel="icon" type="image/png" href="/favicon-16x16.png" sizes="16x16">
    <link rel="manifest" href="/manifest.json">

    <meta name="msapplication-TileColor" content="#da532c">
    <meta name="msapplication-TileImage" content="/mstile-144x144.png">
    <meta name="theme-color" content="#ffffff">

    <meta name="description" content="SaveMyGame is a service that will automatically record all of your League of Legends game replays without needing you to download or install any 3rd party applications">
    <meta name="keywords" content="lol, league of legends, replay, lolreplay"/>
</head>
<body>
<nav class="top-nav-bar" role="navigation">
    <div class="nav-wrapper container"><a id="logo-container" href="{{ url('/') }}" class="brand-logo">SaveMyGame</a>
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
                        <div class="card-panel {{ Session::get('message_color', 'teal') }} white-text">{{ Session::get('message') }}</div>
                    </div>
                </div>
            @endif

            @if (count($errors) > 0)
                <div class="row">
                    <div class="col s12">
                        <div class="card-panel red white-text">
                            <ul class="error-list">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
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
                <small class="white-text">SaveMyGame isn't endorsed by Riot Games and doesn't reflect the views or opinions of Riot Games or anyone officially involved in producing or managing League of Legends. League of Legends and Riot Games are trademarks or registered trademarks of Riot Games, Inc. League of Legends © Riot Games, Inc.<br>
                    Copyright &copy; {{ date('Y') }} SaveMyGame </small>
            </div>
        </div>
    </div>
</footer>


<!--  Scripts-->
<script src="{{ elixir('js/all.js') }}"></script>
<script>
    (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
        (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
            m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
    })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

    ga('create', 'UA-66134065-1', 'auto');
    ga('send', 'pageview');

</script>
</body>
</html>
