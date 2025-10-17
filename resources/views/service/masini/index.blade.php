@extends('layouts.app')

@php
    $filters = $filters ?? [];
    $viewMode = $viewMode ?? 'interventii';
    $isServiceSheetView = $viewMode === 'service-sheets';
    $selectedMasinaId = optional($selectedMasina)->id;
    $queryParams = collect($filters)
        ->merge(['view' => $viewMode])
        ->reject(fn ($value) => $value === null || $value === '')
        ->toArray();
    $editingEntry = $editingEntry ?? null;
    $isEditingEntry = $editingEntry !== null;
    $formContext = old('form_context');
    $entryOldMatchesEditing = $isEditingEntry && $formContext === 'entry_update' && (int) old('entry_id') === optional($editingEntry)->id;
    $createEntryOld = $formContext === 'entry_store';
    $shouldShowCreateEntryModal = $createEntryOld && $errors->any();
    $shouldShowEditEntryModal = $isEditingEntry && (!$errors->any() || $entryOldMatchesEditing);
    $createMasinaOld = $formContext === 'create_masina';
    $createMasinaErrorFields = ['denumire', 'numar_inmatriculare', 'serie_sasiu', 'observatii'];
    $shouldShowCreateModal = $createMasinaOld && collect($createMasinaErrorFields)->contains(fn ($field) => $errors->has($field));
    $updateMasinaOld = $formContext === 'update_masina' && $selectedMasinaId && (int) old('masina_id') === $selectedMasinaId;
    $updateMasinaErrorFields = $createMasinaErrorFields;
    $shouldShowEditModal = $updateMasinaOld && collect($updateMasinaErrorFields)->contains(fn ($field) => $errors->has($field));
@endphp

