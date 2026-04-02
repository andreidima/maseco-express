@extends('layouts.app')

@php
    use Carbon\Carbon;

    $selectedComenziOld = collect(old('comenzi', []))->map(fn ($id) => (int) $id)->all();
    $oldActionSource = old('action_source');
    $calupErrorFields = ['denumire_calup', 'data_plata', 'observatii', 'fisiere_pdf', 'fisiere_pdf.*', 'comenzi', 'comenzi.*'];
    $shouldShowCalupModal = !empty($selectedComenziOld) && $oldActionSource !== 'move-to-calup';
    $shouldShowMoveCalupModal = !empty($selectedComenziOld) && $oldActionSource === 'move-to-calup';

    foreach ($calupErrorFields as $field) {
        if ($errors->has($field)) {
            $shouldShowCalupModal = true;
            break;
        }
    }
@endphp

@section('content')
<div class="mx-3 px-3 card" style="border-radius: 40px 40px 40px 40px;">
    <div class="row card-header align-items-center" style="border-radius: 40px 40px 0px 0px;">
        <div class="col-lg-1 mb-2">
            <span class="badge culoare1 fs-5">
                <span class="d-inline-flex flex-column align-items-start gap-1 lh-1">
                    <span><i class="fa-solid fa-file-invoice me-1"></i>Facturi</span>
                    <span class="ms-4">transportatori</span>
                </span>
            </span>
        </div>
        <div class="col-lg-9 mb-0">
            <form class="needs-validation mb-lg-0" novalidate method="GET" action="{{ url()->current() }}">
                <div class="row gy-1 gx-3 mb-2 custom-search-form d-flex justify-content-center align-items-end">
                    <div class="col-lg-2 col-md-6">
                        <select name="status" id="filter-status" class="form-select bg-white rounded-3">
                            <option value="toate" @selected($filters['status'] === 'toate')>Toate</option>
                            <option value="neplatite" @selected($filters['status'] === 'neplatite')>Neplatite ({{ $neplatiteCount }})</option>
                            <option value="platite" @selected($filters['status'] === 'platite')>Arhivate (in calupuri)</option>
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <input type="text" class="form-control rounded-3" id="filter-transportator" name="transportator" placeholder="Transportator" value="{{ $filters['transportator'] }}">
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <input type="text" class="form-control rounded-3" id="filter-comanda" name="comanda" placeholder="Nr. comanda" value="{{ $filters['comanda'] }}">
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <label for="filter-scadenta-de-la" class="form-label ps-2 small text-muted mb-0">Scadenta de la</label>
                        <input type="date" class="form-control rounded-3" id="filter-scadenta-de-la" name="scadenta_de_la" value="{{ $filters['scadenta_de_la'] }}">
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <label for="filter-scadenta-pana" class="form-label ps-2 small text-muted mb-0">Scadenta pana la</label>
                        <input type="date" class="form-control rounded-3" id="filter-scadenta-pana" name="scadenta_pana" value="{{ $filters['scadenta_pana'] }}">
                    </div>
                    <div class="col-lg-1 col-md-6">
                        <label for="filter-are-pdf" class="form-label ps-2 small text-muted mb-0">PDF</label>
                        <select name="are_pdf" id="filter-are-pdf" class="form-select bg-white rounded-3">
                            <option value="" @selected($filters['are_pdf'] === null)>Toate</option>
                            <option value="da" @selected($filters['are_pdf'] === 'da')>Da</option>
                            <option value="nu" @selected($filters['are_pdf'] === 'nu')>Nu</option>
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <input type="text" class="form-control rounded-3" id="filter-calup" name="calup" placeholder="Calup" value="{{ $filters['calup'] }}">
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <label for="filter-calup-data" class="form-label ps-2 small text-muted mb-0">Data plata calup</label>
                        <input type="date" class="form-control rounded-3" id="filter-calup-data" name="calup_data_plata" value="{{ $filters['calup_data_plata'] }}">
                    </div>
                </div>
                <div class="row custom-search-form justify-content-center">
                    <button class="btn btn-sm btn-primary text-white col-md-4 me-3 border border-dark rounded-3" type="submit">
                        <i class="fas fa-search text-white me-1"></i>Cauta
                    </button>
                    <a class="btn btn-sm btn-secondary text-white col-md-4 border border-dark rounded-3" href="{{ route('facturi-transportatori.index') }}" role="button">
                        <i class="far fa-trash-alt text-white me-1"></i>Reseteaza
                    </a>
                </div>
            </form>
        </div>
        <div class="col-lg-2 text-lg-end mt-3 mt-lg-0">
            <div class="d-flex flex-column align-items-stretch align-items-lg-end gap-2">
                <div class="d-flex flex-wrap justify-content-lg-end gap-2">
                    <button type="button" class="btn btn-sm btn-warning text-dark border border-dark rounded-3" id="prepare-calup">
                        <i class="fa-solid fa-file-circle-plus me-1"></i>Pregateste calup
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-primary border border-dark rounded-3" id="move-to-existing-calup">
                        <i class="fa-solid fa-right-left me-1"></i>Muta in calup existent
                    </button>
                </div>
                <a class="btn btn-sm btn-info text-white border border-dark rounded-3" href="{{ route('facturi-transportatori.calupuri.index') }}" role="button">
                    <i class="fa-solid fa-layer-group text-white me-1"></i>Vezi toate calupurile
                </a>
            </div>
        </div>
    </div>

    <div class="card-body px-0 py-3">
        @include('errors')

        <div class="table-responsive rounded">
            <table class="table table-sm table-striped table-hover rounded align-middle">
                <thead class="text-white rounded culoare2">
                    <tr>
                        <th class="text-center"><input type="checkbox" id="select-all" title="Selecteaza toate"></th>
                        <th>Transportator</th>
                        <th>Comanda</th>
                        <th>Factura transportator</th>
                        <th>Data comanda</th>
                        <th>Ultima descarcare</th>
                        <th>Data factura</th>
                        <th>Scadenta factura</th>
                        <th class="text-end">Suma</th>
                        <th>Calup</th>
                        <th>PDF factura</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($comenzi as $comanda)
                        @php
                            $ultimaDescarcare = $comanda->locuriOperareDescarcari->sortByDesc('pivot.data_ora')->first();
                            $calupCurent = $comanda->calupuriFacturiTransportatori->first();
                        @endphp
                        <tr>
                            <td class="text-center">
                                <input
                                    type="checkbox"
                                    class="select-comanda"
                                    value="{{ $comanda->id }}"
                                    data-has-calup="{{ $calupCurent ? 'true' : 'false' }}"
                                    @checked(in_array((int) $comanda->id, $selectedComenziOld, true))
                                >
                            </td>
                            <td>{{ $comanda->transportator->nume ?? '' }}</td>
                            <td>
                                <div>{{ $comanda->transportator_contract }}</div>
                                <a href="{{ url('/facturi-memento/deschide/comanda/' . $comanda->id) }}" class="small">
                                    Deschide memento
                                </a>
                            </td>
                            <td>{{ $comanda->factura_transportator }}</td>
                            <td>{{ $comanda->data_creare ? Carbon::parse($comanda->data_creare)->isoFormat('DD.MM.YYYY') : '' }}</td>
                            <td>{{ $ultimaDescarcare && $ultimaDescarcare->pivot->data_ora ? Carbon::parse($ultimaDescarcare->pivot->data_ora)->isoFormat('DD.MM.YYYY') : '' }}</td>
                            <td>{{ $comanda->data_factura_transportator ? Carbon::parse($comanda->data_factura_transportator)->isoFormat('DD.MM.YYYY') : '' }}</td>
                            <td>{{ $comanda->data_scadenta_plata_transportator ? Carbon::parse($comanda->data_scadenta_plata_transportator)->isoFormat('DD.MM.YYYY') : '' }}</td>
                            <td class="text-end">
                                @if (! is_null($comanda->transportator_valoare_contract))
                                    {{ rtrim(rtrim(number_format((float) $comanda->transportator_valoare_contract, 2, '.', ''), '0'), '.') }}
                                    {{ $comanda->transportatorMoneda->nume ?? '' }}
                                @endif
                            </td>
                            <td>
                                @if ($calupCurent)
                                    <a href="{{ route('facturi-transportatori.calupuri.show', $calupCurent) }}" class="text-decoration-none">
                                        <span class="badge bg-success text-white">{{ $calupCurent->denumire_calup }}</span>
                                    </a>
                                    @if ($calupCurent->data_plata)
                                        <div class="small text-muted mt-1">{{ $calupCurent->data_plata->format('d.m.Y') }}</div>
                                    @endif
                                @else
                                    <span class="text-muted">Nealocata</span>
                                @endif
                            </td>
                            <td>
                                @forelse ($comanda->facturiIncarcateDeTransportator as $fisierFactura)
                                    <div class="mb-1">
                                        <a href="{{ route('comanda-incarcare-documente-de-catre-transportator.fisiere.deschide', ['cheie_unica' => $comanda->cheie_unica, 'fisierId' => $fisierFactura->id]) }}" target="_blank" rel="noopener">
                                            Vezi PDF {{ $loop->iteration }}
                                        </a>
                                        <span class="text-muted">|</span>
                                        <a href="{{ route('comanda-incarcare-documente-de-catre-transportator.fisiere.descarca', ['cheie_unica' => $comanda->cheie_unica, 'fisierId' => $fisierFactura->id]) }}">
                                            Descarca
                                        </a>
                                    </div>
                                @empty
                                    <span class="text-muted">Fara PDF</span>
                                @endforelse
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center text-muted py-4">
                                Nu exista facturi de transportatori care sa corespunda filtrelor selectate.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-center">
            {{ $comenzi->appends(Request::except('page'))->links() }}
        </div>
    </div>
