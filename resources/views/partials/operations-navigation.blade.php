@php
    $links = [
        ['route' => 'facturi-furnizori.facturi.index', 'pattern' => 'facturi-furnizori.facturi.*', 'label' => 'Facturi furnizori'],
        ['route' => 'gestiune-piese.index', 'pattern' => 'gestiune-piese.*', 'label' => 'Gestiune piese'],
        ['route' => 'service-masini.index', 'pattern' => 'service-masini.*', 'label' => 'Service mașini'],
    ];
@endphp

<div class="d-flex flex-wrap justify-content-center justify-content-lg-end gap-2">
    @foreach ($links as $link)
        @php
            $isActive = request()->routeIs($link['pattern']);
            $btnClass = $isActive ? 'btn-primary text-white' : 'btn-outline-primary';
        @endphp
        <a href="{{ route($link['route']) }}" class="btn btn-sm {{ $btnClass }} border border-dark rounded-3">
            {{ $link['label'] }}
        </a>
    @endforeach
</div>
