@extends('layouts.app')

@php
    $filters = $filters ?? [];
    $selectedMasinaId = optional($selectedMasina)->id;
    $queryParams = collect($filters)
        ->reject(fn ($value) => $value === null || $value === '')
        ->toArray();
    $editingEntry = $editingEntry ?? null;
    $isEditingEntry = $editingEntry !== null;
    $formContext = old('form_context');
    $entryOldMatchesEditing = $isEditingEntry && $formContext === 'entry_update' && (int) old('entry_id') === optional($editingEntry)->id;
    $useOldForEntry = $formContext === 'entry_store' || $entryOldMatchesEditing;
    $createMasinaOld = $formContext === 'create_masina';
@endphp

@section('content')
    <div class="mx-3 px-3 card mx-auto" style="border-radius: 40px 40px 40px 40px;">
        <div class="row card-header align-items-center" style="border-radius: 40px 40px 0px 0px;">
            <div class="col-lg-3">
                <span class="badge culoare1 fs-5">
                    <i class="fa-solid fa-screwdriver-wrench me-1"></i>Service mașini
                </span>
            </div>
            <div class="col-lg-9 mb-2">
                <form class="needs-validation" novalidate method="GET" action="{{ route('service-masini.index') }}">
                    <div class="row gy-2 gx-3 align-items-end">
                        <div class="col-lg-4 col-md-6">
                            <label for="numar_inmatriculare" class="form-label small text-muted mb-1">
                                <i class="fa-solid fa-car me-1"></i>Nr. înmatriculare / Denumire mașină
                            </label>
                            <input type="text" class="form-control rounded-3" id="numar_inmatriculare"
                                name="numar_inmatriculare" placeholder="Ex: B00ABC"
                                value="{{ $filters['numar_inmatriculare'] ?? '' }}" autocomplete="off">
                        </div>
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
                                href="{{ route('service-masini.index') }}">
                                <i class="far fa-trash-alt text-white me-1"></i>Resetează
                            </a>
                        </div>
                    </div>
                </form>
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
                <div class="col-lg-4">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span class="fw-semibold"><i class="fa-solid fa-car-side me-1"></i>Mașini</span>
                        </div>
                        <div class="card-body">
                            <div class="list-group mb-4" style="max-height: 320px; overflow-y: auto;">
                                @forelse ($masini as $masina)
                                    @php
                                        $masinaQuery = $queryParams;
                                        $masinaQuery['masina_id'] = $masina->id;
                                    @endphp
                                    <a href="{{ route('service-masini.index', $masinaQuery) }}"
                                        class="list-group-item list-group-item-action rounded-3 mb-2 {{ $selectedMasinaId === $masina->id ? 'active text-white' : '' }}">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <div class="fw-semibold">{{ $masina->denumire }}</div>
                                                <small class="text-muted {{ $selectedMasinaId === $masina->id ? 'text-white-50' : '' }}">
                                                    {{ $masina->numar_inmatriculare }}
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

                            <h6 class="fw-semibold mb-3"><i class="fa-solid fa-plus-circle me-1"></i>Adaugă mașină</h6>
                            <form method="POST" action="{{ route('service-masini.store-masina') }}" class="row g-2">
                                @csrf
                                <input type="hidden" name="form_context" value="create_masina">
                                <div class="col-12">
                                    <label for="denumire" class="form-label small text-muted mb-1">Denumire mașină <span class="text-danger">*</span></label>
                                    <input type="text" name="denumire" id="denumire" class="form-control rounded-3"
                                        value="{{ $createMasinaOld ? old('denumire') : '' }}" required>
                                    @if ($createMasinaOld && $errors->has('denumire'))
                                        <div class="text-danger small mt-1">{{ $errors->first('denumire') }}</div>
                                    @endif
                                </div>
                                <div class="col-12">
                                    <label for="numar_inmatriculare_form" class="form-label small text-muted mb-1">Nr.
                                        înmatriculare <span class="text-danger">*</span></label>
                                    <input type="text" name="numar_inmatriculare" id="numar_inmatriculare_form"
                                        class="form-control rounded-3" value="{{ $createMasinaOld ? old('numar_inmatriculare') : '' }}" required>
                                    @if ($createMasinaOld && $errors->has('numar_inmatriculare'))
                                        <div class="text-danger small mt-1">{{ $errors->first('numar_inmatriculare') }}</div>
                                    @endif
                                </div>
                                <div class="col-12">
                                    <label for="serie_sasiu" class="form-label small text-muted mb-1">Serie șasiu</label>
                                    <input type="text" name="serie_sasiu" id="serie_sasiu" class="form-control rounded-3"
                                        value="{{ $createMasinaOld ? old('serie_sasiu') : '' }}">
                                    @if ($createMasinaOld && $errors->has('serie_sasiu'))
                                        <div class="text-danger small mt-1">{{ $errors->first('serie_sasiu') }}</div>
                                    @endif
                                </div>
                                <div class="col-12">
                                    <label for="observatii" class="form-label small text-muted mb-1">Observații</label>
                                    <textarea name="observatii" id="observatii" rows="2" class="form-control rounded-3">{{ $createMasinaOld ? old('observatii') : '' }}</textarea>
                                    @if ($createMasinaOld && $errors->has('observatii'))
                                        <div class="text-danger small mt-1">{{ $errors->first('observatii') }}</div>
                                    @endif
                                </div>
                                <div class="col-12 d-grid">
                                    <button type="submit" class="btn btn-sm btn-success rounded-3">
                                        <i class="fa-solid fa-save me-1"></i>Salvează mașina
                                    </button>
                                </div>
                            </form>

                            @if ($selectedMasina)
                                @php
                                    $updateMasinaOld = $formContext === 'update_masina' && (int) old('masina_id') === $selectedMasinaId;
                                @endphp
                                <hr class="my-4">
                                <h6 class="fw-semibold mb-3"><i class="fa-solid fa-pen-to-square me-1"></i>Editează mașina selectată</h6>
                                <form method="POST" action="{{ route('service-masini.update-masina', $selectedMasina) }}" class="row g-2">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="form_context" value="update_masina">
                                    <input type="hidden" name="masina_id" value="{{ $selectedMasinaId }}">
                                    <div class="col-12">
                                        <label for="denumire_edit" class="form-label small text-muted mb-1">Denumire mașină <span class="text-danger">*</span></label>
                                        <input type="text" name="denumire" id="denumire_edit" class="form-control rounded-3"
                                            value="{{ $updateMasinaOld ? old('denumire') : $selectedMasina->denumire }}" required>
                                        @if ($updateMasinaOld && $errors->has('denumire'))
                                            <div class="text-danger small mt-1">{{ $errors->first('denumire') }}</div>
                                        @endif
                                    </div>
                                    <div class="col-12">
                                        <label for="numar_inmatriculare_edit" class="form-label small text-muted mb-1">Nr. înmatriculare <span class="text-danger">*</span></label>
                                        <input type="text" name="numar_inmatriculare" id="numar_inmatriculare_edit"
                                            class="form-control rounded-3"
                                            value="{{ $updateMasinaOld ? old('numar_inmatriculare') : $selectedMasina->numar_inmatriculare }}"
                                            required>
                                        @if ($updateMasinaOld && $errors->has('numar_inmatriculare'))
                                            <div class="text-danger small mt-1">{{ $errors->first('numar_inmatriculare') }}</div>
                                        @endif
                                    </div>
                                    <div class="col-12">
                                        <label for="serie_sasiu_edit" class="form-label small text-muted mb-1">Serie șasiu</label>
                                        <input type="text" name="serie_sasiu" id="serie_sasiu_edit" class="form-control rounded-3"
                                            value="{{ $updateMasinaOld ? old('serie_sasiu') : $selectedMasina->serie_sasiu }}">
                                        @if ($updateMasinaOld && $errors->has('serie_sasiu'))
                                            <div class="text-danger small mt-1">{{ $errors->first('serie_sasiu') }}</div>
                                        @endif
                                    </div>
                                    <div class="col-12">
                                        <label for="observatii_edit" class="form-label small text-muted mb-1">Observații</label>
                                        <textarea name="observatii" id="observatii_edit" rows="2" class="form-control rounded-3">{{ $updateMasinaOld ? old('observatii') : $selectedMasina->observatii }}</textarea>
                                        @if ($updateMasinaOld && $errors->has('observatii'))
                                            <div class="text-danger small mt-1">{{ $errors->first('observatii') }}</div>
                                        @endif
                                    </div>
                                    @foreach ($filters as $key => $value)
                                        @if ($value !== null && $value !== '')
                                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                        @endif
                                    @endforeach
                                    <div class="col-12 d-grid">
                                        <button type="submit" class="btn btn-sm btn-primary rounded-3">
                                            <i class="fa-solid fa-rotate me-1"></i>Actualizează mașina
                                        </button>
                                    </div>
                                </form>

                                <form method="POST" action="{{ route('service-masini.destroy-masina', $selectedMasina) }}" class="mt-3"
                                    onsubmit="return confirm('Ești sigur că vrei să ștergi această mașină și intervențiile asociate?');">
                                    @csrf
                                    @method('DELETE')
                                    @foreach ($filters as $key => $value)
                                        @if ($value !== null && $value !== '')
                                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                        @endif
                                    @endforeach
                                    <button type="submit" class="btn btn-sm btn-outline-danger w-100 rounded-3">
                                        <i class="fa-solid fa-trash-can me-1"></i>Șterge mașina
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-lg-8">
                    @if ($selectedMasina)
                        <div class="card shadow-sm border-0 mb-4">
                            <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                                <div>
                                    <h5 class="mb-0">{{ $selectedMasina->denumire }}</h5>
                                    <small class="text-muted">Nr. înmatriculare: {{ $selectedMasina->numar_inmatriculare }}</small>
                                </div>
                                <a class="btn btn-outline-primary btn-sm rounded-3"
                                    href="{{ route('service-masini.export', $queryParams + ['masina_id' => $selectedMasina->id]) }}">
                                    <i class="fa-solid fa-file-pdf me-1"></i>Descarcă PDF
                                </a>
                            </div>
                            <div class="card-body">
                                @php
                                    $entryFormAction = $isEditingEntry
                                        ? route('service-masini.entries.update', [$selectedMasina, $editingEntry])
                                        : route('service-masini.entries.store', $selectedMasina);
                                    $defaultTip = $isEditingEntry ? $editingEntry->tip : 'piesa';
                                    $defaultCantitate = $isEditingEntry && $editingEntry->cantitate !== null
                                        ? number_format((float) $editingEntry->cantitate, 2, '.', '')
                                        : '1';
                                @endphp
                                <form method="POST" action="{{ $entryFormAction }}" class="row g-3" id="service-entry-form">
                                    @csrf
                                    @if ($isEditingEntry)
                                        @method('PUT')
                                    @endif
                                    @foreach ($filters as $key => $value)
                                        @if ($value !== null && $value !== '')
                                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                        @endif
                                    @endforeach
                                    <input type="hidden" name="form_context" value="{{ $isEditingEntry ? 'entry_update' : 'entry_store' }}">
                                    @if ($isEditingEntry)
                                        <input type="hidden" name="entry_id" value="{{ $editingEntry->id }}">
                                    @endif

                                    <div class="col-12">
                                        <label class="form-label small text-muted mb-1">Tip intervenție <span class="text-danger">*</span></label>
                                        <div class="d-flex gap-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="tip" id="tip_piesa"
                                                    value="piesa"
                                                    {{ ($useOldForEntry ? old('tip', $defaultTip) : $defaultTip) === 'piesa' ? 'checked' : '' }}>
                                                <label class="form-check-label" for="tip_piesa">
                                                    Alocare piesă din gestiune
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="tip" id="tip_manual"
                                                    value="manual"
                                                    {{ ($useOldForEntry ? old('tip', $defaultTip) : $defaultTip) === 'manual' ? 'checked' : '' }}>
                                                <label class="form-check-label" for="tip_manual">
                                                    Intervenție manuală
                                                </label>
                                            </div>
                                        </div>
                                        @error('tip')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6" data-entry="piesa">
                                        <label for="gestiune_piesa_id" class="form-label small text-muted mb-1">Piesă <span class="text-danger">*</span>
                                            <small class="text-muted">(pentru alocări din gestiune)</small>
                                        </label>
                                        @php
                                            $selectedPiece = $useOldForEntry ? (int) old('gestiune_piesa_id') : ($isEditingEntry ? (int) $editingEntry->gestiune_piesa_id : null);
                                        @endphp
                                        <select name="gestiune_piesa_id" id="gestiune_piesa_id"
                                            class="form-select rounded-3">
                                            <option value="">Selectează piesa</option>
                                            @foreach ($availablePieces as $piesa)
                                                <option value="{{ $piesa->id }}" @selected($selectedPiece === $piesa->id)>
                                                    {{ $piesa->denumire }} ({{ $piesa->cod }}) - {{ number_format((float) $piesa->nr_bucati, 2) }} buc
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('gestiune_piesa_id')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-3" data-entry="piesa">
                                        <label for="cantitate" class="form-label small text-muted mb-1">Cantitate <span class="text-danger">*</span>
                                            <small class="text-muted">(pentru alocări din gestiune)</small>
                                        </label>
                                        <input type="number" step="0.01" min="0" class="form-control rounded-3"
                                            id="cantitate" name="cantitate"
                                            value="{{ $useOldForEntry ? old('cantitate') : $defaultCantitate }}">
                                        @error('cantitate')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6" data-entry="manual">
                                        <label for="denumire_interventie" class="form-label small text-muted mb-1">Denumire
                                            intervenție <span class="text-danger">*</span>
                                            <small class="text-muted">(pentru intervenții manuale)</small>
                                        </label>
                                        <input type="text" class="form-control rounded-3" id="denumire_interventie"
                                            name="denumire_interventie"
                                            value="{{ $useOldForEntry ? old('denumire_interventie') : ($isEditingEntry ? $editingEntry->denumire_interventie : '') }}">
                                        @error('denumire_interventie')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label for="data_montaj" class="form-label small text-muted mb-1">Data intervenției <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control rounded-3" id="data_montaj" name="data_montaj"
                                            value="{{ $useOldForEntry ? old('data_montaj') : ($isEditingEntry ? optional($editingEntry->data_montaj)->toDateString() : now()->toDateString()) }}"
                                            required>
                                        @error('data_montaj')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label for="nume_mecanic" class="form-label small text-muted mb-1">Nume mecanic <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control rounded-3" id="nume_mecanic" name="nume_mecanic"
                                            value="{{ $useOldForEntry ? old('nume_mecanic') : ($isEditingEntry ? $editingEntry->nume_mecanic : '') }}" required>
                                        @error('nume_mecanic')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-12">
                                        <label for="observatii_interventie" class="form-label small text-muted mb-1">Observații</label>
                                        <textarea name="observatii" id="observatii_interventie" rows="3" class="form-control rounded-3">{{ $useOldForEntry ? old('observatii') : ($isEditingEntry ? $editingEntry->observatii : '') }}</textarea>
                                        @error('observatii')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-12 d-flex justify-content-end gap-2">
                                        @if ($isEditingEntry)
                                            <a class="btn btn-outline-secondary rounded-3"
                                                href="{{ route('service-masini.index', $queryParams + ['masina_id' => $selectedMasina->id]) }}">
                                                Renunță
                                            </a>
                                        @endif
                                        <button type="submit" class="btn btn-primary rounded-3">
                                            <i class="fa-solid fa-paper-plane me-1"></i>{{ $isEditingEntry ? 'Actualizează intervenția' : 'Salvează intervenția' }}
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="card shadow-sm border-0">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="fa-solid fa-clipboard-list me-1"></i>Istoric intervenții</h6>
                            </div>
                            <div class="table-responsive">
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
                                            <th>Observații</th>
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
                                                <td class="text-wrap" style="max-width: 220px;">{{ $entry->observatii ?? '—' }}</td>
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
                            </div>
                            <div class="card-footer">
                                {{ $entries->links() }}
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
@endsection

@push('page-scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            function toggleEntryFields() {
                const selectedType = document.querySelector('input[name="tip"]:checked')?.value || 'piesa';

                document.querySelectorAll('#service-entry-form [data-entry="piesa"]').forEach(function (element) {
                    element.classList.toggle('d-none', selectedType !== 'piesa');
                });

                document.querySelectorAll('#service-entry-form [data-entry="manual"]').forEach(function (element) {
                    element.classList.toggle('d-none', selectedType !== 'manual');
                });
            }

            document.querySelectorAll('input[name="tip"]').forEach(function (radio) {
                radio.addEventListener('change', toggleEntryFields);
            });

            toggleEntryFields();
        });
    </script>
@endpush
