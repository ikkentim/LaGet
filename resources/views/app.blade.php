<!DOCTYPE html>
<html>
<head>
    <title>{{ config('laget.shortname') }} - @yield('title')</title>

    <link href="{{ elixir('css/app.css') }}" rel="stylesheet" type='text/css'>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <!--Let browser know website is optimized for mobile-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
</head>
<body>
<header>
    <nav class="top-nav">
        <div class="container">
            <div class="nav-wrapper">
                <a href="#" data-activates="slide-out" class="button-collapse top-nav full hide-on-large-only">
                    <i class="mdi-navigation-menu"></i>
                </a>

                <a href="{{ route('home') }}" class="brand-logo">{{ config('laget.shortname') }}</a>

                <ul class="right hide-on-med-and-down">
                    <li><a href="{{ route('home') }}">Home</a></li>
                    <li><a href="{{ route('packages.index', ['by' => 'most', 'count' => 30]) }}">Browse</a></li>
                </ul>
            </div>
        </div>

    </nav>
    <div class="container">
        <ul id="slide-out" class="side-nav">
            <li class="bold"><a href="{{ route('home') }}">Home</a></li>
            <li class="bold"><a href="{{ route('packages.index', ['by' => 'most', 'count' => 30]) }}">Browse</a></li>
            <li style="border-bottom: 1px solid #ddd;"></li>
            @foreach(config('laget.links') as $link)
                <li class="bold">
                    <a href="{{ $link['href'] }}">{!! $link['title'] !!}</a>
                </li>
            @endforeach
        </ul>
    </div>
</header>
<main>
    @yield('content')
</main>
<footer class="page-footer">
    <div class="container">
        <div class="row">
            <div class="col l6 s12">
                <h5 class="white-text">{{ config('laget.name') }}</h5>

                <p class="grey-text text-lighten-4">
                    {!! config('laget.description') !!}
                </p>
            </div>
            <div class="col l4 offset-l2 s12">
                @if(!empty(config('laget.links')) && config('laget.display_links'))
                <h5 class="white-text">Links</h5>
                <ul>
                    @foreach(config('laget.links') as $link)
                        <li>
                            <a class="grey-text text-lighten-3" href="{{ $link['href'] }}">{!! $link['title'] !!}</a>
                        </li>
                    @endforeach
                </ul>
                @endif
            </div>
        </div>
    </div>
    <div class="footer-copyright">
        <div class="container">
            Powered by <a class="grey-text text-lighten-3" href="https://github.com/ikkentim/LaGet">LaGet</a>.
        </div>
    </div>
</footer>

<!--Import jQuery before materialize.js-->
<script type="text/javascript" src="https://code.jquery.com/jquery-2.1.1.min.js"></script>
<script type="text/javascript" src="/js/bin/materialize.js"></script>

<script>
    $(function () {
        // Initialize collapse button
        $(".button-collapse").sideNav();
        // Initialize collapsible (uncomment the line below if you use the dropdown variation)
        // $('.collapsible').collapsible();
    });
</script>
</body>
</html>
