@php
    $isFlashDivision = (bool) ($isFlashDivision ?? ((int) optional($valabilitate->divizie)->id === 1));
    $grupuri = $valabilitate->cursaGrupuri ?? collect();
    $isEdit = (bool) ($isEdit ?? false);
    $formAction = $formAction
        ?? ($isEdit
            ? route('valabilitati.curse.update', [$valabilitate, $cursa])
            : route('valabilitati.curse.store', $valabilitate));
    $httpMethod = $isEdit ? 'PUT' : 'POST';
    $redirectTo = $redirectTo
        ?? route('valabilitati.curse.index', $valabilitate);

    $nrCursa = old('nr_cursa', $cursa->nr_cursa);
    $dataCursa = old('data_cursa', optional($cursa->data_cursa)->format('Y-m-d\TH:i'));
    $incarcareLocalitate = old('incarcare_localitate', $cursa->incarcare_localitate);
    $incarcareCodPostal = old('incarcare_cod_postal', $cursa->incarcare_cod_postal);
    $incarcareTaraId = old('incarcare_tara_id', $cursa->incarcare_tara_id);
    $incarcareTaraText = old('incarcare_tara_text', optional($cursa->incarcareTara)->nume);
    $descarcareLocalitate = old('descarcare_localitate', $cursa->descarcare_localitate);
    $descarcareCodPostal = old('descarcare_cod_postal', $cursa->descarcare_cod_postal);
    $descarcareTaraId = old('descarcare_tara_id', $cursa->descarcare_tara_id);
    $descarcareTaraText = old('descarcare_tara_text', optional($cursa->descarcareTara)->nume);

    $kmMapsGol = old('km_maps_gol', $cursa->km_maps_gol);
    $kmMapsPlin = old('km_maps_plin', $cursa->km_maps_plin);
    $kmCuTaxa = old('km_cu_taxa', $cursa->km_cu_taxa);
    $kmFlashGol = old('km_flash_gol', $cursa->km_flash_gol);
    $kmFlashPlin = old('km_flash_plin', $cursa->km_flash_plin);
    $alteTaxe = old('alte_taxe', $cursa->alte_taxe);
    $fuelTax = old('fuel_tax', $cursa->fuel_tax);
    $sumaIncasata = old('suma_incasata', $cursa->suma_incasata);
    $dailyContributionIncasata = old('daily_contribution_incasata', $cursa->daily_contribution_incasata);
    $cursaGrupId = old('cursa_grup_id', $cursa->cursa_grup_id);
    $observatii = old('observatii', $cursa->observatii);

    $stops = collect(old('stops') ?? $cursa->stops?->map(function ($stop) {
        return [
            'type' => $stop->type,
            'cod_postal' => $stop->cod_postal,
            'localitate' => $stop->localitate,
            'tara' => $stop->tara,
            'position' => $stop->position,
        ];
    }) ?? [])->values();

    $incarcareStops = $stops
        ->where('type', 'incarcare')
        ->values()
        ->map(fn ($stop, $index) => [
            'type' => 'incarcare',
            'cod_postal' => $stop['cod_postal'] ?? '',
            'localitate' => $stop['localitate'] ?? '',
            'tara' => $stop['tara'] ?? '',
            'position' => (int) ($stop['position'] ?? ($index + 1)),
        ]);

    $descarcareStops = $stops
        ->where('type', 'descarcare')
        ->values()
        ->map(fn ($stop, $index) => [
            'type' => 'descarcare',
            'cod_postal' => $stop['cod_postal'] ?? '',
            'localitate' => $stop['localitate'] ?? '',
            'tara' => $stop['tara'] ?? '',
            'position' => (int) ($stop['position'] ?? ($index + 1)),
        ]);
@endphp

