@php
    $cursa = $cursa ?? null;
    $action = $action ?? '';
    $method = strtoupper($method ?? 'POST');
    $submitLabel = $submitLabel ?? 'Salvează';
    $tari = $tari ?? collect();
    $isFlashDivizie = (bool) ($isFlashDivizie ?? false);
    $countryOptions = $tari->map(fn ($tara) => [
        'id' => (string) $tara->id,
        'name' => $tara->nume,
    ])->values();

    $incarcareLocalitate = old('incarcare_localitate', optional($cursa)->incarcare_localitate);
    $incarcareCodPostal = old('incarcare_cod_postal', optional($cursa)->incarcare_cod_postal);
    $incarcareTaraId = (int) old('incarcare_tara_id', optional($cursa)->incarcare_tara_id);
    $incarcareTaraText = old('incarcare_tara_text', optional(optional($cursa)->incarcareTara)->nume);

    $descarcareLocalitate = old('descarcare_localitate', optional($cursa)->descarcare_localitate);
    $descarcareCodPostal = old('descarcare_cod_postal', optional($cursa)->descarcare_cod_postal);
    $descarcareTaraId = (int) old('descarcare_tara_id', optional($cursa)->descarcare_tara_id);
    $descarcareTaraText = old('descarcare_tara_text', optional(optional($cursa)->descarcareTara)->nume);

    $dataCursaValue = old('data_cursa', optional(optional($cursa)->data_cursa)->format('Y-m-d\TH:i'));
    $nrCursa = old('nr_cursa', optional($cursa)->nr_cursa);
@endphp

<form
    method="POST"
    action="{{ $action }}"
    class="cursa-form"
    data-cursa-form
    data-country-options='@json($countryOptions)'