</div>

<div class="modal fade" id="calupModal" tabindex="-1" aria-labelledby="calupModalLabel" aria-hidden="true" @if ($shouldShowCalupModal) data-show-on-load="true" @endif>
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="calupModalLabel">Pregateste calup pentru <span id="calup-selected-count" class="text-white fw-bold">0</span> comenzi</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('facturi-transportatori.calupuri.store') }}" method="POST" id="calup-form" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="action_source" value="create-calup">
                <div class="modal-body">
                    @include('facturi_transportatori.calupuri._form', ['calup' => null])
                    <div id="calup-form-selected" class="d-none"></div>
                    @if ($errors->has('comenzi'))
                        <div class="alert alert-danger mb-0">{{ $errors->first('comenzi') }}</div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Renunta</button>
                    <button type="submit" class="btn btn-success text-white border border-dark rounded-3">
                        <i class="fa-solid fa-floppy-disk me-1"></i>Salveaza calupul
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="moveToCalupModal" tabindex="-1" aria-labelledby="moveToCalupModalLabel" aria-hidden="true" @if ($shouldShowMoveCalupModal) data-show-on-load="true" @endif>
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="moveToCalupModalLabel">Muta <span id="move-calup-selected-count" class="text-white fw-bold">0</span> comenzi in calup existent</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('facturi-transportatori.move-to-calup') }}" method="POST" id="move-to-calup-form">
                @csrf
                <input type="hidden" name="action_source" value="move-to-calup">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="plata_calup_id" class="mb-0 ps-2">Calup existent<span class="text-danger">*</span></label>
                        <select name="plata_calup_id" id="plata_calup_id" class="form-select bg-white rounded-3 {{ $errors->has('plata_calup_id') ? 'is-invalid' : '' }}" required>
                            <option value="">Selecteaza calupul</option>
                            @foreach ($calupuriDisponibile as $calup)
                                <option value="{{ $calup->id }}" @selected((string) old('plata_calup_id') === (string) $calup->id)>
                                    {{ $calup->denumire_calup }}@if($calup->data_plata) - {{ $calup->data_plata->format('d.m.Y') }}@endif
                                </option>
                            @endforeach
                        </select>
                        @if ($errors->has('plata_calup_id'))
                            <div class="invalid-feedback d-block">{{ $errors->first('plata_calup_id') }}</div>
                        @endif
                        @if ($calupuriDisponibile->isEmpty())
                            <small class="text-muted d-block mt-2">Nu exista inca niciun calup creat.</small>
                        @else
                            <small class="text-muted d-block mt-2">Comenzile selectate vor fi mutate in calupul ales, chiar daca sunt deja intr-un alt calup.</small>
                        @endif
                    </div>
                    <div id="move-calup-form-selected" class="d-none"></div>
                    @if ($errors->has('comenzi'))
                        <div class="alert alert-danger mb-0">{{ $errors->first('comenzi') }}</div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Renunta</button>
                    <button type="submit" class="btn btn-primary text-white border border-dark rounded-3" @disabled($calupuriDisponibile->isEmpty())>
                        <i class="fa-solid fa-right-left me-1"></i>Muta comenzile
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="calupSelectionWarningModal" tabindex="-1" aria-labelledby="calupSelectionWarningModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title text-dark" id="calupSelectionWarningModalLabel">Selectati comenzi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">Selectati cel putin o comanda.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Inchide</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="calupNewSelectionWarningModal" tabindex="-1" aria-labelledby="calupNewSelectionWarningModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title text-dark" id="calupNewSelectionWarningModalLabel">Selectie invalida</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">Pentru un calup nou, selectati doar comenzi care nu sunt deja intr-un alt calup. Pentru cele deja arhivate, folositi "Muta in calup existent".</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Inchide</button>
            </div>
        </div>
    </div>
 </div>

