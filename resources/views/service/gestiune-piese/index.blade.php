@extends('layouts.app')

@php
    use Carbon\Carbon;
    use Illuminate\Support\Str;

    $currentSort = request('sort');
    $currentDirection = strtolower(request('direction', 'desc')) === 'asc' ? 'asc' : 'desc';
    $denumire = $denumire ?? '';
    $cod = $cod ?? '';
    $dataFactura = $dataFactura ?? '';
    $invoiceColumn = $invoiceColumn ?? null;
@endphp

@section('content')
    <div class="mx-3 px-3 card mx-auto" style="border-radius: 40px 40px 40px 40px;">
        <div class="row card-header align-items-center" style="border-radius: 40px 40px 0px 0px;">
            <div class="col-lg-3">
                <span class="badge culoare1 fs-5">
                    <i class="fa-solid fa-warehouse me-1"></i>Gestiune piese
                </span>
            </div>
            <div class="col-lg-9 mb-2" id="formularGestiunePiese">
                <form class="needs-validation mb-lg-0" novalidate method="GET" action="{{ route('gestiune-piese.index') }}">
                    <div class="row gy-1 gx-4 mb-2 custom-search-form d-flex justify-content-center">
                        <div class="col-lg-4 col-md-6">
                            <div class="d-flex align-items-center gap-2">
                                <i class="fa-solid fa-font text-muted" title="Caută după denumire"></i>
                                <input type="text" class="form-control rounded-3 flex-grow-1" id="denumire" name="denumire"
                                    placeholder="Denumire" value="{{ $denumire }}" autocomplete="off">
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6">
                            <div class="d-flex align-items-center gap-2">
                                <i class="fa-solid fa-barcode text-muted" title="Caută după cod"></i>
                                <input type="text" class="form-control rounded-3 flex-grow-1" id="cod" name="cod"
                                    placeholder="Cod" value="{{ $cod }}" autocomplete="off">
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6">
                            <div class="d-flex align-items-center gap-2">
                                <label for="data_factura" class="form-label small text-muted mb-0 flex-shrink-0 text-nowrap">
                                    Data factură
                                </label>
                                <input type="date" class="form-control rounded-3" id="data_factura" name="data_factura"
                                    value="{{ $dataFactura }}">
                            </div>
                        </div>
                    </div>

                    <div class="row custom-search-form justify-content-center mt-2">
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
                    Datele din <code>service_gestiune_piese</code> nu sunt disponibile în acest mediu.
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
                                        <a class="text-white text-decoration-none"
                                            href="{{ route('gestiune-piese.index', $query) }}">
                                            {{ $label }}
                                            @if ($isSorted)
                                                <i class="fa-solid fa-arrow-{{ $currentDirection === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                            @endif
                                        </a>
                                    </th>
                                @endforeach
                                @if ($invoiceColumn)
                                    <th class="culoare2 text-white" style="min-width: 130px;">Factură</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($items as $row)
                                <tr>
                                    <td>
                                        {{ ($items->currentPage() - 1) * $items->perPage() + $loop->index + 1 }}
                                    </td>
                                    @php
                                        $invoiceId = $invoiceColumn ? ($row->{$invoiceColumn} ?? null) : null;
                                    @endphp
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
                                    @if ($invoiceColumn)
                                        <td>
                                            @if ($invoiceId !== null && $invoiceId !== '')
                                                <a class="btn btn-sm btn-outline-primary border-0 rounded-3"
                                                    href="{{ route('facturi-furnizori.facturi.show', $invoiceId) }}">
                                                    <i class="fa-solid fa-file-invoice me-1"></i>Deschide
                                                </a>
                                            @else
                                                —
                                            @endif
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ count($columns) + 1 + ($invoiceColumn ? 1 : 0) }}"
                                        class="text-center text-muted py-4">
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
