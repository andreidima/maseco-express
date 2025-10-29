@extends('layouts.app')

@php
    $filters = $filters ?? [];
    $selectedMasinaId = optional($selectedMasina)->id;
    $queryParams = collect($filters)
        ->reject(fn ($value) => $value === null || $value === '')
        ->toArray();
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

            @if (session('status'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('status') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Închide"></button>
                </div>
            @endif

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
                            <div id="service-cars-list" class="list-group mb-4"
                                style="max-height: 320px; overflow-y: auto;">
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
                                <div class="col-12">
                                    <label for="denumire" class="form-label small text-muted mb-1">Denumire mașină</label>
                                    <input type="text" name="denumire" id="denumire" class="form-control rounded-3"
                                        value="{{ old('denumire') }}" required>
                                    @error('denumire')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12">
                                    <label for="numar_inmatriculare_form" class="form-label small text-muted mb-1">Nr.
                                        înmatriculare</label>
                                    <input type="text" name="numar_inmatriculare" id="numar_inmatriculare_form"
                                        class="form-control rounded-3" value="{{ old('numar_inmatriculare') }}" required>
                                    @error('numar_inmatriculare')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12">
                                    <label for="serie_sasiu" class="form-label small text-muted mb-1">Serie șasiu</label>
                                    <input type="text" name="serie_sasiu" id="serie_sasiu" class="form-control rounded-3"
                                        value="{{ old('serie_sasiu') }}">
                                    @error('serie_sasiu')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12">
                                    <label for="observatii" class="form-label small text-muted mb-1">Observații</label>
                                    <textarea name="observatii" id="observatii" rows="2" class="form-control rounded-3">{{ old('observatii') }}</textarea>
                                    @error('observatii')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12 d-grid">
                                    <button type="submit" class="btn btn-sm btn-success rounded-3">
                                        <i class="fa-solid fa-save me-1"></i>Salvează mașina
                                    </button>
                                </div>
                            </form>
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
                                    @if ($selectedMasina->serie_sasiu)
                                        <div class="text-muted small">Serie șasiu: {{ $selectedMasina->serie_sasiu }}</div>
                                    @endif
                                </div>
                                <a class="btn btn-outline-primary btn-sm rounded-3"
                                    href="{{ route('service-masini.export', $queryParams + ['masina_id' => $selectedMasina->id]) }}">
                                    <i class="fa-solid fa-file-pdf me-1"></i>Descarcă PDF
                                </a>
                            </div>
                            <div class="card-body">
                                <form method="POST"
                                    action="{{ route('service-masini.entries.store', $selectedMasina) }}"
                                    class="row g-3" id="service-entry-form">
                                    @csrf
                                    @foreach ($filters as $key => $value)
                                        @if ($value !== null && $value !== '')
                                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                        @endif
                                    @endforeach

                                    <div class="col-12">
                                        <label class="form-label small text-muted mb-1">Tip intervenție</label>
                                        <div class="d-flex gap-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="tip" id="tip_piesa"
                                                    value="piesa" {{ old('tip', 'piesa') === 'piesa' ? 'checked' : '' }}>
                                                <label class="form-check-label" for="tip_piesa">
                                                    Alocare piesă din gestiune
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="tip" id="tip_manual"
                                                    value="manual" {{ old('tip') === 'manual' ? 'checked' : '' }}>
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
                                        <label for="gestiune_piesa_id" class="form-label small text-muted mb-1">Piesă</label>
                                        <select name="gestiune_piesa_id" id="gestiune_piesa_id"
                                            class="form-select rounded-3">
                                            <option value="">Selectează piesa</option>
                                            @foreach ($availablePieces as $piesa)
                                                <option value="{{ $piesa->id }}"
                                                    @selected((int) old('gestiune_piesa_id') === $piesa->id)>
                                                    {{ $piesa->denumire }} ({{ $piesa->cod }}) - {{ number_format((float) $piesa->nr_bucati, 2) }} buc
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('gestiune_piesa_id')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-3" data-entry="piesa">
                                        <label for="cantitate" class="form-label small text-muted mb-1">Cantitate</label>
                                        <input type="number" step="0.01" min="0" class="form-control rounded-3"
                                            id="cantitate" name="cantitate" value="{{ old('cantitate', '1') }}">
                                        @error('cantitate')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6" data-entry="manual">
                                        <label for="denumire_interventie" class="form-label small text-muted mb-1">Denumire
                                            intervenție</label>
                                        <input type="text" class="form-control rounded-3" id="denumire_interventie"
                                            name="denumire_interventie" value="{{ old('denumire_interventie') }}">
                                        @error('denumire_interventie')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label for="data_montaj" class="form-label small text-muted mb-1">Data intervenției</label>
                                        <input type="date" class="form-control rounded-3" id="data_montaj" name="data_montaj"
                                            value="{{ old('data_montaj', now()->toDateString()) }}" required>
                                        @error('data_montaj')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label for="nume_mecanic" class="form-label small text-muted mb-1">Nume mecanic</label>
                                        <input type="text" class="form-control rounded-3" id="nume_mecanic" name="nume_mecanic"
                                            value="{{ old('nume_mecanic') }}" required>
                                        @error('nume_mecanic')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-12">
                                        <label for="observatii_interventie" class="form-label small text-muted mb-1">Observații</label>
                                        <textarea name="observatii" id="observatii_interventie" rows="3" class="form-control rounded-3">{{ old('observatii') }}</textarea>
                                        @error('observatii')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-12 d-flex justify-content-end">
                                        <button type="submit" class="btn btn-primary rounded-3">
                                            <i class="fa-solid fa-paper-plane me-1"></i>Salvează intervenția
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
                                                <td>{{ $entry->cod_piesa ?? '—' }}</td>
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
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center text-muted py-4">
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
        function initializeServiceMasiniPage() {
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

            const carList = document.getElementById('service-cars-list');
            const activeCar = carList?.querySelector('.list-group-item.active');

            if (carList && activeCar) {
                const offsetTop = activeCar.offsetTop;
                carList.scrollTop = Math.max(0, offsetTop);
            }
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initializeServiceMasiniPage);
        } else {
            initializeServiceMasiniPage();
        }
    </script>
@endpush
