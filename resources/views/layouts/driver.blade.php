<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name'))</title>

    @vite(['resources/js/driver.js'])

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-Xx9x5xukdxbYF9bBlt3FqMsTnA4wZ0UnvItg5jGJPD7W82dManIeZDV4SSQdlqzTeWY5Avzkdxl3pNGdisz8ig==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    @stack('driver-styles')
</head>
<body class="driver-layout bg-light">
    <header class="driver-header shadow-sm bg-white">
        <div class="container py-3 d-flex justify-content-between align-items-center">
            <div>
                <h1 class="fs-4 mb-0">@yield('heading', __('Zona șoferului'))</h1>
                <p class="mb-0 text-muted small">{{ __('Gestionați cursele active rapid și în siguranță.') }}</p>
            </div>
            <div class="text-end">
                <a href="{{ route('logout') }}" class="btn btn-outline-secondary btn-sm"
                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    {{ __('Deconectare') }}
                </a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                    @csrf
                </form>
            </div>
        </div>
    </header>

    <main class="py-4">
        <div class="container">
            @yield('content')
        </div>
    </main>

    @stack('driver-scripts')
</body>
</html>