>
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="row g-3">
        <div class="col-12 col-md-6">
            <label class="form-label small text-uppercase fw-semibold">Număr cursă</label>
            <input
                type="text"
                name="nr_cursa"
                class="form-control form-control-sm @error('nr_cursa') is-invalid @enderror"
                value="{{ $nrCursa }}"
                autocomplete="off"
            >
            @error('nr_cursa')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-12 col-md-6">
            <label class="form-label small text-uppercase fw-semibold">Localitate încărcare</label>
            <input
                type="text"
                name="incarcare_localitate"
                class="form-control form-control-sm @error('incarcare_localitate') is-invalid @enderror"
                value="{{ $incarcareLocalitate }}"
                autocomplete="off"
            >
            @error('incarcare_localitate')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-12 col-md-6">
            <label class="form-label small text-uppercase fw-semibold">Cod poștal încărcare</label>
            <input
                type="text"
                name="incarcare_cod_postal"
                class="form-control form-control-sm @error('incarcare_cod_postal') is-invalid @enderror"
                value="{{ $incarcareCodPostal }}"
                autocomplete="off"
            >
            @error('incarcare_cod_postal')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-12 col-md-6" data-country-field data-country-role="incarcare">
            <label class="form-label small text-uppercase fw-semibold">Țara încărcării</label>
            <input type="hidden" name="incarcare_tara_id" value="{{ $incarcareTaraId ?: '' }}" data-country-hidden>
            <div class="country-autocomplete position-relative" data-country-autocomplete>
                <input
                    type="text"
                    name="incarcare_tara_text"
                    class="form-control form-control-sm @error('incarcare_tara_id') is-invalid @enderror"
                    value="{{ $incarcareTaraText }}"
                    autocomplete="off"
                    data-country-input
                >
                <div class="dropdown-menu w-100" data-country-dropdown></div>
            </div>
            @error('incarcare_tara_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-12 col-md-6">
            <label class="form-label small text-uppercase fw-semibold">Localitate descărcare</label>
            <input
                type="text"
                name="descarcare_localitate"
                class="form-control form-control-sm @error('descarcare_localitate') is-invalid @enderror"
                value="{{ $descarcareLocalitate }}"
                autocomplete="off"
            >
            @error('descarcare_localitate')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-12 col-md-6">
            <label class="form-label small text-uppercase fw-semibold">Cod poștal descărcare</label>
            <input
                type="text"
                name="descarcare_cod_postal"
                class="form-control form-control-sm @error('descarcare_cod_postal') is-invalid @enderror"
                value="{{ $descarcareCodPostal }}"
                autocomplete="off"
            >
            @error('descarcare_cod_postal')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-12 col-md-6" data-country-field data-country-role="descarcare">
            <label class="form-label small text-uppercase fw-semibold">Țara descărcării</label>
            <input type="hidden" name="descarcare_tara_id" value="{{ $descarcareTaraId ?: '' }}" data-country-hidden>
            <div class="country-autocomplete position-relative" data-country-autocomplete>
                <input
                    type="text"
                    name="descarcare_tara_text"
                    class="form-control form-control-sm @error('descarcare_tara_id') is-invalid @enderror"
                    value="{{ $descarcareTaraText }}"
                    autocomplete="off"
                    data-country-input
                >
                <div class="dropdown-menu w-100" data-country-dropdown></div>
            </div>
            @error('descarcare_tara_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-12 col-md-6">
            <label class="form-label small text-uppercase fw-semibold">Ora cursei</label>
            <input
                type="datetime-local"
                name="data_cursa"
                class="form-control form-control-sm @error('data_cursa') is-invalid @enderror"
                value="{{ $dataCursaValue }}"
                step="60"
            >
            @error('data_cursa')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        @unless ($isFlashDivizie)
        <div class="col-12 col-md-6">
            <label class="form-label small text-uppercase fw-semibold">Km bord încărcare</label>
            <input
                type="number"
                name="km_bord_incarcare"
                class="form-control form-control-sm @error('km_bord_incarcare') is-invalid @enderror"
                value="{{ old('km_bord_incarcare', optional($cursa)->km_bord_incarcare) }}"
                min="0"
                step="1"
            >
            @error('km_bord_incarcare')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-12 col-md-6">
            <label class="form-label small text-uppercase fw-semibold">Km bord descărcare</label>
            <input
                type="number"
                name="km_bord_descarcare"
                class="form-control form-control-sm @error('km_bord_descarcare') is-invalid @enderror"
                value="{{ old('km_bord_descarcare', optional($cursa)->km_bord_descarcare) }}"
                min="0"
                step="1"
            >
            @error('km_bord_descarcare')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        @endunless
        <div class="col-12">
            <label class="form-label small text-uppercase fw-semibold">Observații</label>
            <textarea
                name="observatii"
                class="form-control form-control-sm @error('observatii') is-invalid @enderror"
                rows="3"
            >{{ old('observatii', optional($cursa)->observatii) }}</textarea>
            @error('observatii')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="d-flex flex-column flex-sm-row gap-2 justify-content-between mt-4">
        <a href="{{ route('sofer.valabilitati.show', $valabilitate) }}" class="btn btn-outline-secondary">Înapoi la curse</a>
        <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
    </div>
</form>

@push('page-styles')
    <style>
        .country-autocomplete {
            position: relative;
        }

        .country-autocomplete .dropdown-menu {
            max-height: 16rem;
            overflow-y: auto;
            width: 100%;
            inset: 100% 0 auto 0 !important;
            transform: none !important;
        }

        .country-autocomplete .dropdown-item {
            white-space: normal;
        }
    </style>
@endpush

@push('page-scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.querySelector('[data-cursa-form]');
            if (!form) {
                return;
            }

            const countryOptions = (() => {
                try {
                    return JSON.parse(form.dataset.countryOptions || '[]');
                } catch (error) {
                    return [];
                }
            })();
            const mapByName = new Map();
            const mapById = new Map();

            const normaliseString = (value) => {
                const raw = String(value ?? '').trim();
                if (!raw) {
                    return '';
                }

                const normalised = typeof raw.normalize === 'function'
                    ? raw.normalize('NFD').replace(/[\u0300-\u036f]/g, '')
                    : raw;

                return normalised.toLowerCase();
            };

            countryOptions.forEach((option) => {
                const name = (option.name || '').trim();
                const id = String(option.id || '').trim();

                if (name) {
                    const normalised = normaliseString(name);
                    const lowercase = name.toLowerCase();

                    if (normalised) {
                        mapByName.set(normalised, id);
                    }

                    if (lowercase && !mapByName.has(lowercase)) {
                        mapByName.set(lowercase, id);
                    }
                }

                if (id) {
                    mapById.set(id, name);
                }
            });

            const syncHiddenToText = (textInput, hiddenInput) => {
                const hiddenValue = (hiddenInput.value || '').trim();

                if (!hiddenValue) {
                    return;
                }

                const resolved = mapById.get(hiddenValue);
                if (resolved && (textInput.value || '').trim() === '') {
                    textInput.value = resolved;
                }
            };

            const syncTextToHidden = (textInput, hiddenInput) => {
                const normalisedValue = normaliseString(textInput.value || '');
                let matchedId = normalisedValue ? (mapByName.get(normalisedValue) || '') : '';

                if (!matchedId) {
                    const fallback = (textInput.value || '').trim().toLowerCase();
                    matchedId = mapByName.get(fallback) || '';
                }
                const previousValue = hiddenInput.value;

                hiddenInput.value = matchedId;

                if (previousValue !== matchedId) {
                    hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));
                }
            };

            const bindCountryField = (field) => {
                if (!field) {
                    return;
                }

                const textInput = field.querySelector('[data-country-input]');
                const hiddenInput = field.querySelector('[data-country-hidden]');
                const autocomplete = field.querySelector('[data-country-autocomplete]');
                const dropdown = autocomplete ? autocomplete.querySelector('[data-country-dropdown]') : null;

                if (!textInput || !hiddenInput || !dropdown) {
                    return;
                }

                const maxSuggestions = 12;
                let currentSuggestions = [];
                let activeIndex = -1;
                let dropdownVisible = false;
                let suppressNextRender = false;

                const closeDropdown = () => {
                    dropdown.classList.remove('show');
                    dropdownVisible = false;
                    activeIndex = -1;
                    dropdown.querySelectorAll('.dropdown-item').forEach((item) => item.classList.remove('active'));
                };

                const openDropdown = () => {
                    if (currentSuggestions.length === 0) {
                        closeDropdown();
                        return;
                    }

                    dropdown.classList.add('show');
                    dropdownVisible = true;
                };

                const updateActiveItem = () => {
                    dropdown.querySelectorAll('.dropdown-item').forEach((item, index) => {
                        if (index === activeIndex) {
                            item.classList.add('active');
                            item.scrollIntoView({ block: 'nearest' });
                        } else {
                            item.classList.remove('active');
                        }
                    });
                };

                const renderSuggestions = (query) => {
                    const value = normaliseString(query || '');
                    const suggestions = countryOptions
                        .filter((option) => {
                            if (!option || !option.name) {
                                return false;
                            }

                            if (!value) {
                                return true;
                            }

                            return normaliseString(option.name).includes(value);
                        })
                        .slice(0, maxSuggestions);

                    currentSuggestions = suggestions;
                    dropdown.innerHTML = '';
                    activeIndex = -1;

                    suggestions.forEach((option, index) => {
                        const item = document.createElement('button');
                        item.type = 'button';
                        item.className = 'dropdown-item text-start';
                        item.textContent = option.name;
                        item.dataset.index = String(index);
                        dropdown.appendChild(item);
                    });

                    if (suggestions.length === 0 || document.activeElement !== textInput) {
                        closeDropdown();
                        return;
                    }

                    openDropdown();
                };

                const selectOption = (option) => {
                    if (!option) {
                        return;
                    }

                    textInput.value = option.name || '';
                    if (mapByName.size > 0) {
                        syncTextToHidden(textInput, hiddenInput);
                    }
                    suppressNextRender = true;
                    textInput.dispatchEvent(new Event('input', { bubbles: true }));
                    textInput.dispatchEvent(new Event('change', { bubbles: true }));
                    window.setTimeout(() => {
                        suppressNextRender = false;
                    }, 0);
                    closeDropdown();
                };

                syncHiddenToText(textInput, hiddenInput);
                if (mapByName.size > 0) {
                    syncTextToHidden(textInput, hiddenInput);
                }

                textInput.addEventListener('input', () => {
                    if (mapByName.size > 0) {
                        syncTextToHidden(textInput, hiddenInput);
                    }

                    if (!suppressNextRender && countryOptions.length > 0) {
                        renderSuggestions(textInput.value || '');
                    }
                });

                textInput.addEventListener('focus', () => {
                    if (countryOptions.length > 0) {
                        renderSuggestions(textInput.value || '');
                    }
                });

                textInput.addEventListener('change', () => {
                    if (suppressNextRender) {
                        return;
                    }

                    if (mapByName.size > 0) {
                        syncTextToHidden(textInput, hiddenInput);
                    }
                });

                textInput.addEventListener('keydown', (event) => {
                    if (!dropdownVisible || currentSuggestions.length === 0) {
                        return;
                    }

                    if (event.key === 'ArrowDown') {
                        event.preventDefault();
                        activeIndex = (activeIndex + 1) % currentSuggestions.length;
                        updateActiveItem();
                    } else if (event.key === 'ArrowUp') {
                        event.preventDefault();
                        activeIndex = activeIndex <= 0
                            ? currentSuggestions.length - 1
                            : activeIndex - 1;
                        updateActiveItem();
                    } else if (event.key === 'Enter') {
                        if (activeIndex >= 0 && activeIndex < currentSuggestions.length) {
                            event.preventDefault();
                            selectOption(currentSuggestions[activeIndex]);
                        }
                    } else if (event.key === 'Escape') {
                        closeDropdown();
                    }
                });

                dropdown.addEventListener('mousedown', (event) => {
                    event.preventDefault();
                });

                dropdown.addEventListener('click', (event) => {
                    const target = event.target;
                    if (!(target instanceof HTMLElement)) {
                        return;
                    }

                    const index = Number.parseInt(target.dataset.index || '', 10);
                    if (!Number.isNaN(index) && currentSuggestions[index]) {
                        selectOption(currentSuggestions[index]);
                    }
                });

                textInput.addEventListener('blur', () => {
                    if ((textInput.value || '').trim() === '') {
                        hiddenInput.value = '';
                        hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));
                    }

                    window.setTimeout(() => {
                        closeDropdown();
                    }, 150);
                });

                document.addEventListener('click', (event) => {
                    const target = event.target;
                    if (target instanceof Node && !field.contains(target)) {
                        closeDropdown();
                    }
                });
            };

            form.querySelectorAll('[data-country-field]').forEach((field) => bindCountryField(field));
        });
    </script>
@endpush
