@extends('layouts.app')

@php
    use Carbon\Carbon;

    $facturiIndexUrl = \App\Support\FacturiTransportatori\FacturiIndexFilterState::route();
    $oldActionSource = old('action_source');
    $oldMoveComandaId = (int) collect(old('comenzi', []))->first();
@endphp

@section('content')
<div class="mx-3 px-3 card" style="border-radius: 40px 40px 40px 40px;">
    <div class="row card-header align-items-center" style="border-radius: 40px 40px 0px 0px;">
        <div class="col-lg-6">
            <span class="badge culoare1 fs-5">
                <i class="fa-solid fa-layer-group me-1"></i>Calup: {{ $calup->denumire_calup }}
            </span>
        </div>
        <div class="col-lg-6 text-end">
            <a class="btn btn-sm btn-secondary text-white border border-dark rounded-3" href="{{ $facturiIndexUrl }}">
                <i class="fa-solid fa-rotate-left me-1"></i>Inapoi la facturi
            </a>
        </div>
    </div>

    <div class="card-body px-0 py-3">
        @include('errors')

        <div class="px-3">
            <div class="mb-3">
                <div class="border border-dark rounded-3 p-3 bg-white">
                    <h6 class="text-uppercase text-muted">Sumar calup</h6>
                    <p class="mb-1"><strong>Comenzi atasate:</strong> {{ $comenziCalup->count() }}</p>
                    <p class="mb-1"><strong>Data plata:</strong> {{ $calup->data_plata?->format('d.m.Y') ?: 'Nespecificata' }}</p>
                    <p class="mb-0"><strong>Fisiere PDF:</strong> {{ $calup->fisiere->count() }}</p>
                </div>
            </div>

            <div class="mb-3">
                <div class="border border-dark rounded-3 p-3 bg-white" id="calup-fisiere">
                    <h6 class="text-uppercase text-muted">Fisiere PDF atasate</h6>
                    @if ($calup->fisiere->isEmpty())
                        <p class="text-muted mb-0">Nu exista fisiere atasate acestui calup.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm table-hover table-bordered border-dark align-middle mb-0">
                                <thead class="text-white rounded culoare2">
                                    <tr>
                                        <th>Nume fisier</th>
                                        <th>Incarcat la</th>
                                        <th class="text-end">Actiuni</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($calup->fisiere as $fisier)
                                        <tr>
                                            <td>{{ $fisier->nume_original ?: basename($fisier->cale) }}</td>
                                            <td>{{ $fisier->created_at?->format('d.m.Y H:i') }}</td>
                                            <td class="text-end">
                                                @if ($fisier->isPreviewable())
                                                    <a href="{{ route('facturi-transportatori.calupuri.vizualizeaza-fisier', [$calup, $fisier]) }}" class="btn btn-sm btn-outline-success border border-success me-2" target="_blank" rel="noopener">
                                                        <i class="fa-solid fa-up-right-from-square me-1"></i>Deschide
                                                    </a>
                                                @endif
                                                <a href="{{ route('facturi-transportatori.calupuri.descarca-fisier', [$calup, $fisier]) }}" class="btn btn-sm btn-outline-primary border border-primary me-2">
                                                    <i class="fa-solid fa-download me-1"></i>Descarca
                                                </a>
                                                <form action="{{ route('facturi-transportatori.calupuri.fisiere.destroy', [$calup, $fisier]) }}" method="POST" class="d-inline" onsubmit="return confirm('Stergi acest fisier PDF?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger border border-danger">
                                                        <i class="fa-solid fa-trash me-1"></i>Sterge
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            <form action="{{ route('facturi-transportatori.calupuri.update', $calup) }}" method="POST" enctype="multipart/form-data" class="border border-dark rounded-3 p-3 mb-3 bg-white">
                @csrf
                @method('PUT')
                @include('facturi_transportatori.calupuri._form', ['calup' => $calup])
                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-sm btn-primary text-white border border-dark rounded-3">
                        <i class="fa-solid fa-floppy-disk me-1"></i>Actualizeaza calup
                    </button>
                </div>
            </form>

            <div class="border border-dark rounded-3 p-3 mb-3 bg-white">
                <h6 class="text-uppercase text-muted mb-3">Comenzi atasate</h6>
                @if ($comenziCalup->isEmpty())
                    <p class="text-muted mb-0">Nu sunt comenzi atasate acestui calup.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm table-hover table-bordered border-dark align-middle mb-0">
                            <thead class="text-white rounded culoare2">
                                <tr>
                                    <th>Transportator</th>
                                    <th>Comanda</th>
                                    <th>Factura</th>
                                    <th>Scadenta</th>
                                    <th class="text-end">Suma</th>
                                    <th>PDF</th>
                                    <th class="text-end">Actiuni</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($comenziCalup as $comanda)
                                    <tr>
                                        <td>{{ $comanda->transportator->nume ?? '' }}</td>
                                        <td>{{ $comanda->transportator_contract }}</td>
                                        <td>{{ $comanda->factura_transportator }}</td>
                                        <td>{{ $comanda->data_scadenta_plata_transportator ? Carbon::parse($comanda->data_scadenta_plata_transportator)->format('d.m.Y') : '-' }}</td>
                                        <td class="text-end">
                                            @if (! is_null($comanda->transportator_valoare_contract))
                                                {{ rtrim(rtrim(number_format((float) $comanda->transportator_valoare_contract, 2, '.', ''), '0'), '.') }}
                                                {{ $comanda->transportatorMoneda->nume ?? '' }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            @forelse ($comanda->facturiIncarcateDeTransportator as $fisierFactura)
                                                <div class="mb-1">
                                                    <a href="{{ route('comanda-incarcare-documente-de-catre-transportator.fisiere.deschide', ['cheie_unica' => $comanda->cheie_unica, 'fisierId' => $fisierFactura->id]) }}" target="_blank" rel="noopener">PDF {{ $loop->iteration }}</a>
                                                </div>
                                            @empty
                                                <span class="text-muted">Fara PDF</span>
                                            @endforelse
                                        </td>
                                        <td class="text-end">
                                            <div class="d-flex flex-wrap justify-content-end gap-2">
                                                <button type="button" class="badge bg-primary text-white border-0 rounded-3 px-3 py-2" data-bs-toggle="modal" data-bs-target="#mutaComandaModal{{ $comanda->id }}">
                                                    Muta
                                                </button>
                                                <button type="button" class="badge bg-danger text-white border-0 rounded-3 px-3 py-2" data-bs-toggle="modal" data-bs-target="#detaseazaComandaModal{{ $comanda->id }}">
                                                    Elimina
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            @if ($totaluriPeMoneda->isNotEmpty())
                                <tfoot>
                                    @foreach ($totaluriPeMoneda as $moneda => $total)
                                        <tr class="fw-semibold">
                                            <td colspan="4" class="text-end">Total {{ $moneda }}</td>
                                            <td class="text-end">{{ number_format($total, 2) }} {{ $moneda }}</td>
                                            <td colspan="2"></td>
                                        </tr>
                                    @endforeach
                                </tfoot>
                            @endif
                        </table>
                    </div>
                @endif
            </div>

            @foreach ($comenziCalup as $comanda)
                <div class="modal fade" id="mutaComandaModal{{ $comanda->id }}" tabindex="-1" aria-labelledby="mutaComandaModalLabel{{ $comanda->id }}" aria-hidden="true" @if ($oldActionSource === 'move-single-comanda' && $oldMoveComandaId === (int) $comanda->id) data-show-on-load="true" @endif>
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header bg-primary text-white">
                                <h5 class="modal-title" id="mutaComandaModalLabel{{ $comanda->id }}">Muta comanda in alt calup</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form action="{{ route('facturi-transportatori.calupuri.muta-comanda', [$calup, $comanda]) }}" method="POST">
                                @csrf
                                <input type="hidden" name="action_source" value="move-single-comanda">
                                <input type="hidden" name="comenzi[]" value="{{ $comanda->id }}">
                                <div class="modal-body text-start">
                                    <p class="mb-3">Alege calupul in care vrei sa muti comanda <strong>{{ $comanda->transportator_contract }}</strong>.</p>
                                    <label for="plata_calup_id_{{ $comanda->id }}" class="form-label">Calup destinatie</label>
                                    <select name="plata_calup_id" id="plata_calup_id_{{ $comanda->id }}" class="form-select bg-white rounded-3 {{ $oldActionSource === 'move-single-comanda' && $oldMoveComandaId === (int) $comanda->id && $errors->has('plata_calup_id') ? 'is-invalid' : '' }}" required>
                                        <option value="">Selecteaza calupul</option>
                                        @foreach ($calupuriDestinatie as $calupDestinatie)
                                            <option value="{{ $calupDestinatie->id }}" @selected((string) old('plata_calup_id') === (string) $calupDestinatie->id)>
                                                {{ $calupDestinatie->denumire_calup }}@if($calupDestinatie->data_plata) - {{ $calupDestinatie->data_plata->format('d.m.Y') }}@endif
                                            </option>
                                        @endforeach
                                    </select>
                                    @if ($oldActionSource === 'move-single-comanda' && $oldMoveComandaId === (int) $comanda->id && $errors->has('plata_calup_id'))
                                        <div class="invalid-feedback d-block">{{ $errors->first('plata_calup_id') }}</div>
                                    @endif
                                    @if ($calupuriDestinatie->isEmpty())
                                        <small class="text-muted d-block mt-2">Nu exista alt calup disponibil catre care sa muti comanda.</small>
                                    @endif
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Renunta</button>
                                    <button type="submit" class="btn btn-primary" @disabled($calupuriDestinatie->isEmpty())>Muta comanda</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="detaseazaComandaModal{{ $comanda->id }}" tabindex="-1" aria-labelledby="detaseazaComandaModalLabel{{ $comanda->id }}" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header bg-danger text-white">
                                <h5 class="modal-title" id="detaseazaComandaModalLabel{{ $comanda->id }}">Elimina comanda din calup</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body text-start">
                                Sigur doresti sa elimini comanda <strong>{{ $comanda->transportator_contract }}</strong> din acest calup?
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Renunta</button>
                                <form action="{{ route('facturi-transportatori.calupuri.detaseaza-comanda', [$calup, $comanda]) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger">Elimina comanda</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

            <div class="border border-dark rounded-3 p-3 bg-white">
                <h6 class="text-uppercase text-muted mb-3">Adauga comenzi in calup</h6>
                @if ($facturiDisponibile->isEmpty())
                    <p class="text-muted mb-0">Nu exista comenzi disponibile.</p>
                @else
                    <form action="{{ route('facturi-transportatori.calupuri.atasare-comenzi', $calup) }}" method="POST" id="attach-form">
                        @csrf
                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <label for="attach-filter-transportator" class="form-label mb-1 small text-muted">Filtreaza dupa transportator</label>
                                <input type="text" id="attach-filter-transportator" class="form-control form-control-sm border border-dark" placeholder="Cauta transportator" autocomplete="off">
                            </div>
                            <div class="col-md-6">
                                <label for="attach-filter-comanda" class="form-label mb-1 small text-muted">Filtreaza dupa comanda</label>
                                <input type="text" id="attach-filter-comanda" class="form-control form-control-sm border border-dark" placeholder="Cauta numar comanda" autocomplete="off">
                            </div>
                        </div>
                        <div class="table-responsive" style="max-height: 350px; overflow-y: auto;">
                            <table class="table table-sm table-hover align-middle mb-0" id="attach-table">
                                <thead class="text-white rounded culoare2">
                                    <tr>
                                        <th class="text-center" style="width: 50px;"><input type="checkbox" id="attach-toggle-all"></th>
                                        <th>Transportator</th>
                                        <th>Comanda</th>
                                        <th>Factura</th>
                                        <th>Scadenta</th>
                                        <th class="text-end">Suma</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($facturiDisponibile as $comandaDisponibila)
                                        <tr class="attach-row" data-transportator="{{ \Illuminate\Support\Str::lower($comandaDisponibila->transportator->nume ?? '') }}" data-comanda="{{ \Illuminate\Support\Str::lower($comandaDisponibila->transportator_contract ?? '') }}">
                                            <td class="text-center">
                                                <input type="checkbox" name="comenzi[]" value="{{ $comandaDisponibila->id }}" class="attach-checkbox">
                                            </td>
                                            <td>{{ $comandaDisponibila->transportator->nume ?? '' }}</td>
                                            <td>{{ $comandaDisponibila->transportator_contract }}</td>
                                            <td>{{ $comandaDisponibila->factura_transportator }}</td>
                                            <td>{{ $comandaDisponibila->data_scadenta_plata_transportator ? Carbon::parse($comandaDisponibila->data_scadenta_plata_transportator)->format('d.m.Y') : '-' }}</td>
                                            <td class="text-end">
                                                @if (! is_null($comandaDisponibila->transportator_valoare_contract))
                                                    {{ rtrim(rtrim(number_format((float) $comandaDisponibila->transportator_valoare_contract, 2, '.', ''), '0'), '.') }}
                                                    {{ $comandaDisponibila->transportatorMoneda->nume ?? '' }}
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                    <tr id="attach-empty-state" class="d-none">
                                        <td colspan="6" class="text-center text-muted">Nu exista comenzi care corespund filtrelor.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-end gap-2 mt-3">
                            <span class="badge bg-primary text-white" id="attach-counter">Selectate: 0</span>
                            <button type="submit" class="btn btn-sm btn-success text-white border border-dark rounded-3">
                                <i class="fa-solid fa-plus me-1"></i>Adauga in calup
                            </button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const bootstrap = window.bootstrap;

        document.querySelectorAll('[data-show-on-load="true"]').forEach(modalElement => {
            if (bootstrap?.Modal) {
                const instance = typeof bootstrap.Modal.getOrCreateInstance === 'function'
                    ? bootstrap.Modal.getOrCreateInstance(modalElement)
                    : new bootstrap.Modal(modalElement);
                instance.show();
            }
        });

        const toggleAll = document.getElementById('attach-toggle-all');
        const counter = document.getElementById('attach-counter');
        const filterTransportator = document.getElementById('attach-filter-transportator');
        const filterComanda = document.getElementById('attach-filter-comanda');
        const rows = Array.from(document.querySelectorAll('.attach-row'));
        const emptyStateRow = document.getElementById('attach-empty-state');

        const updateCounter = () => {
            if (!counter) {
                return;
            }

            const total = rows.reduce((sum, row) => {
                const checkbox = row.querySelector('.attach-checkbox');
                return checkbox && checkbox.checked ? sum + 1 : sum;
            }, 0);

            counter.textContent = `Selectate: ${total}`;
        };

        const updateToggleAllState = () => {
            if (!toggleAll) {
                return;
            }

            const visibleCheckboxes = rows
                .filter(row => !row.classList.contains('d-none'))
                .map(row => row.querySelector('.attach-checkbox'))
                .filter(Boolean);

            if (visibleCheckboxes.length === 0) {
                toggleAll.checked = false;
                toggleAll.indeterminate = false;
                toggleAll.disabled = true;
                return;
            }

            toggleAll.disabled = false;

            const allChecked = visibleCheckboxes.every(checkbox => checkbox.checked);
            const someChecked = visibleCheckboxes.some(checkbox => checkbox.checked);

            toggleAll.checked = allChecked;
            toggleAll.indeterminate = !allChecked && someChecked;
        };

        const applyFilters = () => {
            const transportatorTerm = (filterTransportator?.value || '').trim().toLowerCase();
            const comandaTerm = (filterComanda?.value || '').trim().toLowerCase();
            let visibleCount = 0;

            rows.forEach(row => {
                const transportator = row.dataset.transportator || '';
                const comanda = row.dataset.comanda || '';
                const matchesTransportator = !transportatorTerm || transportator.includes(transportatorTerm);
                const matchesComanda = !comandaTerm || comanda.includes(comandaTerm);

                if (matchesTransportator && matchesComanda) {
                    row.classList.remove('d-none');
                    visibleCount += 1;
                } else {
                    row.classList.add('d-none');
                    const checkbox = row.querySelector('.attach-checkbox');

                    if (checkbox && checkbox.checked) {
                        checkbox.checked = false;
                    }
                }
            });

            if (emptyStateRow) {
                emptyStateRow.classList.toggle('d-none', visibleCount > 0);
            }

            updateCounter();
            updateToggleAllState();
        };

        if (toggleAll) {
            toggleAll.addEventListener('change', () => {
                rows.forEach(row => {
                    if (row.classList.contains('d-none')) {
                        return;
                    }

                    const checkbox = row.querySelector('.attach-checkbox');

                    if (checkbox) {
                        checkbox.checked = toggleAll.checked;
                    }
                });

                updateCounter();
                updateToggleAllState();
            });
        }

        rows.forEach(row => {
            const checkbox = row.querySelector('.attach-checkbox');

            if (checkbox) {
                checkbox.addEventListener('change', () => {
                    updateCounter();
                    updateToggleAllState();
                });
            }
        });

        if (filterTransportator) {
            filterTransportator.addEventListener('input', applyFilters);
        }

        if (filterComanda) {
            filterComanda.addEventListener('input', applyFilters);
        }

        applyFilters();
    });
</script>
@endsection
