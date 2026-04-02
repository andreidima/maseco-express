@php
    $links = [
        [
            'route' => 'facturi-furnizori.facturi.index',
            'pattern' => 'facturi-furnizori.facturi.*',
            'label' => 'Facturi furnizori',
            'permission' => 'facturi-furnizori',
        ],
        [
            'route' => 'facturi-transportatori.index',
            'pattern' => 'facturi-transportatori.index',
            'label' => 'Facturi transportatori',
            'permission' => 'facturi',
        ],
        [
            'route' => 'gestiune-piese.index',
            'pattern' => 'gestiune-piese.*',
            'label' => 'Gestiune piese',
            'permission' => 'gestiune-piese',
        ],
        [
            'route' => 'service-masini.index',
            'pattern' => 'service-masini.*',
            'label' => 'Service mașini',
            'permission' => 'service-masini',
        ],
    ];
@endphp

<div class="d-flex flex-wrap justify-content-center justify-content-lg-end gap-2">
    @foreach ($links as $link)
        @can($link['permission'])
            @php
                $isActive = request()->routeIs($link['pattern']);
                $btnClass = $isActive ? 'btn-primary text-white' : 'btn-outline-primary';
            @endphp
            <a href="{{ route($link['route']) }}" class="btn btn-sm {{ $btnClass }} border border-dark rounded-3">
                {{ $link['label'] }}
            </a>
        @endcan
    @endforeach
</div>
