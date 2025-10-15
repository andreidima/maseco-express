@extends('layouts.app')

@php
    use Carbon\Carbon;
    use Illuminate\Support\Str;

    $currentSort = request('sort');
    $currentDirection = strtolower(request('direction', 'desc')) === 'asc' ? 'asc' : 'desc';
@endphp

@section('content')
    <div class="mx-3 px-3 card mx-auto" style="border-radius: 40px 40px 40px 40px;">
        <div class="row card-header align-items-center" style="border-radius: 40px 40px 0px 0px;">
            <div class="col-lg-3">
                <span class="badge culoare1 fs-5">
                    <i class="fa-solid fa-warehouse me-1"></i>Gestiune piese
                </span>
            </div>
            <div class="col-lg-9">
                <form class="needs-validation" novalidate method="GET" action="{{ route('gestiune-piese.index') }}">
                    <div class="row mb-2 custom-search-form justify-content-center">
                        <div class="col-lg-8">
                            <input type="text" class="form-control rounded-3" id="search" name="search"
                                placeholder="Căutare rapidă" value="{{ $search }}">
                        </div>
                    </div>

                    @if (!empty($columns))
                        <div class="text-end">
                            <button class="btn btn-link text-decoration-none" type="button" data-bs-toggle="collapse"
                                data-bs-target="#advancedFilters" aria-expanded="{{ request()->has('filters') ? 'true' : 'false' }}"
                                aria-controls="advancedFilters">
                                <i class="fa-solid fa-sliders me-1"></i>Filtre avansate
                            </button>
                        </div>
                        <div class="collapse {{ request()->has('filters') ? 'show' : '' }}" id="advancedFilters">
                            <div class="row g-3 mt-1">
                                @foreach ($columns as $column)
                                    @php
                                        $label = $column === 'factura_data_factura'
                                            ? 'Data factură'
                                            : Str::of($column)->replace('_', ' ')->title();
                                        $rawFilterValue = $filters[$column] ?? '';
                                        $filterValue = is_array($rawFilterValue) ? implode(', ', $rawFilterValue) : $rawFilterValue;
                                    @endphp
                                    <div class="col-md-4">
                                        <label class="form-label small text-uppercase text-muted" for="filter_{{ $column }}">
                                            {{ $label }}
                                        </label>
                                        <input type="text" class="form-control form-control-sm rounded-3"
                                            id="filter_{{ $column }}" name="filters[{{ $column }}]" value="{{ $filterValue }}">
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="row custom-search-form justify-content-center mt-3">
                        <button class="btn btn-sm btn-primary text-white col-md-4 me-3 border border-dark rounded-3"
                            type="submit">
                            <i class="fas fa-search text-white me-1"></i>Caută
                        </button>
                        <a class="btn btn-sm btn-secondary text-white col-md-4 border border-dark rounded-3"
                            href="{{ route('gestiune-piese.index') }}" role="button">
                            <i class="far fa-trash-alt text-white me-1"></i>Resetează căutarea
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card-body px-0 py-3">
            @include('errors')

            @if (! $hasTable)
                <div class="alert alert-warning mx-3" role="alert">
                    Datele din <code>gestiune_piese</code> nu sunt disponibile în acest mediu.
                </div>
            @endif

            @if ($loadError)
                <div class="alert alert-danger mx-3" role="alert">
                    {{ $loadError }}
                </div>
            @endif

            @if ($hasTable && $items)
                <div class="table-responsive rounded-3">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th class="culoare2 text-white" style="min-width: 70px;">#</th>
                                @foreach ($columns as $column)
                                    @php
                                        $label = $column === 'factura_data_factura'
                                            ? 'Data factură'
                                            : Str::of($column)->replace('_', ' ')->title();
                                        $isSorted = $currentSort === $column;
                                        $nextDirection = $isSorted && $currentDirection === 'asc' ? 'desc' : 'asc';
                                        $query = array_merge(request()->query(), [
                                            'sort' => $column,
                                            'direction' => $nextDirection,
                                        ]);
                                    @endphp
                                    <th class="culoare2 text-white">
                                        <a class="text-white text-decoration-none" href="{{ route('gestiune-piese.index', $query) }}">
                                            {{ $label }}
                                            @if ($isSorted)
                                                <i class="fa-solid fa-arrow-{{ $currentDirection === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                            @endif
                                        </a>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($items as $row)
                                <tr>
                                    <td>
                                        {{ ($items->currentPage() - 1) * $items->perPage() + $loop->index + 1 }}
                                    </td>
                                    @foreach ($columns as $column)
                                        @php
                                            $value = $row->{$column} ?? null;

                                            if ($column === 'factura_data_factura' && $value) {
                                                try {
                                                    $value = Carbon::parse($value)->format('d.m.Y');
                                                } catch (\Throwable $exception) {
                                                    // Leave the raw value if parsing fails
                                                }
                                            }
                                        @endphp
                                        <td>
                                            {{ $value !== null && $value !== '' ? $value : '—' }}
                                        </td>
                                    @endforeach
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ count($columns) + 1 }}" class="text-center text-muted py-4">
                                        Nu există înregistrări care să corespundă filtrelor alese.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-center">
                    {{ $items->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