@push('page-scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const selectAll = document.getElementById('select-all');
        const prepareButton = document.getElementById('prepare-calup');
        const moveToCalupButton = document.getElementById('move-to-existing-calup');
        const selectedContainer = document.getElementById('calup-form-selected');
        const selectedCount = document.getElementById('calup-selected-count');
        const calupModalElement = document.getElementById('calupModal');
        const moveToCalupModalElement = document.getElementById('moveToCalupModal');
        const moveSelectedContainer = document.getElementById('move-calup-form-selected');
        const moveSelectedCount = document.getElementById('move-calup-selected-count');
        const selectionWarningModalElement = document.getElementById('calupSelectionWarningModal');
        const newCalupSelectionWarningModalElement = document.getElementById('calupNewSelectionWarningModal');
        const bootstrap = window.bootstrap;
        const bootstrapModal = bootstrap && bootstrap.Modal ? bootstrap.Modal : null;

        const showModal = (element, fallbackMessage = null) => {
            if (!element) {
                if (fallbackMessage && typeof window.alert === 'function') {
                    window.alert(fallbackMessage);
                }
                return;
            }

            if (bootstrapModal) {
                const modalInstance = typeof bootstrapModal.getOrCreateInstance === 'function'
                    ? bootstrapModal.getOrCreateInstance(element)
                    : new bootstrapModal(element);
                modalInstance.show();
                return;
            }

            if (fallbackMessage && typeof window.alert === 'function') {
                window.alert(fallbackMessage);
            }
        };

        const checkboxItems = () => Array.from(document.querySelectorAll('.select-comanda'));
        const collectSelectedValues = () => checkboxItems().filter(checkbox => checkbox.checked).map(item => item.value);
        const selectedHasExistingCalup = () => checkboxItems().some(checkbox => checkbox.checked && checkbox.dataset.hasCalup === 'true');

        const syncSelectedInputs = (selected, container) => {
            if (!container) {
                return;
            }

            container.innerHTML = '';

            selected.forEach(value => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'comenzi[]';
                input.value = value;
                container.appendChild(input);
            });
        };

        const updateSelectAllState = (selectedCountValue) => {
            if (!selectAll) {
                return;
            }

            const checkboxes = checkboxItems();
            const total = checkboxes.length;
            const allSelected = total > 0 && selectedCountValue === total;

            selectAll.checked = allSelected;
            selectAll.indeterminate = selectedCountValue > 0 && selectedCountValue < total;
        };

        const refreshSelectionState = () => {
            const selected = collectSelectedValues();
            syncSelectedInputs(selected, selectedContainer);
            syncSelectedInputs(selected, moveSelectedContainer);

            if (selectedCount) {
                selectedCount.textContent = selected.length.toString();
            }

            if (moveSelectedCount) {
                moveSelectedCount.textContent = selected.length.toString();
            }

            updateSelectAllState(selected.length);

            return selected;
        };

        if (selectAll) {
            selectAll.addEventListener('change', () => {
                checkboxItems().forEach(checkbox => {
                    checkbox.checked = selectAll.checked;
                });

                refreshSelectionState();
            });
        }

        document.addEventListener('change', event => {
            if (event.target && event.target.classList && event.target.classList.contains('select-comanda')) {
                refreshSelectionState();
            }
        });

        if (prepareButton) {
            prepareButton.addEventListener('click', event => {
                const selected = refreshSelectionState();

                if (!selected.length) {
                    event.preventDefault();
                    showModal(selectionWarningModalElement, 'Selectati cel putin o comanda.');
                    return;
                }

                if (selectedHasExistingCalup()) {
                    event.preventDefault();
                    showModal(newCalupSelectionWarningModalElement);
                    return;
                }

                showModal(calupModalElement);
            });
        }

        if (moveToCalupButton) {
            moveToCalupButton.addEventListener('click', event => {
                const selected = refreshSelectionState();

                if (!selected.length) {
                    event.preventDefault();
                    showModal(selectionWarningModalElement, 'Selectati cel putin o comanda.');
                    return;
                }

                showModal(moveToCalupModalElement);
            });
        }

        if (calupModalElement && calupModalElement.dataset.showOnLoad === 'true') {
            refreshSelectionState();
            showModal(calupModalElement);
        } else if (moveToCalupModalElement && moveToCalupModalElement.dataset.showOnLoad === 'true') {
            refreshSelectionState();
            showModal(moveToCalupModalElement);
        } else {
            refreshSelectionState();
        }

        const preventDoubleSubmit = form => {
            if (!form) {
                return;
            }

            form.addEventListener('submit', event => {
                if (form.dataset.submitting === 'true') {
                    event.preventDefault();
                    return;
                }

                form.dataset.submitting = 'true';

                form.querySelectorAll('button[type="submit"]').forEach(button => {
                    button.disabled = true;
                });
            });
        };

        preventDoubleSubmit(document.getElementById('calup-form'));
        preventDoubleSubmit(document.getElementById('move-to-calup-form'));
    });
</script>
@endpush
@endsection
