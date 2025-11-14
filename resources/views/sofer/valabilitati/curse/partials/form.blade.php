@php
    $cursa = $cursa ?? null;
    $action = $action ?? '';
    $method = strtoupper($method ?? 'POST');
    $submitLabel = $submitLabel ?? 'Salvează';
    $tari = $tari ?? collect();
    $requiresTime = (bool) ($requiresTime ?? false);
    $lockTime = (bool) ($lockTime ?? false);
    $romanianCountryIds = collect($romanianCountryIds ?? [])->map(fn ($id) => (int) $id)->all();
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

    $dateValue = old('data_cursa_date', optional(optional($cursa)->data_cursa)->format('Y-m-d'));
    $timeValue = old('data_cursa_time', optional(optional($cursa)->data_cursa)->format('H:i'));

    $defaultFinalReturn = old('final_return');
    if ($defaultFinalReturn === null) {
        $defaultFinalReturn = in_array($descarcareTaraId, $romanianCountryIds, true) ? 1 : 0;
    }
    $finalReturnValue = (int) $defaultFinalReturn;

    $showTime = $requiresTime || $lockTime || $finalReturnValue === 1;
    if (! $showTime && filled($timeValue)) {
        $showTime = true;
    }

    $requireTimeDefault = $requiresTime || $finalReturnValue === 1 || $lockTime;
@endphp

<form
    method="POST"
    action="{{ $action }}"
    class="cursa-form"
    data-cursa-form
    data-initial-time-visible="{{ $showTime ? 'true' : 'false' }}"
    data-lock-time-visible="{{ $lockTime ? 'true' : 'false' }}"
    data-require-time-default="{{ $requireTimeDefault ? 'true' : 'false' }}"
    data-initial-final-return="{{ $finalReturnValue }}"
    data-romanian-ids='@json(array_map("strval", $romanianCountryIds))'
    data-country-options='@json($countryOptions)'