@section('content')
    <div class="mx-3 px-3 card mx-auto" style="border-radius: 40px 40px 40px 40px;">
        <div class="row card-header align-items-center" style="border-radius: 40px 40px 0px 0px;">
            <div class="col-lg-3 mb-2 mb-lg-0">
                <span class="badge culoare1 fs-5">
                    <i class="fa-solid fa-screwdriver-wrench me-1"></i>Service mașini
                </span>
            </div>
            <div class="col-lg-6 mb-2">
                <form class="needs-validation" novalidate method="GET" action="{{ route('service-masini.index') }}">
                    <div class="row gy-2 gx-3 align-items-end">
                        <input type="hidden" name="view" value="{{ $viewMode }}">
                        <div class="col-lg-4 col-md-6">
                            <label for="numar_inmatriculare" class="form-label small text-muted mb-1">
                                <i class="fa-solid fa-car me-1"></i>Nr. înmatriculare / Denumire mașină
                            </label>
                            <input type="text" class="form-control rounded-3" id="numar_inmatriculare"
                                name="numar_inmatriculare" placeholder="Ex: B00ABC"
                                value="{{ $filters['numar_inmatriculare'] ?? '' }}" autocomplete="off">
                        </div>
                        @unless ($isServiceSheetView)
                            <div class="col-lg-4 col-md-6">
                                <label for="piesa" class="form-label small text-muted mb-1">
                                    <i class="fa-solid fa-puzzle-piece me-1"></i>Denumire piesă / intervenție
                                </label>
                                <input type="text" class="form-control rounded-3" id="piesa" name="piesa"
                                    placeholder="Ex: Filtru ulei"
                                    value="{{ $filters['piesa'] ?? '' }}" autocomplete="off">
                            </div>
                            <div class="col-lg-4 col-md-6">
                                <label for="cod" class="form-label small text-muted mb-1">
                                    <i class="fa-solid fa-barcode me-1"></i>Cod piesă
                                </label>
                                <input type="text" class="form-control rounded-3" id="cod" name="cod"
                                    placeholder="Cod piesă" value="{{ $filters['cod'] ?? '' }}" autocomplete="off">
                            </div>
                        @endunless
                        <div class="col-lg-4 col-md-6">
                            <label for="data_start" class="form-label small text-muted mb-1">
                                <i class="fa-solid fa-calendar-day me-1"></i>De la data
                            </label>
                            <input type="date" class="form-control rounded-3" id="data_start" name="data_start"
                                value="{{ $filters['data_start'] ?? '' }}">
                        </div>
                        <div class="col-lg-4 col-md-6">
                            <label for="data_end" class="form-label small text-muted mb-1">
                                <i class="fa-solid fa-calendar-check me-1"></i>Până la data
                            </label>
                            <input type="date" class="form-control rounded-3" id="data_end" name="data_end"
                                value="{{ $filters['data_end'] ?? '' }}">
                        </div>
                        <div class="col-lg-4 col-md-6 d-flex gap-2">
                            <button class="btn btn-sm btn-primary text-white flex-grow-1 border border-dark rounded-3"
                                type="submit">
                                <i class="fas fa-search text-white me-1"></i>Caută
                            </button>
                            <a class="btn btn-sm btn-secondary text-white flex-grow-1 border border-dark rounded-3"
                                href="{{ route('service-masini.index', ['view' => $viewMode]) }}">
                                <i class="far fa-trash-alt text-white me-1"></i>Resetează
                            </a>
                        </div>
                    </div>
                </form>
            </div>
            <div class="col-lg-3 mt-2 mt-lg-0">
                @include('partials.operations-navigation')
            </div>
        </div>

        <div class="card-body">
            @include('errors')

            @if ($errors->has('general'))
                <div class="alert alert-danger" role="alert">
                    {{ $errors->first('general') }}
                </div>
            @endif

            <div class="row g-4">
                <div class="col-lg-2">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span class="fw-semibold"><i class="fa-solid fa-car-side me-1"></i>Mașini</span>
                        </div>
                        <div class="card-body">
                            <div class="list-group mb-4" style="max-height: 450px; overflow-y: auto;">
                                @forelse ($masini as $masina)
                                    @php
                                        $masinaQuery = $queryParams;
                                        $masinaQuery['masina_id'] = $masina->id;
                                    @endphp
                                    <a href="{{ route('service-masini.index', $masinaQuery) }}"
                                        class="list-group-item list-group-item-action rounded-3 mb-2 {{ $selectedMasinaId === $masina->id ? 'active text-white' : '' }}"
                                        style="padding-top: 0.35rem; padding-bottom: 0.35rem;">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <div class="fw-semibold">{{ $masina->numar_inmatriculare }}</div>
                                                <small class="text-muted {{ $selectedMasinaId === $masina->id ? 'text-white-50' : '' }}">
                                                    {{ $masina->denumire }}
                                                </small>
                                            </div>
                                            <i class="fa-solid fa-chevron-right small"></i>
                                        </div>
                                    </a>
                                @empty
                                    <div class="text-center text-muted py-4">
                                        Nu există mașini adăugate momentan.
                                    </div>
                                @endforelse
                            </div>

                            <div class="d-grid gap-2">
                                <button type="button" class="btn btn-sm btn-success rounded-3" data-bs-toggle="modal"
                                    data-bs-target="#createMasinaModal">
                                    <i class="fa-solid fa-plus-circle me-1"></i>Adaugă mașină
                                </button>
                            </div>

                            @if ($selectedMasina)
                                <div class="d-grid gap-2 mt-3">
                                    <button type="button" class="btn btn-sm btn-primary rounded-3" data-bs-toggle="modal"
                                        data-bs-target="#editMasinaModal">
                                        <i class="fa-solid fa-pen-to-square me-1"></i>Editează mașina selectată
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-lg-10">
                    @if ($selectedMasina)
                        <div class="card shadow-sm border-0 mb-4">
                            <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                                <div>
                                    <h5 class="mb-0">{{ $selectedMasina->denumire }}</h5>
                                    <small class="text-muted">Nr. înmatriculare: {{ $selectedMasina->numar_inmatriculare }}</small>
                                </div>
                                <div class="d-flex flex-wrap gap-2 align-items-center">
                                    @php
                                        $tabQuery = array_merge($queryParams, ['masina_id' => $selectedMasina->id]);
                                    @endphp
                                    <div class="btn-group btn-group-sm" role="group" aria-label="Comutare listă intervenții sau foi">
                                        <a class="btn {{ $isServiceSheetView ? 'btn-outline-primary' : 'btn-primary' }}"
                                            href="{{ route('service-masini.index', array_merge($tabQuery, ['view' => 'interventii'])) }}">
                                            <i class="fa-solid fa-list me-1"></i>Intervenții
                                        </a>
                                        <a class="btn {{ $isServiceSheetView ? 'btn-primary' : 'btn-outline-primary' }}"
                                            href="{{ route('service-masini.index', array_merge($tabQuery, ['view' => 'service-sheets'])) }}">
                                            <i class="fa-solid fa-file-lines me-1"></i>Foi service
                                        </a>
                                    </div>

                                    @if ($isServiceSheetView)
                                        <a class="btn btn-success btn-sm rounded-3"
                                            href="{{ route('service-masini.sheet.create', $selectedMasina) }}">
                                            <i class="fa-solid fa-plus-circle me-1"></i>Adaugă foaie service
                                        </a>
                                    @else
                                        <button type="button" class="btn btn-success btn-sm rounded-3"
                                            data-bs-toggle="modal" data-bs-target="#createEntryModal">
                                            <i class="fa-solid fa-plus-circle me-1"></i>Adaugă intervenție
                                        </button>
                                        <a class="btn btn-outline-secondary btn-sm rounded-3"
                                            href="{{ route('service-masini.sheet.create', $selectedMasina) }}">
                                            <i class="fa-solid fa-file-lines me-1"></i>Foaie service
                                        </a>
                                        <a class="btn btn-outline-primary btn-sm rounded-3"
                                            href="{{ route('service-masini.export', $queryParams + ['masina_id' => $selectedMasina->id]) }}">
                                            <i class="fa-solid fa-file-pdf me-1"></i>Descarcă PDF
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="card shadow-sm border-0">
                            <div class="table-responsive">
                                @if ($isServiceSheetView)
                                    <table class="table table-striped table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="min-width: 140px;">Data service</th>
                                                <th style="min-width: 120px;">Km bord</th>
                                                <th style="min-width: 150px;">Număr poziții</th>
                                                <th style="min-width: 160px;">Creată la</th>
                                                <th class="text-end" style="min-width: 200px;">Acțiuni</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($serviceSheets as $sheet)
                                                <tr>
                                                    <td>{{ optional($sheet->data_service)->format('d.m.Y') ?? '—' }}</td>
                                                    <td>{{ number_format((int) $sheet->km_bord) }}</td>
                                                    <td>{{ $sheet->items_count }}</td>
                                                    <td>{{ optional($sheet->created_at)->format('d.m.Y H:i') ?? '—' }}</td>
                                                    <td class="text-end">
                                                        <div class="d-flex justify-content-end gap-2">
                                                            <a class="btn btn-outline-primary btn-sm"
                                                                href="{{ route('service-masini.sheet.download', [$selectedMasina, $sheet]) }}">
                                                                <i class="fa-solid fa-file-arrow-down me-1"></i>PDF
                                                            </a>
                                                            <a class="btn btn-outline-secondary btn-sm"
                                                                href="{{ route('service-masini.sheet.edit', [$selectedMasina, $sheet]) }}">
                                                                Editează
                                                            </a>
                                                            <form method="POST" class="d-inline"
                                                                action="{{ route('service-masini.sheet.destroy', [$selectedMasina, $sheet]) }}"
                                                                onsubmit="return confirm('Sigur dorești să ștergi această foaie de service?');">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-outline-danger btn-sm">Șterge</button>
                                                            </form>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="text-center text-muted py-4">
                                                        Nu există foi de service pentru această mașină.
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                @else
                                    <table class="table table-striped table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="min-width: 110px;">Data</th>
                                                <th style="min-width: 120px;">Tip</th>
                                                <th style="min-width: 180px;">Denumire</th>
                                                <th style="min-width: 120px;">Cod</th>
                                                <th style="min-width: 90px;">Cantitate</th>
                                                <th style="min-width: 150px;">Mecanic</th>
                                                <th style="min-width: 150px;">Utilizator</th>
                                                <th>Obs</th>
                                                <th class="text-end" style="min-width: 140px;">Acțiuni</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($entries as $entry)
                                                <tr>
                                                    <td>{{ optional($entry->data_montaj)->format('d.m.Y') ?? '—' }}</td>
                                                    <td>
                                                        @if ($entry->tip === 'piesa')
                                                            <span class="badge bg-primary">Piesă</span>
                                                        @else
                                                            <span class="badge bg-secondary">Manual</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if ($entry->tip === 'piesa')
                                                            {{ $entry->denumire_piesa ?? '—' }}
                                                        @else
                                                            {{ $entry->denumire_interventie ?? '—' }}
                                                        @endif
                                                    </td>
                                                    <td>{{ $entry->tip === 'piesa' ? ($entry->cod_piesa ?? '—') : '—' }}</td>
                                                    <td>
                                                        @if ($entry->tip === 'piesa')
                                                            {{ $entry->cantitate !== null ? number_format((float) $entry->cantitate, 2) : '—' }}
                                                        @else
                                                            —
                                                        @endif
                                                    </td>
                                                    <td>{{ $entry->nume_mecanic ?? '—' }}</td>
                                                    <td>{{ $entry->nume_utilizator ?? optional($entry->user)->name ?? '—' }}</td>
                                                    <td class="text-center">
                                                        @if ($entry->observatii)
                                                            <button type="button" class="btn btn-link p-0 text-decoration-none"
                                                                data-bs-toggle="tooltip" data-bs-trigger="hover focus"
                                                                title="{{ $entry->observatii }}" aria-label="Vizualizează observațiile">
                                                                <i class="fa-solid fa-circle-info"></i>
                                                            </button>
                                                        @else
                                                            —
                                                        @endif
                                                    </td>
                                                    <td class="text-end">
                                                        <div class="d-flex justify-content-end gap-2">
                                                            <a class="btn btn-outline-primary btn-sm"
                                                                href="{{ route('service-masini.index', $queryParams + ['masina_id' => $selectedMasina->id, 'entry_id' => $entry->id]) }}">
                                                                Editează
                                                            </a>
                                                            <form method="POST" class="d-inline"
                                                                action="{{ route('service-masini.entries.destroy', [$selectedMasina, $entry]) }}"
                                                                onsubmit="return confirm('Sigur dorești să ștergi această intervenție?');">
                                                                @csrf
                                                                @method('DELETE')
                                                                @foreach ($filters as $key => $value)
                                                                    @if ($value !== null && $value !== '')
                                                                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                                                    @endif
                                                                @endforeach
                                                                <button type="submit" class="btn btn-outline-danger btn-sm">Șterge</button>
                                                            </form>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="9" class="text-center text-muted py-4">
                                                        Nu există intervenții pentru această mașină.
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                @endif
                            </div>
                            <div class="card-footer">
                                {{ $isServiceSheetView ? $serviceSheets->links() : $entries->links() }}
                            </div>
                        </div>
                    @else
                        <div class="alert alert-info" role="alert">
                            Adaugă o mașină pentru a începe să înregistrezi intervenții.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @if ($selectedMasina)
        @if (! $isServiceSheetView)
            @php
            $createEntryTip = $createEntryOld ? old('tip', 'piesa') : 'piesa';
            $createEntrySelectedPiece = $createEntryOld ? (int) old('gestiune_piesa_id') : null;
            $createEntryCantitate = $createEntryOld ? old('cantitate', '1') : '1';
            $createEntryDate = $createEntryOld ? old('data_montaj', now()->toDateString()) : now()->toDateString();
            $createEntryMechanic = $createEntryOld ? old('nume_mecanic') : '';
            $createEntryObservatii = $createEntryOld ? old('observatii') : '';
            $createEntryDenumire = $createEntryOld ? old('denumire_interventie') : '';
            @endphp
        <div class="modal fade" id="createEntryModal" tabindex="-1" aria-labelledby="createEntryModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <form id="create-entry-form" method="POST"
                    action="{{ route('service-masini.entries.store', $selectedMasina) }}" class="modal-content">
                    @csrf
                    @foreach ($filters as $key => $value)
                        @if ($value !== null && $value !== '')
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endif
                    @endforeach
                    <input type="hidden" name="form_context" value="entry_store">
                    <div class="modal-header">
                        <h5 class="modal-title" id="createEntryModalLabel">
                            <i class="fa-solid fa-plus-circle me-1"></i>Adaugă intervenție
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Închide"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small text-muted mb-1">Tip intervenție <span class="text-danger">*</span></label>
                            <div class="d-flex gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="tip" id="tip_piesa_create"
                                        value="piesa" {{ $createEntryTip === 'piesa' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="tip_piesa_create">
                                        Alocare piesă din gestiune
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="tip" id="tip_manual_create"
                                        value="manual" {{ $createEntryTip === 'manual' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="tip_manual_create">
                                        Intervenție manuală
                                    </label>
                                </div>
                            </div>
                            @if ($createEntryOld && $errors->has('tip'))
                                <div class="text-danger small mt-1">{{ $errors->first('tip') }}</div>
                            @endif
                        </div>

                        <div class="row g-3">
                            <div class="col-lg-6" data-entry="piesa">
                                <label for="gestiune_piesa_id_create" class="form-label small text-muted mb-1">Piesă
                                    <span class="text-danger">*</span>
                                    <small class="text-muted">(pentru alocări din gestiune)</small>
                                </label>
                                <select name="gestiune_piesa_id" id="gestiune_piesa_id_create"
                                    class="form-select rounded-3">
                                    <option value="">Selectează piesa</option>
                                    @foreach ($availablePieces as $piesa)
                                        <option value="{{ $piesa->id }}" @selected($createEntrySelectedPiece === $piesa->id)>
                                            {{ $piesa->denumire }} ({{ $piesa->cod }}) -
                                            {{ number_format((float) $piesa->nr_bucati, 2) }} buc
                                        </option>
                                    @endforeach
                                </select>
                                @if ($createEntryOld && $errors->has('gestiune_piesa_id'))
                                    <div class="text-danger small mt-1">{{ $errors->first('gestiune_piesa_id') }}</div>
                                @endif
                            </div>

                            <div class="col-lg-6" data-entry="piesa">
                                <label for="cantitate_create" class="form-label small text-muted mb-1">Cantitate
                                    <span class="text-danger">*</span>
                                    <small class="text-muted">(pentru alocări din gestiune)</small>
                                </label>
                                <input type="number" step="0.01" min="0" class="form-control rounded-3"
                                    id="cantitate_create" name="cantitate" value="{{ $createEntryCantitate }}">
                                @if ($createEntryOld && $errors->has('cantitate'))
                                    <div class="text-danger small mt-1">{{ $errors->first('cantitate') }}</div>
                                @endif
                            </div>

                            <div class="col-lg-6" data-entry="manual">
                                <label for="denumire_interventie_create" class="form-label small text-muted mb-1">Denumire
                                    intervenție <span class="text-danger">*</span>
                                    <small class="text-muted">(pentru intervenții manuale)</small>
                                </label>
                                <input type="text" class="form-control rounded-3" id="denumire_interventie_create"
                                    name="denumire_interventie" value="{{ $createEntryDenumire }}">
                                @if ($createEntryOld && $errors->has('denumire_interventie'))
                                    <div class="text-danger small mt-1">{{ $errors->first('denumire_interventie') }}</div>
                                @endif
                            </div>

                            <div class="col-lg-6">
                                <label for="data_montaj_create" class="form-label small text-muted mb-1">Data intervenției
                                    <span class="text-danger">*</span></label>
                                <input type="date" class="form-control rounded-3" id="data_montaj_create" name="data_montaj"
                                    value="{{ $createEntryDate }}">
                                @if ($createEntryOld && $errors->has('data_montaj'))
                                    <div class="text-danger small mt-1">{{ $errors->first('data_montaj') }}</div>
                                @endif
                            </div>

                            <div class="col-lg-6">
                                <label for="nume_mecanic_create" class="form-label small text-muted mb-1">Nume mecanic
                                    <span class="text-danger">*</span></label>
                                <input type="text" class="form-control rounded-3" id="nume_mecanic_create"
                                    name="nume_mecanic" value="{{ $createEntryMechanic }}">
                                @if ($createEntryOld && $errors->has('nume_mecanic'))
                                    <div class="text-danger small mt-1">{{ $errors->first('nume_mecanic') }}</div>
                                @endif
                            </div>

                            <div class="col-12">
                                <label for="observatii_create_entry" class="form-label small text-muted mb-1">Observații</label>
                                <textarea name="observatii" id="observatii_create_entry" rows="3" class="form-control rounded-3">{{ $createEntryObservatii }}</textarea>
                                @if ($createEntryOld && $errors->has('observatii'))
                                    <div class="text-danger small mt-1">{{ $errors->first('observatii') }}</div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary rounded-3" data-bs-dismiss="modal">Închide</button>
                        <button type="submit" class="btn btn-primary rounded-3">
                            <i class="fa-solid fa-paper-plane me-1"></i>Salvează intervenția
                        </button>
                    </div>
                </form>
            </div>
        </div>

        @if ($isEditingEntry && ! $isServiceSheetView)
            @php
                $editEntryTip = $entryOldMatchesEditing ? old('tip', $editingEntry->tip ?? 'piesa') : ($editingEntry->tip ?? 'piesa');
                $editEntrySelectedPiece = $entryOldMatchesEditing
                    ? (int) old('gestiune_piesa_id', optional($editingEntry)->gestiune_piesa_id)
                    : optional($editingEntry)->gestiune_piesa_id;
                $editEntryCantitateDefault = $editingEntry && $editingEntry->cantitate !== null
                    ? number_format((float) $editingEntry->cantitate, 2, '.', '')
                    : '1';
                $editEntryCantitate = $entryOldMatchesEditing ? old('cantitate', $editEntryCantitateDefault) : $editEntryCantitateDefault;
                $editEntryDenumire = $entryOldMatchesEditing
                    ? old('denumire_interventie', $editingEntry->denumire_interventie)
                    : $editingEntry->denumire_interventie;
                $editEntryDateDefault = optional($editingEntry->data_montaj)->toDateString() ?? now()->toDateString();
                $editEntryDate = $entryOldMatchesEditing ? old('data_montaj', $editEntryDateDefault) : $editEntryDateDefault;
                $editEntryMechanic = $entryOldMatchesEditing ? old('nume_mecanic', $editingEntry->nume_mecanic) : $editingEntry->nume_mecanic;
                $editEntryObservatii = $entryOldMatchesEditing ? old('observatii', $editingEntry->observatii) : $editingEntry->observatii;
            @endphp
            <div class="modal fade" id="editEntryModal" tabindex="-1" aria-labelledby="editEntryModalLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-scrollable">
                    <form id="edit-entry-form" method="POST"
                        action="{{ route('service-masini.entries.update', [$selectedMasina, $editingEntry]) }}"
                        class="modal-content">
                        @csrf
                        @method('PUT')
                        @foreach ($filters as $key => $value)
                            @if ($value !== null && $value !== '')
                                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                            @endif
                        @endforeach
                        <input type="hidden" name="form_context" value="entry_update">
                        <input type="hidden" name="entry_id" value="{{ $editingEntry->id }}">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editEntryModalLabel">
                                <i class="fa-solid fa-pen-to-square me-1"></i>Editează intervenția selectată
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Închide"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label small text-muted mb-1">Tip intervenție <span class="text-danger">*</span></label>
                                <div class="d-flex gap-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="tip" id="tip_piesa_edit"
                                            value="piesa" {{ $editEntryTip === 'piesa' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="tip_piesa_edit">
                                            Alocare piesă din gestiune
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="tip" id="tip_manual_edit"
                                            value="manual" {{ $editEntryTip === 'manual' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="tip_manual_edit">
                                            Intervenție manuală
                                        </label>
                                    </div>
                                </div>
                                @if ($entryOldMatchesEditing && $errors->has('tip'))
                                    <div class="text-danger small mt-1">{{ $errors->first('tip') }}</div>
                                @endif
                            </div>

                            <div class="row g-3">
                                <div class="col-lg-6" data-entry="piesa">
                                    <label for="gestiune_piesa_id_edit" class="form-label small text-muted mb-1">Piesă
                                        <span class="text-danger">*</span>
                                        <small class="text-muted">(pentru alocări din gestiune)</small>
                                    </label>
                                    <select name="gestiune_piesa_id" id="gestiune_piesa_id_edit"
                                        class="form-select rounded-3">
                                        <option value="">Selectează piesa</option>
                                        @foreach ($availablePieces as $piesa)
                                            <option value="{{ $piesa->id }}" @selected($editEntrySelectedPiece === $piesa->id)>
                                                {{ $piesa->denumire }} ({{ $piesa->cod }}) -
                                                {{ number_format((float) $piesa->nr_bucati, 2) }} buc
                                            </option>
                                        @endforeach
                                    </select>
                                    @if ($entryOldMatchesEditing && $errors->has('gestiune_piesa_id'))
                                        <div class="text-danger small mt-1">{{ $errors->first('gestiune_piesa_id') }}</div>
                                    @endif
                                </div>

                                <div class="col-lg-6" data-entry="piesa">
                                    <label for="cantitate_edit" class="form-label small text-muted mb-1">Cantitate
                                        <span class="text-danger">*</span>
                                        <small class="text-muted">(pentru alocări din gestiune)</small>
                                    </label>
                                    <input type="number" step="0.01" min="0" class="form-control rounded-3"
                                        id="cantitate_edit" name="cantitate" value="{{ $editEntryCantitate }}">
                                    @if ($entryOldMatchesEditing && $errors->has('cantitate'))
                                        <div class="text-danger small mt-1">{{ $errors->first('cantitate') }}</div>
                                    @endif
                                </div>

                                <div class="col-lg-6" data-entry="manual">
                                    <label for="denumire_interventie_edit" class="form-label small text-muted mb-1">Denumire
                                        intervenție <span class="text-danger">*</span>
                                        <small class="text-muted">(pentru intervenții manuale)</small>
                                    </label>
                                    <input type="text" class="form-control rounded-3" id="denumire_interventie_edit"
                                        name="denumire_interventie" value="{{ $editEntryDenumire }}">
                                    @if ($entryOldMatchesEditing && $errors->has('denumire_interventie'))
                                        <div class="text-danger small mt-1">{{ $errors->first('denumire_interventie') }}</div>
                                    @endif
                                </div>

                                <div class="col-lg-6">
                                    <label for="data_montaj_edit" class="form-label small text-muted mb-1">Data intervenției
                                        <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control rounded-3" id="data_montaj_edit" name="data_montaj"
                                        value="{{ $editEntryDate }}">
                                    @if ($entryOldMatchesEditing && $errors->has('data_montaj'))
                                        <div class="text-danger small mt-1">{{ $errors->first('data_montaj') }}</div>
                                    @endif
                                </div>

                                <div class="col-lg-6">
                                    <label for="nume_mecanic_edit" class="form-label small text-muted mb-1">Nume mecanic
                                        <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control rounded-3" id="nume_mecanic_edit" name="nume_mecanic"
                                        value="{{ $editEntryMechanic }}">
                                    @if ($entryOldMatchesEditing && $errors->has('nume_mecanic'))
                                        <div class="text-danger small mt-1">{{ $errors->first('nume_mecanic') }}</div>
                                    @endif
                                </div>

                                <div class="col-12">
                                    <label for="observatii_edit_entry" class="form-label small text-muted mb-1">Observații</label>
                                    <textarea name="observatii" id="observatii_edit_entry" rows="3" class="form-control rounded-3">{{ $editEntryObservatii }}</textarea>
                                    @if ($entryOldMatchesEditing && $errors->has('observatii'))
                                        <div class="text-danger small mt-1">{{ $errors->first('observatii') }}</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer flex-wrap gap-2">
                            <a class="btn btn-outline-secondary rounded-3"
                                href="{{ route('service-masini.index', $queryParams + ['masina_id' => $selectedMasina->id]) }}">
                                Renunță
                            </a>
                            <button type="submit" class="btn btn-primary rounded-3">
                                <i class="fa-solid fa-rotate me-1"></i>Actualizează intervenția
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
        @endif
    @endif

        <div class="modal fade" id="createMasinaModal" tabindex="-1" aria-labelledby="createMasinaModalLabel"
            aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable">
            <form method="POST" action="{{ route('service-masini.store-masina') }}" class="modal-content">
                @csrf
                <input type="hidden" name="form_context" value="create_masina">
                <div class="modal-header">
                    <h5 class="modal-title" id="createMasinaModalLabel">
                        <i class="fa-solid fa-plus-circle me-1"></i>Adaugă mașină
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Închide"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="denumire_create" class="form-label small text-muted mb-1">Denumire mașină
                            <span class="text-danger">*</span></label>
                        <input type="text" name="denumire" id="denumire_create" class="form-control rounded-3"
                            value="{{ $createMasinaOld ? old('denumire') : '' }}" required>
                        @if ($createMasinaOld && $errors->has('denumire'))
                            <div class="text-danger small mt-1">{{ $errors->first('denumire') }}</div>
                        @endif
                    </div>
                    <div class="mb-3">
                        <label for="numar_inmatriculare_create" class="form-label small text-muted mb-1">Nr.
                            înmatriculare <span class="text-danger">*</span></label>
                        <input type="text" name="numar_inmatriculare" id="numar_inmatriculare_create"
                            class="form-control rounded-3"
                            value="{{ $createMasinaOld ? old('numar_inmatriculare') : '' }}" required>
                        @if ($createMasinaOld && $errors->has('numar_inmatriculare'))
                            <div class="text-danger small mt-1">{{ $errors->first('numar_inmatriculare') }}</div>
                        @endif
                    </div>
                    <div class="mb-3">
                        <label for="serie_sasiu_create" class="form-label small text-muted mb-1">Serie șasiu</label>
                        <input type="text" name="serie_sasiu" id="serie_sasiu_create" class="form-control rounded-3"
                            value="{{ $createMasinaOld ? old('serie_sasiu') : '' }}">
                        @if ($createMasinaOld && $errors->has('serie_sasiu'))
                            <div class="text-danger small mt-1">{{ $errors->first('serie_sasiu') }}</div>
                        @endif
                    </div>
                    <div class="mb-0">
                        <label for="observatii_create" class="form-label small text-muted mb-1">Observații</label>
                        <textarea name="observatii" id="observatii_create" rows="2" class="form-control rounded-3">{{ $createMasinaOld ? old('observatii') : '' }}</textarea>
                        @if ($createMasinaOld && $errors->has('observatii'))
                            <div class="text-danger small mt-1">{{ $errors->first('observatii') }}</div>
                        @endif
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary rounded-3" data-bs-dismiss="modal">Închide</button>
                    <button type="submit" class="btn btn-success rounded-3">
                        <i class="fa-solid fa-save me-1"></i>Salvează mașina
                    </button>
                </div>
            </form>
        </div>
    </div>

    @if ($selectedMasina)
        <div class="modal fade" id="editMasinaModal" tabindex="-1" aria-labelledby="editMasinaModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editMasinaModalLabel">
                            <i class="fa-solid fa-pen-to-square me-1"></i>Editează mașina selectată
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Închide"></button>
                    </div>
                    <form id="updateMasinaForm" method="POST"
                        action="{{ route('service-masini.update-masina', $selectedMasina) }}">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="form_context" value="update_masina">
                        <input type="hidden" name="masina_id" value="{{ $selectedMasinaId }}">
                        @foreach ($filters as $key => $value)
                            @if ($value !== null && $value !== '')
                                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                            @endif
                        @endforeach
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="denumire_edit" class="form-label small text-muted mb-1">Denumire mașină
                                    <span class="text-danger">*</span></label>
                                <input type="text" name="denumire" id="denumire_edit" class="form-control rounded-3"
                                    value="{{ $updateMasinaOld ? old('denumire') : $selectedMasina->denumire }}" required>
                                @if ($updateMasinaOld && $errors->has('denumire'))
                                    <div class="text-danger small mt-1">{{ $errors->first('denumire') }}</div>
                                @endif
                            </div>
                            <div class="mb-3">
                                <label for="numar_inmatriculare_edit" class="form-label small text-muted mb-1">Nr.
                                    înmatriculare <span class="text-danger">*</span></label>
                                <input type="text" name="numar_inmatriculare" id="numar_inmatriculare_edit"
                                    class="form-control rounded-3"
                                    value="{{ $updateMasinaOld ? old('numar_inmatriculare') : $selectedMasina->numar_inmatriculare }}"
                                    required>
                                @if ($updateMasinaOld && $errors->has('numar_inmatriculare'))
                                    <div class="text-danger small mt-1">{{ $errors->first('numar_inmatriculare') }}</div>
                                @endif
                            </div>
                            <div class="mb-3">
                                <label for="serie_sasiu_edit" class="form-label small text-muted mb-1">Serie șasiu</label>
                                <input type="text" name="serie_sasiu" id="serie_sasiu_edit" class="form-control rounded-3"
                                    value="{{ $updateMasinaOld ? old('serie_sasiu') : $selectedMasina->serie_sasiu }}">
                                @if ($updateMasinaOld && $errors->has('serie_sasiu'))
                                    <div class="text-danger small mt-1">{{ $errors->first('serie_sasiu') }}</div>
                                @endif
                            </div>
                            <div class="mb-0">
                                <label for="observatii_edit" class="form-label small text-muted mb-1">Observații</label>
                                <textarea name="observatii" id="observatii_edit" rows="2" class="form-control rounded-3">{{ $updateMasinaOld ? old('observatii') : $selectedMasina->observatii }}</textarea>
                                @if ($updateMasinaOld && $errors->has('observatii'))
                                    <div class="text-danger small mt-1">{{ $errors->first('observatii') }}</div>
                                @endif
                            </div>
                        </div>
                    </form>
                    <div class="modal-footer flex-wrap gap-2">
                        <form method="POST" action="{{ route('service-masini.destroy-masina', $selectedMasina) }}"
                            class="me-auto"
                            onsubmit="return confirm('Ești sigur că vrei să ștergi această mașină și intervențiile asociate?');">
                            @csrf
                            @method('DELETE')
                            @foreach ($filters as $key => $value)
                                @if ($value !== null && $value !== '')
                                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                @endif
                            @endforeach
                            <button type="submit" class="btn btn-outline-danger btn-sm rounded-3">
                                <i class="fa-solid fa-trash-can me-1"></i>Șterge mașina
                            </button>
                        </form>
                        <div class="ms-auto d-flex gap-2">
                            <button type="button" class="btn btn-outline-secondary rounded-3" data-bs-dismiss="modal">
                                Renunță
                            </button>
                            <button type="submit" class="btn btn-primary rounded-3" form="updateMasinaForm">
                                <i class="fa-solid fa-rotate me-1"></i>Actualizează mașina
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

@push('page-scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            function setupEntryForm(formId) {
                var form = document.getElementById(formId);
                if (!form) {
                    return;
                }

                function toggleFields() {
                    var selectedType = form.querySelector('input[name="tip"]:checked')?.value || 'piesa';

                    form.querySelectorAll('[data-entry="piesa"]').forEach(function (element) {
                        element.classList.toggle('d-none', selectedType !== 'piesa');
                    });

                    form.querySelectorAll('[data-entry="manual"]').forEach(function (element) {
                        element.classList.toggle('d-none', selectedType !== 'manual');
                    });
                }

                form.querySelectorAll('input[name="tip"]').forEach(function (radio) {
                    radio.addEventListener('change', toggleFields);
                });

                toggleFields();
            }

            setupEntryForm('create-entry-form');
            setupEntryForm('edit-entry-form');

            if (typeof bootstrap !== 'undefined') {
                document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (element) {
                    new bootstrap.Tooltip(element);
                });

                @if ($shouldShowCreateModal)
                    var createMasinaModalEl = document.getElementById('createMasinaModal');
                    if (createMasinaModalEl) {
                        var createMasinaModal = new bootstrap.Modal(createMasinaModalEl);
                        createMasinaModal.show();
                    }
                @endif

                @if ($shouldShowEditModal)
                    var editMasinaModalEl = document.getElementById('editMasinaModal');
                    if (editMasinaModalEl) {
                        var editMasinaModal = new bootstrap.Modal(editMasinaModalEl);
                        editMasinaModal.show();
                    }
                @endif

                @if ($shouldShowCreateEntryModal)
                    var createEntryModalEl = document.getElementById('createEntryModal');
                    if (createEntryModalEl) {
                        var createEntryModal = new bootstrap.Modal(createEntryModalEl);
                        createEntryModal.show();
                    }
                @endif

                @if ($shouldShowEditEntryModal)
                    var editEntryModalEl = document.getElementById('editEntryModal');
                    if (editEntryModalEl) {
                        var editEntryModal = new bootstrap.Modal(editEntryModalEl);
                        editEntryModal.show();
                    }
                @endif
            }
        });
    </script>
@endpush
