<!doctype html>
<html class="h-100" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Disable DNS Prefetching --}}
    <meta http-equiv="x-dns-prefetch-control" content="off">
    {{-- Disable Link Prefetching (specific to certain browsers): --}}
    {{-- Andrei - this one gives 404 status error --}}
    {{-- <link rel="prefetch" href="..." disabled> --}}

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Scripts -->
    {{-- <script src="{{ asset('js/app.js') }}" defer></script> --}}
    {{-- <script src="{{ asset('js/app.js?v='.filemtime(public_path('js/app.js'))) }}" defer></script> --}}
    {{-- @vite(['resources/css/app.css', 'resources/css/andrei.css', 'resources/js/app.js']) --}}
    @vite(['resources/js/app.js'])

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

    <!-- Styles -->
    {{-- <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('css/andrei.css') }}" rel="stylesheet"> --}}

    {{-- Added to add print style JUST to Word documets  --}}
    @stack('page-styles')

    <!-- Font Awesome links -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
</head>
<body class="d-flex flex-column h-100">
    @auth
    {{-- <div id="app"> --}}
    <header>
        <nav class="navbar navbar-lg navbar-expand-lg navbar-dark shadow culoare1"
            {{-- style="background-color: #2f5c8f" --}}
        >
            <div class="container">
                <a class="navbar-brand me-5" href="{{ url('/') }}">
                    {{ config('app.name', 'Laravel') }}
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        {{-- <li class="nav-item me-3 dropdown">
                            <a class="nav-link active dropdown-toggle" href="about:blank" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fa-solid fa-folder"></i>&nbsp;
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                                <li>
                                    <a class="dropdown-item" href="/file-manager" title="File Manager">
                                        File Manager
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="/file-manager-personalizat" title="File Explorer">
                                        File Explorer
                                    </a>
                                </li>
                            </ul>
                        </li> --}}
                        <li class="nav-item me-3">
                            <a class="nav-link active" aria-current="page" href="/file-manager-personalizat" title="File Explorer">
                                <i class="fa-solid fa-folder"></i>&nbsp;
                            </a>
                        </li>
                        <li class="nav-item me-3">
                            <a class="nav-link active" aria-current="page" href="/acasa" title="Pagina principală">
                                <i class="fa-solid fa-house"></i>&nbsp;
                            </a>
                        </li>
                        <li class="nav-item me-3">
                            <a class="nav-link active" aria-current="page" href="/comenzi">
                                <i class="fa-solid fa-clipboard-list me-1"></i>Comenzi
                            </a>
                        </li>
                        {{-- <li class="nav-item me-3">
                            <a class="nav-link active" aria-current="page" href="/comenzi-statusuri">
                                <i class="fa-solid fa-circle-check me-1"></i>Statusuri
                            </a>
                        </li> --}}
                        <li class="nav-item me-3 dropdown">
                            <a class="nav-link active dropdown-toggle" href="about:blank" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fa-solid fa-list-ul me-1"></i>
                                Resurse
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                                <li>
                                    <a class="dropdown-item" href="/flota-statusuri" target="_blank">
                                        <i class="fa-solid fa-truck me-1"></i>Flotă statusuri
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="/flota-statusuri-c" target="_blank">
                                        <i class="fa-solid fa-truck me-1"></i>Flotă statusuri C
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="/masini-valabilitati" target="_blank">
                                        <i class="fa-solid fa-truck me-1"></i>Mașini valabilități
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="/oferte-curse">
                                        <i class="fa-solid fa-truck me-1"></i>Oferte curse
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="/firme/clienti">
                                        <i class="fa-solid fa-users me-1"></i>Firme Clienți
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="/firme/transportatori">
                                        <i class="fa-solid fa-people-carry-box me-1"></i>Firme Transportatori
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="/camioane">
                                        <i class="fa-solid fa-truck me-1"></i>Camioane
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="/locuri-operare">
                                        <i class="fa-solid fa-location-dot me-1"></i>Locuri de operare
                                    </a>
                                </li>
                            </ul>
                        </li>
                        {{-- <li class="nav-item me-3">
                            <a class="nav-link active" aria-current="page" href="/camioane">
                                <i class="fa-solid fa-truck me-1"></i>Camioane
                            </a>
                        </li>
                        <li class="nav-item me-3">
                            <a class="nav-link active" aria-current="page" href="/locuri-operare">
                                <i class="fa-solid fa-location-dot me-1"></i>Locuri de operare
                            </a>
                        </li> --}}
                        {{-- <li class="nav-item me-3">
                            <a class="nav-link active" aria-current="page" href="/facturi">
                                <i class="fa-solid fa-file-invoice me-1"></i>Facturi
                            </a>
                        </li> --}}
                        <li class="nav-item me-3 dropdown">
                            <a class="nav-link active dropdown-toggle" href="about:blank" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fa-solid fa-table-list me-1"></i>
                                Rapoarte
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                                {{-- Removed on 18.02.2025 - A more complet raport can be found on intermedieri --}}
                                {{-- <li>
                                    <a class="dropdown-item" href="/rapoarte/incasari-utilizatori">
                                        <i class="fa-solid fa-chart-pie me-1"></i>Încasări utilizatori
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li> --}}
                                <li>
                                    <a class="dropdown-item" href="/rapoarte/documente-transportatori">
                                        <i class="fa-solid fa-file me-1"></i>Documente transportatori
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="/intermedieri">
                                        <i class="fa-solid fa-file me-1"></i>Intermedieri
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="/key-performance-indicators">
                                        <i class="fa-solid fa-chart-simple me-1"></i>KPI
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="/facturi-scadente">
                                        <i class="fa-solid fa-file-invoice"></i> Facturi scadente
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li class="nav-item me-3 dropdown">
                            <a class="nav-link active dropdown-toggle" href="about:blank" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-bars me-1"></i>
                                Utile
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                                <li>
                                    <a class="dropdown-item" href="/mementouri/1/mementouri">
                                        <i class="fa-solid fa-bell me-1"></i>Mementouri generale
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="/mementouri/2/mementouri">
                                        <i class="fa-solid fa-bell me-1"></i>Mementouri rca + copii conforme
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="/mementouri/3/mementouri">
                                        <i class="fa-solid fa-bell me-1"></i>Mementouri itp + rovinieta
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="/statii-peco">
                                        <i class="fa-solid fa-gas-pump me-1"></i>Stații peco
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="/documente-word">
                                        <i class="fa-solid fa-file-word me-1"></i>Documente word
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="/facturi">
                                        <i class="fa-solid fa-file-invoice me-1"></i>Facturi
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('mesaje-trimise-sms.index') }}">
                                        <i class="fa-solid fa-comment-sms me-1"></i>SMS trimise
                                    </a>
                                </li>
                                @if (auth()->user()->role == "1")
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('utilizatori.index') }}">
                                            <i class="fa-solid fa-users me-1"></i>Utilizatori
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </li>
                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ms-auto">
                        <!-- Authentication Links -->
                        @guest
                            @if (Route::has('login'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('login') }}">Login</a>
                                </li>
                            @endif

                            @if (Route::has('register'))
                                {{-- <li class="nav-item">
                                    <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                                </li> --}}
                            @endif
                        @else
                            <li class="nav-item dropdown">
                                <a class="nav-link active dropdown-toggle" href="about:blank" id="navbarAuthentication" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    {{ Auth::user()->name }}
                                </a>

                                <ul class="dropdown-menu" aria-labelledby="navbarAuthentication">
                                    <li>
                                        <a class="dropdown-item" href="{{ route('logout') }}"
                                        onclick="event.preventDefault();
                                                        document.getElementById('logout-form').submit();">
                                            Logout
                                        </a>

                                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                            @csrf
                                        </form>
                                    </li>
                                </ul>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>
    </header>
    @else
    {{-- <header class="py-1 culoare1 d-flex justify-content-left" style="">
        <div class="container" style="display: inline-block">
                <img src="{{ asset('imagini/autogns-logo-01-2048x482.png') }}" class="bg-white"
                    style="width: auto; height: auto; max-width: 100%; max-height: 100px;">
        </div>
    </header> --}}
    @endauth

    <main class="flex-shrink-0 py-4">
        @yield('content')
    </main>

    <footer class="mt-auto py-2 text-center text-white culoare1">
        <div class="">
            <p class="mb-1">
                © {{ date('Y') }} {{ config('app.name', 'Laravel') }}
            </p>
            <span class="text-white">
                <a href="https://validsoftware.ro/dezvoltare-aplicatii-web-personalizate/" class="text-white" target="_blank">
                    Aplicație web</a>
                dezvoltată de
                <a href="https://validsoftware.ro/" class="text-white" target="_blank">
                    validsoftware.ro
                </a>
            </span>
        </div>
    </footer>
</body>
</html>
