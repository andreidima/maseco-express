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
    @php
        $facturiIndexUrl = \App\Support\FacturiFurnizori\FacturiIndexFilterState::route();
        $impersonationActive = session()->has('impersonated_by');
        $impersonatorName = session('impersonated_by_name');
        $user = auth()->user();

        $brandHref = url('/');
        if ($user) {
            if ($user->hasPermission('gestiune-piese')) {
                $brandHref = route('gestiune-piese.index');
            } elseif ($user->hasPermission('service-masini')) {
                $brandHref = route('service-masini.index');
            } elseif ($user->hasPermission('dashboard')) {
                $brandHref = route('dashboard');
            }
        }

        $prepareDropdown = function (array $items) use ($user) {
            $filtered = array_values(array_filter($items, function ($item) use ($user) {
                if (($item['type'] ?? 'link') === 'divider') {
                    return true;
                }

                if (! isset($item['permission'])) {
                    return true;
                }

                return $user && $user->hasPermission($item['permission']);
            }));

            while (! empty($filtered) && (($filtered[0]['type'] ?? 'link') === 'divider')) {
                array_shift($filtered);
            }

            while (! empty($filtered) && ((end($filtered)['type'] ?? 'link') === 'divider')) {
                array_pop($filtered);
            }

            $cleaned = [];
            foreach ($filtered as $entry) {
                if (($entry['type'] ?? 'link') === 'divider'
                    && ! empty($cleaned)
                    && (($cleaned[array_key_last($cleaned)]['type'] ?? 'link') === 'divider')) {
                    continue;
                }

                $cleaned[] = $entry;
            }

            return $cleaned;
        };

        $primaryNavLinks = array_values(array_filter([
            [
                'permission' => 'documente',
                'href' => '/file-manager-personalizat',
                'icon' => 'fa-solid fa-folder',
                'label' => null,
                'title' => 'File Explorer',
            ],
            [
                'permission' => 'dashboard',
                'href' => '/acasa',
                'icon' => 'fa-solid fa-house',
                'label' => null,
                'title' => 'Pagina principală',
            ],
            [
                'permission' => 'comenzi',
                'href' => '/comenzi',
                'icon' => 'fa-solid fa-clipboard-list',
                'label' => 'Comenzi',
            ],
        ], function ($link) use ($user) {
            return ! isset($link['permission']) || ($user && $user->hasPermission($link['permission']));
        }));

        $resurseDropdownItems = $prepareDropdown([
            [
                'permission' => 'comenzi',
                'href' => '/flota-statusuri',
                'icon' => 'fa-solid fa-truck',
                'label' => 'Flotă statusuri',
                'target' => '_blank',
            ],
            ['type' => 'divider'],
            [
                'permission' => 'comenzi',
                'href' => '/flota-statusuri-c',
                'icon' => 'fa-solid fa-truck',
                'label' => 'Flotă statusuri C',
                'target' => '_blank',
            ],
            ['type' => 'divider'],
            [
                'permission' => 'service-masini',
                'href' => '/masini-valabilitati',
                'icon' => 'fa-solid fa-truck',
                'label' => 'Mașini valabilități',
                'target' => '_blank',
            ],
            ['type' => 'divider'],
            [
                'permission' => 'comenzi',
                'href' => '/oferte-curse',
                'icon' => 'fa-solid fa-truck',
                'label' => 'Oferte curse',
            ],
            ['type' => 'divider'],
            [
                'permission' => 'firme',
                'href' => '/firme/clienti',
                'icon' => 'fa-solid fa-users',
                'label' => 'Firme Clienți',
            ],
            ['type' => 'divider'],
            [
                'permission' => 'firme',
                'href' => '/firme/transportatori',
                'icon' => 'fa-solid fa-people-carry-box',
                'label' => 'Firme Transportatori',
            ],
            ['type' => 'divider'],
            [
                'permission' => 'camioane',
                'href' => '/camioane',
                'icon' => 'fa-solid fa-truck',
                'label' => 'Camioane',
            ],
            ['type' => 'divider'],
            [
                'permission' => 'locuri-operare',
                'href' => '/locuri-operare',
                'icon' => 'fa-solid fa-location-dot',
                'label' => 'Locuri de operare',
            ],
        ]);

        $rapoarteDropdownItems = $prepareDropdown([
            [
                'permission' => 'rapoarte',
                'href' => '/rapoarte/documente-transportatori',
                'icon' => 'fa-solid fa-file',
                'label' => 'Documente transportatori',
            ],
            ['type' => 'divider'],
            [
                'permission' => 'comenzi',
                'href' => '/intermedieri',
                'icon' => 'fa-solid fa-file',
                'label' => 'Intermedieri',
            ],
            ['type' => 'divider'],
            [
                'permission' => 'rapoarte',
                'href' => '/key-performance-indicators',
                'icon' => 'fa-solid fa-chart-simple',
                'label' => 'KPI',
            ],
            ['type' => 'divider'],
            [
                'permission' => 'facturi',
                'href' => '/facturi-scadente',
                'icon' => 'fa-solid fa-file-invoice',
                'label' => 'Facturi scadente',
            ],
        ]);

        $utileDropdownItems = $prepareDropdown([
            [
                'permission' => 'mementouri',
                'href' => '/mementouri/1/mementouri',
                'icon' => 'fa-solid fa-bell',
                'label' => 'Mementouri generale',
            ],
            [
                'permission' => 'mementouri',
                'href' => '/mementouri/2/mementouri',
                'icon' => 'fa-solid fa-bell',
                'label' => 'Mementouri rca + copii conforme',
            ],
            [
                'permission' => 'mementouri',
                'href' => '/mementouri/3/mementouri',
                'icon' => 'fa-solid fa-bell',
                'label' => 'Mementouri itp + rovinieta',
            ],
            ['type' => 'divider'],
            [
                'permission' => 'comenzi',
                'href' => '/statii-peco',
                'icon' => 'fa-solid fa-gas-pump',
                'label' => 'Stații peco',
            ],
            ['type' => 'divider'],
            [
                'permission' => 'documente',
                'href' => '/documente-word',
                'icon' => 'fa-solid fa-file-word',
                'label' => 'Documente word',
            ],
            ['type' => 'divider'],
            [
                'permission' => 'facturi',
                'href' => '/facturi',
                'icon' => 'fa-solid fa-file-invoice',
                'label' => 'Facturi',
            ],
            ['type' => 'divider'],
            [
                'permission' => 'facturi-furnizori',
                'href' => $facturiIndexUrl,
                'icon' => 'fa-solid fa-file-invoice-dollar',
                'label' => 'Facturi furnizori',
            ],
            [
                'permission' => 'gestiune-piese',
                'href' => route('gestiune-piese.index'),
                'icon' => 'fa-solid fa-boxes-stacked',
                'label' => 'Gestiune piese',
            ],
            [
                'permission' => 'service-masini',
                'href' => route('service-masini.index'),
                'icon' => 'fa-solid fa-screwdriver-wrench',
                'label' => 'Service mașini',
            ],
            ['type' => 'divider'],
            [
                'permission' => 'mesagerie',
                'href' => route('mesaje-trimise-sms.index'),
                'icon' => 'fa-solid fa-comment-sms',
                'label' => 'SMS trimise',
            ],
        ]);
    @endphp
    {{-- <div id="app"> --}}
    <header>
        <nav class="navbar navbar-lg navbar-expand-lg navbar-dark shadow culoare1"
            {{-- style="background-color: #2f5c8f" --}}
        >
            <div class="container">
                <a class="navbar-brand me-5" href="{{ $brandHref }}">
                    {{ config('app.name', 'Laravel') }}
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        @foreach ($primaryNavLinks as $link)
                            <li class="nav-item me-3">
                                <a class="nav-link active" href="{{ $link['href'] }}" aria-current="page"
                                    @if (! empty($link['title'])) title="{{ $link['title'] }}" @endif>
                                    <i class="{{ $link['icon'] }}{{ ! empty($link['label']) ? ' me-1' : '' }}"></i>
                                    @if (! empty($link['label']))
                                        {{ $link['label'] }}
                                    @else
                                        &nbsp;
                                    @endif
                                </a>
                            </li>
                        @endforeach

                        @if (! empty($resurseDropdownItems))
                            <li class="nav-item me-3 dropdown">
                                <a class="nav-link active dropdown-toggle" href="about:blank" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fa-solid fa-list-ul me-1"></i>
                                    Resurse
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                                    @foreach ($resurseDropdownItems as $item)
                                        @if (($item['type'] ?? 'link') === 'divider')
                                            <li><hr class="dropdown-divider"></li>
                                        @else
                                            <li>
                                                <a class="dropdown-item" href="{{ $item['href'] }}" @if (! empty($item['target'])) target="{{ $item['target'] }}" @endif>
                                                    <i class="{{ $item['icon'] }} me-1"></i>{{ $item['label'] }}
                                                </a>
                                            </li>
                                        @endif
                                    @endforeach
                                </ul>
                            </li>
                        @endif

                        @if (! empty($rapoarteDropdownItems))
                            <li class="nav-item me-3 dropdown">
                                <a class="nav-link active dropdown-toggle" href="about:blank" id="navbarReports" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fa-solid fa-table-list me-1"></i>
                                    Rapoarte
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="navbarReports">
                                    @foreach ($rapoarteDropdownItems as $item)
                                        @if (($item['type'] ?? 'link') === 'divider')
                                            <li><hr class="dropdown-divider"></li>
                                        @else
                                            <li>
                                                <a class="dropdown-item" href="{{ $item['href'] }}">
                                                    <i class="{{ $item['icon'] }} me-1"></i>{{ $item['label'] }}
                                                </a>
                                            </li>
                                        @endif
                                    @endforeach
                                </ul>
                            </li>
                        @endif

                        @if (! empty($utileDropdownItems) || Gate::check('users'))
                            <li class="nav-item me-3 dropdown">
                                <a class="nav-link active dropdown-toggle" href="about:blank" id="navbarUtile" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-bars me-1"></i>
                                    Utile
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="navbarUtile">
                                    @foreach ($utileDropdownItems as $item)
                                        @if (($item['type'] ?? 'link') === 'divider')
                                            <li><hr class="dropdown-divider"></li>
                                        @else
                                            <li>
                                                <a class="dropdown-item" href="{{ $item['href'] }}">
                                                    <i class="{{ $item['icon'] }} me-1"></i>{{ $item['label'] }}
                                                </a>
                                            </li>
                                        @endif
                                    @endforeach
                                    @can('users')
                                        @if (! empty($utileDropdownItems))
                                            <li><hr class="dropdown-divider"></li>
                                        @endif
                                        <li>
                                            <a class="dropdown-item" href="{{ route('utilizatori.index') }}">
                                                <i class="fa-solid fa-users me-1"></i>Utilizatori
                                            </a>
                                        </li>
                                    @endcan
                                </ul>
                            </li>
                        @endif

                        @canany(['access-tech', 'access-tech-impersonation'])
                            <li class="nav-item me-3 dropdown">
                                <a class="nav-link active dropdown-toggle" href="about:blank" id="navbarTech" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fa-solid fa-microchip me-1"></i>
                                    Tech
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="navbarTech">
                                    @can('access-tech-impersonation')
                                        <li>
                                            <a class="dropdown-item" href="{{ route('tech.impersonation.index') }}">
                                                <i class="fa-solid fa-user-secret me-1"></i>Impersonare utilizatori
                                            </a>
                                        </li>
                                    @endcan
                                    @can('access-tech')
                                        <li>
                                            <a class="dropdown-item" href="{{ route('tech.migrations.index') }}">
                                                <i class="fa-solid fa-database me-1"></i>Migration Center
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('tech.seeders.index') }}">
                                                <i class="fa-solid fa-seedling me-1"></i>Seeder Center
                                            </a>
                                        </li>
                                    @endcan
                                </ul>
                            </li>
                        @endcanany
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
                            @if ($impersonationActive)
                                <li class="nav-item d-flex align-items-center me-3 gap-2">
                                    <span class="badge bg-warning text-dark text-wrap">
                                        <i class="fa-solid fa-user-secret me-1"></i>
                                        Impersonate
                                    </span>
                                </li>
                            @endif
                            <li class="nav-item dropdown">
                                <a class="nav-link active dropdown-toggle" href="about:blank" id="navbarAuthentication" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    {{ Auth::user()->name }}
                                </a>

                                <ul class="dropdown-menu" aria-labelledby="navbarAuthentication">
                                    @if ($impersonationActive)
                                        <li>
                                            <form method="post" action="{{ route('impersonation.stop') }}" class="d-inline">
                                                @csrf
                                                <button type="submit" class="dropdown-item d-flex align-items-center gap-2"
                                                    title="Revenire la {{ $impersonatorName ?? 'contul inițial' }}">
                                                    <i class="fa-solid fa-person-walking-arrow-loop-left"></i>
                                                    <span>Oprește impersonarea</span>
                                                </button>
                                            </form>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                    @endif
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

    @stack('page-scripts')
</body>
</html>
