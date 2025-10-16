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
    $stockDetails = $stockDetails ?? [];
@endphp

@section('content')
    <div class="mx-3 px-3 card mx-auto" style="border-radius: 40px 40px 40px 40px;">
        <div class="row card-header align-items-center" style="border-radius: 40px 40px 0px 0px;">
            <div class="col-lg-3 mb-2 mb-lg-0">
                <span class="badge culoare1 fs-5">
                    <i class="fa-solid fa-warehouse me-1"></i>Gestiune piese
                </span>
            </div>
            <div class="col-lg-6 mb-2" id="formularGestiunePiese">
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
            <div class="col-lg-3 mt-2 mt-lg-0">
                @include('partials.operations-navigation')
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
                                <th class="culoare2 text-white" style="min-width: 150px;">Detalii stoc</th>
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
                                        $pieceId = isset($row->id) ? (int) $row->id : 0;
                                        $stockInfo = $stockDetails[$pieceId] ?? null;
                                        $pieceName = $row->denumire ?? '';
                                        $pieceCode = $row->cod ?? '';
                                        $initialValue = $stockInfo['initial'] ?? null;
                                        $remainingValue = $stockInfo['remaining'] ?? null;
                                        $usedValue = $stockInfo['used'] ?? 0;
                                        $machinesData = $stockInfo['machines'] ?? [];
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
                                    @php
                                        $initialDisplay = $initialValue !== null ? number_format((float) $initialValue, 2, '.', '') : '';
                                        $remainingDisplay = $remainingValue !== null ? number_format((float) $remainingValue, 2, '.', '') : '';
                                        $usedDisplay = number_format((float) $usedValue, 2, '.', '');
                                    @endphp
                                    <td class="text-center">
                                        @if ($pieceId > 0)
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-outline-secondary"
                                                data-bs-toggle="modal"
                                                data-bs-target="#stockDetailsModal"
                                                data-piece-name="{{ $pieceName }}"
                                                data-piece-code="{{ $pieceCode }}"
                                                data-piece-initial="{{ $initialDisplay }}"
                                                data-piece-remaining="{{ $remainingDisplay }}"
                                                data-piece-used="{{ $usedDisplay }}"
                                                data-piece-machines='@json($machinesData ?? [])'
                                            >
                                                <i class="fa-solid fa-circle-info me-1"></i>Detalii
                                            </button>
                                        @else
                                            —
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ count($columns) + 2 + ($invoiceColumn ? 1 : 0) }}"
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

<div class="modal fade" id="stockDetailsModal" tabindex="-1" aria-labelledby="stockDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="stockDetailsModalLabel">Detalii stoc</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Închide"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <h5 class="mb-1" data-stock-details="name">—</h5>
                    <div class="text-muted small d-none" data-stock-details="code"></div>
                </div>
                <dl class="row mb-4">
                    <dt class="col-sm-5 col-lg-4">Cantitate inițială</dt>
                    <dd class="col-sm-7 col-lg-8" data-stock-details="initial">—</dd>
                    <dt class="col-sm-5 col-lg-4">Cantitate alocată</dt>
                    <dd class="col-sm-7 col-lg-8" data-stock-details="used">0.00</dd>
                    <dt class="col-sm-5 col-lg-4">Cantitate rămasă</dt>
                    <dd class="col-sm-7 col-lg-8" data-stock-details="remaining">—</dd>
                </dl>
                <h6 class="mb-2">Alocări pe mașini</h6>
                <div class="table-responsive mb-2">
                    <table class="table table-sm table-striped mb-0">
                        <thead>
                            <tr>
                                <th>Mașină</th>
                                <th class="text-end">Cantitate</th>
                            </tr>
                        </thead>
                        <tbody data-stock-details="machines-body"></tbody>
                    </table>
                </div>
                <div class="text-muted" data-stock-details="machines-empty">Nu există alocări pentru această piesă.</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Închide</button>
            </div>
        </div>
    </div>
</div>

@push('page-scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modal = document.getElementById('stockDetailsModal');

            if (!modal) {
                return;
            }

            const nameEl = modal.querySelector('[data-stock-details="name"]');
            const codeEl = modal.querySelector('[data-stock-details="code"]');
            const initialEl = modal.querySelector('[data-stock-details="initial"]');
            const usedEl = modal.querySelector('[data-stock-details="used"]');
            const remainingEl = modal.querySelector('[data-stock-details="remaining"]');
            const machinesBody = modal.querySelector('[data-stock-details="machines-body"]');
            const noMachinesEl = modal.querySelector('[data-stock-details="machines-empty"]');

            modal.addEventListener('show.bs.modal', function (event) {
                const trigger = event.relatedTarget;

                if (!trigger) {
                    return;
                }

                const name = trigger.getAttribute('data-piece-name') || '—';
                const code = trigger.getAttribute('data-piece-code') || '';
                const initial = trigger.getAttribute('data-piece-initial');
                const remaining = trigger.getAttribute('data-piece-remaining');
                const used = trigger.getAttribute('data-piece-used');
                const machinesJson = trigger.getAttribute('data-piece-machines') || '[]';

                let machines = [];

                try {
                    const parsed = JSON.parse(machinesJson);
                    machines = Array.isArray(parsed) ? parsed : [];
                } catch (error) {
                    machines = [];
                }

                nameEl.textContent = name || '—';

                if (code) {
                    codeEl.textContent = `Cod: ${code}`;
                    codeEl.classList.remove('d-none');
                } else {
                    codeEl.textContent = '';
                    codeEl.classList.add('d-none');
                }

                initialEl.textContent = initial && initial !== '' ? initial : '—';
                usedEl.textContent = used && used !== '' ? used : '0.00';
                remainingEl.textContent = remaining && remaining !== '' ? remaining : '—';

                machinesBody.innerHTML = '';

                if (!machines.length) {
                    noMachinesEl.classList.remove('d-none');
                    return;
                }

                noMachinesEl.classList.add('d-none');

                machines.forEach((machine) => {
                    const row = document.createElement('tr');
                    const masinaCell = document.createElement('td');
                    const number = machine.numar_inmatriculare || '';
                    const label = machine.denumire || '';
                    masinaCell.textContent = number
                        ? label
                            ? `${number} – ${label}`
                            : number
                        : label || '—';

                    const qtyCell = document.createElement('td');
                    qtyCell.className = 'text-end';
                    const qty = Number.parseFloat(machine.cantitate ?? 0);
                    qtyCell.textContent = Number.isFinite(qty) ? qty.toFixed(2) : '0.00';

                    row.appendChild(masinaCell);
                    row.appendChild(qtyCell);
                    machinesBody.appendChild(row);
                });
            });
        });
    </script>
@endpush