>
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <input type="hidden" name="final_return" value="{{ $finalReturnValue }}" data-final-return-input>

    <div class="row g-3">
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
            <label class="form-label small text-uppercase fw-semibold">Data cursei</label>
            <input
                type="date"
                name="data_cursa_date"
                class="form-control form-control-sm @error('data_cursa_date') is-invalid @enderror"
                value="{{ $dateValue }}"
            >
            @error('data_cursa_date')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-12 col-md-6 cursa-time-field @if (! $showTime) d-none @endif" data-time-container>
            <label class="form-label small text-uppercase fw-semibold">Ora cursei</label>
            <input
                type="time"
                name="data_cursa_time"
                class="form-control form-control-sm @error('data_cursa_time') is-invalid @enderror"
                value="{{ $timeValue }}"
                data-time-input
            >
            @error('data_cursa_time')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            @if ($lockTime)
                <div class="form-text">Ora este necesară pentru prima cursă înregistrată.</div>
            @endif
        </div>
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
        .cursa-form .cursa-time-field label::after {
            content: '*';
            color: #dc3545;
            margin-left: 0.25rem;
            font-weight: 600;
            display: none;
        }

        .cursa-form .cursa-time-field.is-required label::after {
            display: inline;
        }

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

            const timeContainer = form.querySelector('[data-time-container]');
            const timeInput = timeContainer ? timeContainer.querySelector('[data-time-input]') : null;
            const finalReturnInput = form.querySelector('[data-final-return-input]');
            const descarcareField = form.querySelector('[data-country-field][data-country-role="descarcare"]');
            const descarcareHidden = descarcareField ? descarcareField.querySelector('[data-country-hidden]') : null;
            const descarcareText = descarcareField ? descarcareField.querySelector('[data-country-input]') : null;
            const confirmModalEl = document.getElementById('finalReturnConfirmModal');
            const confirmButton = confirmModalEl ? confirmModalEl.querySelector('[data-final-return-confirm]') : null;
            const cancelButton = confirmModalEl ? confirmModalEl.querySelector('[data-final-return-cancel]') : null;
            const bootstrapModal = window.bootstrap?.Modal ?? window.bootstrapModal ?? null;

            const initialTimeVisible = form.dataset.initialTimeVisible === 'true';
            const lockTimeVisible = form.dataset.lockTimeVisible === 'true';
            const requireTimeDefault = form.dataset.requireTimeDefault === 'true';
            const initialFinalReturn = form.dataset.initialFinalReturn === '1';
            const romanianIds = (() => {
                try {
                    return JSON.parse(form.dataset.romanianIds || '[]').map(String);
                } catch (error) {
                    return [];
                }
            })();

            let pendingModal = false;

            const toggleTimeField = (visible, requireTime = null) => {
                if (!timeContainer) {
                    return;
                }

                const shouldRequire = requireTime !== null ? requireTime : (lockTimeVisible || requireTimeDefault);

                if (visible) {
                    timeContainer.classList.remove('d-none');
                    if (shouldRequire) {
                        timeContainer.classList.add('is-required');
                    } else {
                        timeContainer.classList.remove('is-required');
                    }
                } else if (!lockTimeVisible) {
                    timeContainer.classList.add('d-none');
                    timeContainer.classList.remove('is-required');
                    if (timeInput) {
                        timeInput.value = '';
                    }
                }
            };

            const setFinalReturnFlag = (value) => {
                if (finalReturnInput) {
                    finalReturnInput.value = value ? '1' : '0';
                }
            };

            const isRomaniaSelected = () => {
                if (!descarcareHidden) {
                    return false;
                }

                const hiddenValue = (descarcareHidden.value || '').trim();
                if (hiddenValue && romanianIds.includes(hiddenValue)) {
                    return true;
                }

                const rawText = descarcareText ? (descarcareText.value || '') : '';
                const normalisedValue = rawText
                    ? (typeof rawText.normalize === 'function'
                        ? rawText
                            .normalize('NFD')
                            .replace(/[\u0300-\u036f]/g, '')
                        : rawText)
                    : '';

                return normalisedValue.trim().toLowerCase() === 'romania';
            };

            const showFinalModal = () => {
                if (!confirmModalEl) {
                    return;
                }

                if (bootstrapModal && typeof bootstrapModal.getOrCreateInstance === 'function') {
                    bootstrapModal.getOrCreateInstance(confirmModalEl).show();
                    return;
                }

                const $ = window.jQuery || window.$;
                if (typeof $ === 'function' && typeof $(confirmModalEl).modal === 'function') {
                    $(confirmModalEl).modal('show');
                    return;
                }

                confirmModalEl.classList.add('show');
                confirmModalEl.removeAttribute('aria-hidden');
            };

            const hideFinalModal = () => {
                if (!confirmModalEl) {
                    return;
                }

                if (bootstrapModal && typeof bootstrapModal.getInstance === 'function') {
                    const instance = bootstrapModal.getInstance(confirmModalEl);
                    if (instance) {
                        instance.hide();
                        return;
                    }
                }

                const $ = window.jQuery || window.$;
                if (typeof $ === 'function' && typeof $(confirmModalEl).modal === 'function') {
                    $(confirmModalEl).modal('hide');
                    return;
                }

                confirmModalEl.classList.remove('show');
                confirmModalEl.setAttribute('aria-hidden', 'true');
            };

            const resetAfterCancel = () => {
                setFinalReturnFlag(false);

                if (lockTimeVisible) {
                    toggleTimeField(true, true);
                    return;
                }

                const hasTimeValue = timeInput && (timeInput.value || '').trim() !== '';
                if (initialTimeVisible || hasTimeValue) {
                    toggleTimeField(true, hasTimeValue ? requireTimeDefault : initialFinalReturn);
                } else {
                    toggleTimeField(false, false);
                }
            };

            const handleDestinationChange = () => {
                if (!descarcareHidden) {
                    return;
                }

                if (isRomaniaSelected()) {
                    if (finalReturnInput && finalReturnInput.value === '1') {
                        toggleTimeField(true, true);
                        return;
                    }

                    pendingModal = true;
                    showFinalModal();
                    return;
                }

                pendingModal = false;
                setFinalReturnFlag(false);

                if (lockTimeVisible) {
                    toggleTimeField(true, true);
                    return;
                }

                const hasTimeValue = timeInput && (timeInput.value || '').trim() !== '';
                if (initialTimeVisible || hasTimeValue) {
                    toggleTimeField(true, hasTimeValue ? requireTimeDefault : initialFinalReturn);
                } else {
                    toggleTimeField(false, false);
                }
            };

            confirmButton?.addEventListener('click', () => {
                setFinalReturnFlag(true);
                toggleTimeField(true, true);
                hideFinalModal();
                pendingModal = false;
                if (timeInput) {
                    timeInput.focus();
                }
            });

            cancelButton?.addEventListener('click', () => {
                resetAfterCancel();
                hideFinalModal();
                pendingModal = false;
            });

            if (confirmModalEl) {
                confirmModalEl.addEventListener('hidden.bs.modal', () => {
                    if (pendingModal) {
                        resetAfterCancel();
                        pendingModal = false;
                    }
                });
            }

            if (descarcareHidden) {
                descarcareHidden.addEventListener('change', handleDestinationChange);
            }

            if (descarcareText) {
                descarcareText.addEventListener('change', handleDestinationChange);
                descarcareText.addEventListener('blur', handleDestinationChange);
            }

            if (initialTimeVisible || lockTimeVisible || (timeInput && (timeInput.value || '').trim() !== '')) {
                toggleTimeField(true, finalReturnInput && finalReturnInput.value === '1');
            }

            handleDestinationChange();
        });
    </script>
@endpush
