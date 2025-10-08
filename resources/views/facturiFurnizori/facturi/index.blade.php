@extends('layouts.app')

@section('content')
@php
    $selectedFacturiOld = collect(old('facturi', []))->map(fn ($id) => (int) $id)->all();
    $calupErrorFields = ['denumire_calup', 'data_plata', 'observatii', 'fisier_pdf', 'facturi', 'facturi.*'];
    $shouldShowCalupModal = !empty($selectedFacturiOld);

    foreach ($calupErrorFields as $field) {
        if ($errors->has($field)) {
            $shouldShowCalupModal = true;
            break;
        }
    }

@endphp
<div class="mx-3 px-3 card" style="border-radius: 40px 40px 40px 40px;">
    <div class="row card-header align-items-center" style="border-radius: 40px 40px 0px 0px;">
        <div class="col-lg-2 mb-2">
            <span class="badge culoare1 fs-5">
                <i class="fa-solid fa-file-invoice-dollar me-1"></i>Facturi furnizori
            </span>
        </div>
        <div class="col-lg-8 mb-0" id="formularFacturi">
            <form class="needs-validation mb-lg-0" novalidate method="GET" action="{{ url()->current() }}">
                <div class="row mb-1 custom-search-form d-flex justify-content-center">
                    <div class="col-lg-4">
                        <input
                            type="text"
                            class="form-control rounded-3"
                            id="filter-furnizor"
                            name="furnizor"
                            placeholder="Furnizor"
                            value="{{ $filters['furnizor'] }}"
                            list="filter-furnizor-suggestions"
                            autocomplete="off"
                            data-typeahead-tip="furnizor"
                            data-typeahead-minlength="1"
                        >
                        <datalist id="filter-furnizor-suggestions"></datalist>
                        <small class="form-text text-muted ps-1">
                            <i class="fa-solid fa-wand-magic-sparkles me-1"></i>Autocomplete disponibil
                        </small>
                    </div>
                    <div class="col-lg-4">
                        <input
                            type="text"
                            class="form-control rounded-3"
                            id="filter-departament"
                            name="departament"
                            placeholder="Departament"
                            value="{{ $filters['departament'] }}"
                            list="filter-departament-suggestions"
                            autocomplete="off"
                            data-typeahead-tip="departament"
                            data-typeahead-minlength="1"
                        >
                        <datalist id="filter-departament-suggestions"></datalist>
                        <small class="form-text text-muted ps-1">
                            <i class="fa-solid fa-wand-magic-sparkles me-1"></i>Autocomplete disponibil
                        </small>
                    </div>
                    <div class="col-lg-4">
                        <select name="moneda" id="filter-moneda" class="form-select bg-white rounded-3">
                            <option value="">Monedă</option>
                            @foreach ($monede as $moneda)
                                <option value="{{ $moneda }}" @selected($filters['moneda'] === $moneda)>{{ $moneda }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row mb-1 custom-search-form d-flex justify-content-center">
                    <div class="col-lg-3">
                        <label for="filter-scadenta-de-la" class="form-label small text-muted mb-1">Scadență de la</label>
                        <input type="date" class="form-control rounded-3" id="filter-scadenta-de-la" name="scadenta_de_la" value="{{ $filters['scadenta_de_la'] }}">
                    </div>
                    <div class="col-lg-3">
                        <label for="filter-scadenta-pana" class="form-label small text-muted mb-1">Scadență până la</label>
                        <input type="date" class="form-control rounded-3" id="filter-scadenta-pana" name="scadenta_pana" value="{{ $filters['scadenta_pana'] }}">
                    </div>
                    <div class="col-lg-3">
                        <label for="filter-scadente-in-zile" class="form-label small text-muted mb-1">Scadente în (zile)</label>
                        <input type="number" min="0" class="form-control rounded-3" id="filter-scadente-in-zile" name="scadente_in_zile" placeholder="Scadente în (zile)" value="{{ $filters['scadente_in_zile'] }}">
                    </div>
                </div>
                <div class="row mb-1 custom-search-form d-flex justify-content-center">
                    <div class="col-lg-3">
                        <label for="filter-calup" class="form-label small text-muted mb-1">Calup</label>
                        <input type="text" class="form-control rounded-3" id="filter-calup" name="calup" placeholder="Calup" value="{{ $filters['calup'] }}">
                    </div>
                    <div class="col-lg-3">
                        <label for="filter-calup-data" class="form-label small text-muted mb-1">Data plată calup</label>
                        <input type="date" class="form-control rounded-3" id="filter-calup-data" name="calup_data_plata" value="{{ $filters['calup_data_plata'] }}">
                    </div>
                    <div class="col-lg-3">
                        <select name="status" id="filter-status" class="form-select bg-white rounded-3">
                            <option value="" @selected($filters['status'] === '')>Toate</option>
                            <option value="neplatite" @selected($filters['status'] === 'neplatite')>Neplătite ({{ $neplatiteCount }})</option>
                            <option value="platite" @selected($filters['status'] === 'platite')>Plătite</option>
                        </select>
                    </div>
                </div>
                <div class="row custom-search-form justify-content-center">
                    <button class="btn btn-sm btn-primary text-white col-md-4 me-3 border border-dark rounded-3" type="submit">
                        <i class="fas fa-search text-white me-1"></i>Caută
                    </button>
                    <a class="btn btn-sm btn-secondary text-white col-md-4 border border-dark rounded-3" href="{{ route('facturi-furnizori.facturi.index') }}" role="button">
                        <i class="far fa-trash-alt text-white me-1"></i>Resetează
                    </a>
                </div>
            </form>
        </div>
        <div class="col-lg-2 text-lg-end mt-3 mt-lg-0">
            <div class="d-flex flex-column align-items-stretch align-items-lg-end gap-2">
                <a class="btn btn-sm btn-success text-white border border-dark rounded-3" href="{{ route('facturi-furnizori.facturi.create') }}" role="button">
                    <i class="fas fa-plus-square text-white me-1"></i>Adaugă factură
                </a>
                <button type="button" class="btn btn-sm btn-warning text-dark border border-dark rounded-3" id="prepare-calup">
                    <i class="fa-solid fa-file-circle-plus me-1"></i>Pregătește calup
                </button>
            </div>
        </div>
    </div>

    <div class="card-body px-0 py-3">
        @include('errors')

        <div class="table-responsive rounded">
            <table class="table table-sm table-striped table-hover rounded align-middle">
                <thead class="text-white rounded culoare2">
                    <tr>
                        <th class="text-center"><input type="checkbox" id="select-all" title="Selectează toate"></th>
                        <th>Furnizor</th>
                        <th>Număr</th>
                        <th>Data</th>
                        <th>Scadență</th>
                        <th class="text-end">Sumă</th>
                        <th>Monedă</th>
                        <th>Departament</th>
                        <th>Calup</th>
                        <th>Observații</th>
                        <th class="text-end">Acțiuni</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($facturi as $factura)
                        <tr>
                            @php
                                $checkboxDisabled = $factura->calupuri->isNotEmpty();
                                $shouldCheck = in_array($factura->id, $selectedFacturiOld, true);
                            @endphp
                            <td class="text-center">
                                <input
                                    type="checkbox"
                                    class="select-factura"
                                    value="{{ $factura->id }}"
                                    @disabled($checkboxDisabled)
                                    @checked(!$checkboxDisabled && $shouldCheck)
                                >
                            </td>
                            <td>{{ $factura->denumire_furnizor }}</td>
                            <td>{{ $factura->numar_factura }}</td>
                            <td>{{ $factura->data_factura?->format('d.m.Y') }}</td>
                            <td>{{ $factura->data_scadenta?->format('d.m.Y') }}</td>
                            <td class="text-end">{{ number_format($factura->suma, 2) }}</td>
                            <td>{{ $factura->moneda }}</td>
                            <td>{{ $factura->departament_vehicul }}</td>
                            <td>
                                @if ($factura->calupuri->isNotEmpty())
                                    @foreach ($factura->calupuri as $calup)
                                        <a href="{{ route('facturi-furnizori.plati-calupuri.show', $calup) }}" class="badge bg-info text-dark text-decoration-none mb-1">{{ $calup->denumire_calup }}</a>
                                    @endforeach
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-muted">{{ \Illuminate\Support\Str::limit($factura->observatii, 60) }}</td>
                            <td class="text-end">
                                <div class="text-end">
                                    <a href="{{ route('facturi-furnizori.facturi.show', $factura) }}" class="flex me-1">
                                        <span class="badge bg-success">Vezi</span>
                                    </a>
                                    <a href="{{ route('facturi-furnizori.facturi.edit', $factura) }}" class="flex me-1">
                                        <span class="badge bg-primary">Editează</span>
                                    </a>
                                    <a href="#" class="flex"
                                        data-bs-toggle="modal"
                                        data-bs-target="#stergeFactura{{ $factura->id }}">
                                        <span class="badge bg-danger">Șterge</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center text-muted py-4">Nu există facturi înregistrate.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-3">
            {{ $facturi->appends(request()->except('page'))->links() }}
        </div>
    </div>
</div>

<div class="modal fade" id="calupModal" tabindex="-1" aria-labelledby="calupModalLabel" aria-hidden="true" @if ($shouldShowCalupModal) data-show-on-load="true" @endif>
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="calupModalLabel">Pregătește calup pentru <span id="calup-selected-count" class="text-white fw-bold">0</span> facturi</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('facturi-furnizori.plati-calupuri.store') }}" method="POST" id="calup-form" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-lg-6 mb-3">
                            <label for="denumire_calup" class="mb-0 ps-2">Denumire calup<span class="text-danger">*</span></label>
                            <input type="text" name="denumire_calup" id="denumire_calup" class="form-control bg-white rounded-3 {{ $errors->has('denumire_calup') ? 'is-invalid' : '' }}" value="{{ old('denumire_calup') }}" required>
                        </div>
                        <div class="col-lg-3 mb-3">
                            <label for="data_plata" class="mb-0 ps-2">Data plată</label>
                            <input type="date" name="data_plata" id="data_plata" class="form-control bg-white rounded-3 {{ $errors->has('data_plata') ? 'is-invalid' : '' }}" value="{{ old('data_plata') }}">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="observatii" class="mb-0 ps-2">Observații</label>
                        <textarea name="observatii" id="observatii" class="form-control bg-white rounded-3 {{ $errors->has('observatii') ? 'is-invalid' : '' }}" rows="3">{{ old('observatii') }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label for="fisier_pdf" class="mb-0 ps-2">Fișier PDF</label>
                        <input type="file" name="fisier_pdf" id="fisier_pdf" class="form-control bg-white rounded-3 {{ $errors->has('fisier_pdf') ? 'is-invalid' : '' }}" accept="application/pdf">
                    </div>
                    <div id="calup-form-selected" class="d-none"></div>
                    @if ($errors->has('facturi'))
                        <div class="alert alert-danger mb-0">{{ $errors->first('facturi') }}</div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Renunță</button>
                    <button type="submit" class="btn btn-success text-white border border-dark rounded-3">
                        <i class="fa-solid fa-floppy-disk me-1"></i>Salvează calupul
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
                <h5 class="modal-title text-dark" id="calupSelectionWarningModalLabel">Selectați facturi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">Selectați cel puțin o factură.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Închide</button>
            </div>
        </div>
    </div>
</div>

@foreach ($facturi as $factura)
    <div class="modal fade text-dark" id="stergeFactura{{ $factura->id }}" tabindex="-1" role="dialog" aria-labelledby="stergeFacturaLabel{{ $factura->id }}" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger">
                    <h5 class="modal-title text-white" id="stergeFacturaLabel{{ $factura->id }}">Factura {{ $factura->numar_factura }}</h5>
                    <button type="button" class="btn-close bg-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="text-align:left;">
                    Sigur ștergi factura <strong>{{ $factura->numar_factura }}</strong> de la <strong>{{ $factura->denumire_furnizor }}</strong>?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Renunță</button>
                    <form action="{{ route('facturi-furnizori.facturi.destroy', $factura) }}" method="POST" class="m-0">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Șterge factura</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endforeach

@push('page-scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const selectAll = document.getElementById('select-all');
            const checkboxItems = Array.from(document.querySelectorAll('.select-factura'));
            const prepareButton = document.getElementById('prepare-calup');
            const selectedContainer = document.getElementById('calup-form-selected');
            const selectedCount = document.getElementById('calup-selected-count');
            const calupModalElement = document.getElementById('calupModal');
            const selectionWarningModalElement = document.getElementById('calupSelectionWarningModal');
            const bootstrap = window.bootstrap;
            const bootstrapModal = bootstrap && bootstrap.Modal ? bootstrap.Modal : null;

            const showModal = (element, fallbackMessage = null) => {
                if (!element) {
                    if (fallbackMessage && typeof window !== 'undefined' && typeof window.alert === 'function') {
                        window.alert(fallbackMessage);
                    }
                    return;
                }

                if (bootstrapModal) {
                    const modalInstance =
                        typeof bootstrapModal.getOrCreateInstance === 'function'
                            ? bootstrapModal.getOrCreateInstance(element)
                            : new bootstrapModal(element);
                    modalInstance.show();
                    return;
                }

                const $ = window.jQuery || window.$;

                if (typeof $ === 'function' && typeof $(element).modal === 'function') {
                    $(element).modal('show');
                    return;
                }

                if (fallbackMessage && typeof window !== 'undefined' && typeof window.alert === 'function') {
                    window.alert(fallbackMessage);
                }
            };

            const selectableCheckboxes = () => checkboxItems.filter(checkbox => !checkbox.disabled);

            const collectSelectedValues = () => selectableCheckboxes()
                .filter(checkbox => checkbox.checked)
                .map(item => item.value);

            const syncSelectedInputs = (selected) => {
                if (!selectedContainer) {
                    return;
                }

                selectedContainer.innerHTML = '';

                selected.forEach(value => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'facturi[]';
                    input.value = value;
                    selectedContainer.appendChild(input);
                });
            };

            const updateSelectAllState = (selectedCountValue) => {
                if (!selectAll) {
                    return;
                }

                const eligibleCheckboxes = selectableCheckboxes();
                const totalEligible = eligibleCheckboxes.length;
                const allSelected = totalEligible > 0 && selectedCountValue === totalEligible;

                selectAll.checked = allSelected;
                selectAll.indeterminate = selectedCountValue > 0 && selectedCountValue < totalEligible;
            };

            const updateSelectedCounter = (selected) => {
                const count = selected.length;

                if (selectedCount) {
                    selectedCount.textContent = count.toString();
                }

                updateSelectAllState(count);
            };

            const refreshSelectionState = () => {
                const selected = collectSelectedValues();
                syncSelectedInputs(selected);
                updateSelectedCounter(selected);
                return selected;
            };

            if (selectAll) {
                selectAll.addEventListener('change', () => {
                    selectableCheckboxes().forEach(checkbox => {
                        checkbox.checked = selectAll.checked;
                    });

                    refreshSelectionState();
                });
            }

            checkboxItems.forEach(checkbox => {
                checkbox.addEventListener('change', () => {
                    refreshSelectionState();
                });
            });

            if (prepareButton) {
                prepareButton.addEventListener('click', (event) => {
                    const selected = refreshSelectionState();

                    if (!selected.length) {
                        event.preventDefault();
                        showModal(selectionWarningModalElement, 'Selectați cel puțin o factură.');
                        return;
                    }

                    showModal(calupModalElement);
                });
            }

            const initializeModalIfNeeded = () => {
                if (calupModalElement && calupModalElement.dataset.showOnLoad === 'true') {
                    refreshSelectionState();
                    showModal(calupModalElement);
                    return;
                }

                refreshSelectionState();
            };

            initializeModalIfNeeded();
        });
    </script>
    @include('facturiFurnizori.facturi._typeahead')
@endpush
@endsection