@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <a href="{{ $backUrl }}" class="btn btn-link px-0 text-decoration-none">
                    <i class="fa-solid fa-arrow-left-long me-1"></i>
                    Înapoi la curse
                </a>
                <h1 class="h4 fw-semibold mb-0">
                    {{ $isEdit ? 'Editează cursa #' . $cursa->nr_ordine : 'Adaugă cursă' }}
                </h1>
                <div class="text-muted small">
                    {{ $valabilitate->divizie->nume ?? 'Fără divizie' }} — valabilitate #{{ $valabilitate->id }}
                </div>
            </div>
            <div>
                <a href="{{ route('valabilitati.curse.index', $valabilitate) }}" class="btn btn-outline-secondary btn-sm">
                    Vezi tabel curse
                </a>
            </div>
        </div>

        @include('errors')

        <div class="card shadow-sm">
            <div class="card-body">
                @unless ($isFlashDivision)
                    <datalist id="valabilitati-curse-tari">
                        @foreach ($tari as $tara)
                            <option value="{{ $tara->nume }}" data-id="{{ $tara->id }}"></option>
                        @endforeach
                    </datalist>
                @endunless

                <form action="{{ $formAction }}" method="POST" novalidate data-cursa-form>
                    @csrf
                    @if ($httpMethod !== 'POST')
                        @method($httpMethod)
                    @endif
                    <input type="hidden" name="redirect_to" value="{{ $redirectTo }}">
                    <div class="border rounded-3 p-3 mb-3" style="background: #f8f9fb;">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <div class="flex-shrink-0 bg-primary rounded-circle" style="width: 10px; height: 10px;"></div>
                            <h2 class="h6 mb-0 text-uppercase text-muted">Date cursă</h2>
                        </div>
                        <div class="row g-3">
                        <div class="col-12 col-md-3">
                            <label class="form-label mb-0">Număr cursă</label>
                            <input type="text" name="nr_cursa" class="form-control @error('nr_cursa') is-invalid @enderror" value="{{ $nrCursa }}" maxlength="255">
                            @error('nr_cursa')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12 col-md-3">
                            <label class="form-label mb-0">Data și ora cursei</label>
                            <input type="datetime-local" name="data_cursa" class="form-control @error('data_cursa') is-invalid @enderror" value="{{ $dataCursa }}" step="60">
                            @error('data_cursa')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        @unless ($isFlashDivision)
                            <div class="col-12 col-md-3">
                                <label class="form-label mb-0">Localitate încărcare</label>
                                <input type="text" name="incarcare_localitate" class="form-control @error('incarcare_localitate') is-invalid @enderror" value="{{ $incarcareLocalitate }}" maxlength="255">
                                @error('incarcare_localitate')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12 col-md-3">
                                <label class="form-label mb-0">Cod poștal încărcare</label>
                                <input type="text" name="incarcare_cod_postal" class="form-control @error('incarcare_cod_postal') is-invalid @enderror" value="{{ $incarcareCodPostal }}" maxlength="255">
                                @error('incarcare_cod_postal')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12 col-md-3">
                                <label class="form-label mb-0">Țară încărcare</label>
                                <input type="hidden" name="incarcare_tara_id" value="{{ $incarcareTaraId }}">
                                <input type="text" name="incarcare_tara_text" class="form-control @error('incarcare_tara_id') is-invalid @enderror" value="{{ $incarcareTaraText }}" maxlength="255" list="valabilitati-curse-tari" autocomplete="off">
                                @error('incarcare_tara_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12 col-md-3">
                                <label class="form-label mb-0">Localitate descărcare</label>
                                <input type="text" name="descarcare_localitate" class="form-control @error('descarcare_localitate') is-invalid @enderror" value="{{ $descarcareLocalitate }}" maxlength="255">
                                @error('descarcare_localitate')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12 col-md-3">
                                <label class="form-label mb-0">Cod poștal descărcare</label>
                                <input type="text" name="descarcare_cod_postal" class="form-control @error('descarcare_cod_postal') is-invalid @enderror" value="{{ $descarcareCodPostal }}" maxlength="255">
                                @error('descarcare_cod_postal')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12 col-md-3">
                                <label class="form-label mb-0">Țară descărcare</label>
                                <input type="hidden" name="descarcare_tara_id" value="{{ $descarcareTaraId }}">
                                <input type="text" name="descarcare_tara_text" class="form-control @error('descarcare_tara_id') is-invalid @enderror" value="{{ $descarcareTaraText }}" maxlength="255" list="valabilitati-curse-tari" autocomplete="off">
                                @error('descarcare_tara_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        @endunless

                        @if (! $isFlashDivision)
                            <div class="col-12 col-md-3">
                                <label class="form-label mb-0">Km bord încărcare</label>
                                <input type="number" name="km_bord_incarcare" class="form-control @error('km_bord_incarcare') is-invalid @enderror" value="{{ old('km_bord_incarcare', $cursa->km_bord_incarcare) }}" min="0" step="1">
                                @error('km_bord_incarcare')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12 col-md-3">
                                <label class="form-label mb-0">Km bord descărcare</label>
                                <input type="number" name="km_bord_descarcare" class="form-control @error('km_bord_descarcare') is-invalid @enderror" value="{{ old('km_bord_descarcare', $cursa->km_bord_descarcare) }}" min="0" step="1">
                                @error('km_bord_descarcare')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        @endif

                        <div class="col-12 col-md-3">
                            <label class="form-label mb-0">Km Maps gol</label>
                            <input type="number" name="km_maps_gol" class="form-control @error('km_maps_gol') is-invalid @enderror" value="{{ $kmMapsGol }}" min="0" step="1">
                            @error('km_maps_gol')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-3">
                            <label class="form-label mb-0">Km Maps plin</label>
                            <input type="number" name="km_maps_plin" class="form-control @error('km_maps_plin') is-invalid @enderror" value="{{ $kmMapsPlin }}" min="0" step="1">
                            @error('km_maps_plin')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        @if ($isFlashDivision)
                            <div class="col-12 col-md-3">
                                <label class="form-label mb-0">Km cu taxă</label>
                                <input type="number" name="km_cu_taxa" class="form-control @error('km_cu_taxa') is-invalid @enderror" value="{{ $kmCuTaxa }}" min="0" step="1">
                                @error('km_cu_taxa')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12 col-md-3">
                                <label class="form-label mb-0">Km Flash gol</label>
                                <input type="number" name="km_flash_gol" class="form-control @error('km_flash_gol') is-invalid @enderror" value="{{ $kmFlashGol }}" min="0" step="1">
                                @error('km_flash_gol')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12 col-md-3">
                                <label class="form-label mb-0">Km Flash plin</label>
                                <input type="number" name="km_flash_plin" class="form-control @error('km_flash_plin') is-invalid @enderror" value="{{ $kmFlashPlin }}" min="0" step="1">
                                @error('km_flash_plin')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12 col-md-3">
                                <label class="form-label mb-0">Alte taxe</label>
                                <input type="number" name="alte_taxe" class="form-control @error('alte_taxe') is-invalid @enderror" value="{{ $alteTaxe }}" step="0.01">
                                @error('alte_taxe')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12 col-md-3">
                                <label class="form-label mb-0">Fuel tax</label>
                                <input type="number" name="fuel_tax" class="form-control @error('fuel_tax') is-invalid @enderror" value="{{ $fuelTax }}" step="0.01">
                                @error('fuel_tax')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12 col-md-3">
                                <label class="form-label mb-0">Sumă încasată</label>
                                <input type="number" name="suma_incasata" class="form-control @error('suma_incasata') is-invalid @enderror" value="{{ $sumaIncasata }}" step="0.01">
                                @error('suma_incasata')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12 col-md-3">
                                <label class="form-label mb-0">Daily contribution (încasat)</label>
                                <input type="number" name="daily_contribution_incasata" class="form-control @error('daily_contribution_incasata') is-invalid @enderror" value="{{ $dailyContributionIncasata }}" step="0.01">
                                @error('daily_contribution_incasata')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        @endif

                            <div class="col-12 col-md-3">
                                <label class="form-label mb-0">Grup cursă</label>
                                <select name="cursa_grup_id" class="form-select @error('cursa_grup_id') is-invalid @enderror">
                                    <option value="">Fără grup</option>
                                    @foreach ($grupuri as $grup)
                                        <option value="{{ $grup->id }}" @selected((string) $cursaGrupId === (string) $grup->id)>
                                            {{ $isFlashDivision ? ($grup->rr ?? 'Fără RR') : ($grup->nume ?? 'Fără nume') }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('cursa_grup_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label mb-0">Observații</label>
                                <textarea name="observatii" class="form-control @error('observatii') is-invalid @enderror" rows="3">{{ $observatii }}</textarea>
                                @error('observatii')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    @if ($isFlashDivision)
                        <div class="row g-3 mt-2">
                            <div class="col-12 col-lg-6">
                                <div class="border rounded-3 p-3 h-100" style="background: #fefefe;">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="flex-shrink-0 bg-success rounded-circle" style="width: 10px; height: 10px;"></div>
                                            <h2 class="h6 mb-0 text-uppercase text-muted">Încărcări</h2>
                                        </div>
                                        <button type="button" class="btn btn-success btn-sm" data-stop-add data-stop-target="incarcare">
                                            <i class="fa-solid fa-plus me-1"></i>Adaugă
                                        </button>
                                    </div>
                                    <div class="stop-list" data-stop-manager data-stop-type="incarcare" data-initial-stops='@json($incarcareStops)' data-stop-items></div>
                                </div>
                            </div>
                            <div class="col-12 col-lg-6">
                                <div class="border rounded-3 p-3 h-100" style="background: #fefefe;">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="flex-shrink-0 bg-info rounded-circle" style="width: 10px; height: 10px;"></div>
                                            <h2 class="h6 mb-0 text-uppercase text-muted">Descărcări</h2>
                                        </div>
                                        <button type="button" class="btn btn-success btn-sm" data-stop-add data-stop-target="descarcare">
                                            <i class="fa-solid fa-plus me-1"></i>Adaugă
                                        </button>
                                    </div>
                                    <div class="stop-list" data-stop-manager data-stop-type="descarcare" data-initial-stops='@json($descarcareStops)' data-stop-items></div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="d-flex flex-column flex-sm-row gap-2 justify-content-between mt-4">
                        <a href="{{ $backUrl }}" class="btn btn-outline-secondary">Renunță</a>
                        <button type="submit" class="btn btn-primary">Salvează modificările</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('page-styles')
    <style>
        .stop-list {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .stop-item {
            background: #fff;
            border: 1px solid #dee2e6;
            border-radius: 0.5rem;
            padding: 0.75rem;
        }

        .stop-item__actions {
            display: flex;
            gap: 0.5rem;
            justify-content: flex-end;
        }
    </style>
@endpush

@push('page-scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.querySelector('[data-cursa-form]');
            if (!form) return;

            const parseStops = (raw) => {
                if (!raw) return [];
                try {
                    const parsed = JSON.parse(raw);
                    return Array.isArray(parsed) ? parsed : [];
                } catch (_) {
                    return [];
                }
            };

            const stopManagers = new Map();
            let stopIndex = 0;

            const buildStopManager = (container) => {
                const type = container.dataset.stopType || 'incarcare';
                let stops = parseStops(container.dataset.initialStops)
                    .sort((a, b) => (Number.parseInt(a?.position ?? 0, 10) || 0) - (Number.parseInt(b?.position ?? 0, 10) || 0))
                    .map((stop) => ({ ...stop, formIndex: typeof stop.formIndex === 'number' ? stop.formIndex : stopIndex++ }));

                const normalise = () => {
                    stops = (stops || [])
                        .filter((stop) => stop && typeof stop === 'object')
                        .map((stop, index) => ({
                            type,
                            cod_postal: String(stop.cod_postal ?? ''),
                            localitate: String(stop.localitate ?? ''),
                            tara: String(stop.tara ?? ''),
                            position: index + 1,
                            formIndex: typeof stop.formIndex === 'number' ? stop.formIndex : stopIndex++,
                        }));
                };

                const moveStop = (fromIndex, toIndex) => {
                    if (toIndex < 0 || toIndex >= stops.length || fromIndex === toIndex) return;
                    const [moved] = stops.splice(fromIndex, 1);
                    stops.splice(toIndex, 0, moved);
                    render();
                };

                const render = () => {
                    normalise();
                    container.innerHTML = '';

                    stops.forEach((stop, index) => {
                        const wrapper = document.createElement('div');
                        wrapper.className = 'stop-item';

                        const row = document.createElement('div');
                        row.className = 'row g-2 align-items-end';

                        const hiddenType = document.createElement('input');
                        hiddenType.type = 'hidden';
                        hiddenType.name = `stops[${stop.formIndex}][type]`;
                        hiddenType.value = type;

                        const hiddenPosition = document.createElement('input');
                        hiddenPosition.type = 'hidden';
                        hiddenPosition.name = `stops[${stop.formIndex}][position]`;
                        hiddenPosition.value = String(stop.position);

                        const postalCol = document.createElement('div');
                        postalCol.className = 'col-12 col-sm-4';
                        postalCol.innerHTML = `
                            <label class="form-label small text-uppercase fw-semibold mb-1">Cod poștal</label>
                            <input type="text" name="stops[${stop.formIndex}][cod_postal]" class="form-control form-control-sm" value="${stop.cod_postal ?? ''}">
                        `;

                        const cityCol = document.createElement('div');
                        cityCol.className = 'col-12 col-sm-4';
                        cityCol.innerHTML = `
                            <label class="form-label small text-uppercase fw-semibold mb-1">Localitate</label>
                            <input type="text" name="stops[${stop.formIndex}][localitate]" class="form-control form-control-sm" value="${stop.localitate ?? ''}" required>
                        `;

                        const countryCol = document.createElement('div');
                        countryCol.className = 'col-12 col-sm-4';
                        countryCol.innerHTML = `
                            <label class="form-label small text-uppercase fw-semibold mb-1">Țară</label>
                            <input type="text" name="stops[${stop.formIndex}][tara]" class="form-control form-control-sm" value="${stop.tara ?? ''}">
                        `;

                        row.append(postalCol, cityCol, countryCol);
                        wrapper.append(hiddenType, hiddenPosition, row);

                        const actions = document.createElement('div');
                        actions.className = 'stop-item__actions mt-3';

                        const moveUp = document.createElement('button');
                        moveUp.type = 'button';
                        moveUp.className = 'btn btn-outline-secondary btn-sm';
                        moveUp.textContent = 'Sus';
                        moveUp.addEventListener('click', () => moveStop(index, index - 1));

                        const moveDown = document.createElement('button');
                        moveDown.type = 'button';
                        moveDown.className = 'btn btn-outline-secondary btn-sm';
                        moveDown.textContent = 'Jos';
                        moveDown.addEventListener('click', () => moveStop(index, index + 1));

                        const remove = document.createElement('button');
                        remove.type = 'button';
                        remove.className = 'btn btn-outline-danger btn-sm';
                        remove.textContent = 'Șterge';
                        remove.addEventListener('click', () => {
                            stops.splice(index, 1);
                            render();
                        });

                        actions.append(moveUp, moveDown, remove);
                        wrapper.appendChild(actions);
                        container.appendChild(wrapper);
                    });
                };

                const addStop = () => {
                    stops.push({
                        type,
                        cod_postal: '',
                        localitate: '',
                        tara: '',
                        position: stops.length + 1,
                        formIndex: stopIndex++,
                    });
                    render();
                };

                render();
                return { addStop };
            };

            form.querySelectorAll('[data-stop-manager]').forEach((container) => {
                const type = container.dataset.stopType || '';
                if (!type) return;
                stopManagers.set(type, buildStopManager(container));
            });

            form.querySelectorAll('[data-stop-add]').forEach((button) => {
                button.addEventListener('click', () => {
                    const type = button.dataset.stopTarget || '';
                    const manager = stopManagers.get(type);
                    if (manager && typeof manager.addStop === 'function') {
                        manager.addStop();
                    }
                });
            });
        });
    </script>
@endpush
