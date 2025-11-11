@php
    use App\Support\CountryList;

    $formId ??= 'valabilitate-cursa-' . uniqid();
    $countries = $countries ?? CountryList::options();
    $isFirstTrip = $isFirstTrip ?? false;
    $ultimaCursaDefault = old('ultima_cursa', $cursa->ultima_cursa ?? false);
@endphp

<div class="row g-3">
    <div class="col-md-6">
        <label for="{{ $formId }}-localitate-plecare" class="form-label">Localitate plecare</label>
        <input type="text" class="form-control" id="{{ $formId }}-localitate-plecare" name="localitate_plecare"
            value="{{ old('localitate_plecare', $cursa->localitate_plecare ?? '') }}" required maxlength="255"
            list="{{ $formId }}-plecare-suggestions" data-localitate-autocomplete
            data-autocomplete-target="{{ $formId }}-plecare-suggestions">
        <datalist id="{{ $formId }}-plecare-suggestions"></datalist>
    </div>

    <div class="col-md-6">
        <label for="{{ $formId }}-localitate-sosire" class="form-label">Localitate sosire</label>
        <input type="text" class="form-control" id="{{ $formId }}-localitate-sosire" name="localitate_sosire"
            value="{{ old('localitate_sosire', $cursa->localitate_sosire ?? '') }}" maxlength="255"
            list="{{ $formId }}-sosire-suggestions" data-localitate-autocomplete
            data-autocomplete-target="{{ $formId }}-sosire-suggestions">
        <datalist id="{{ $formId }}-sosire-suggestions"></datalist>
    </div>

    <div class="col-md-6 col-lg-4">
        <label for="{{ $formId }}-descarcare-tara" class="form-label">Țara descărcare</label>
        <select class="form-select" id="{{ $formId }}-descarcare-tara" name="descarcare_tara"
            data-descarcare-tara>
            <option value="">Alege țara</option>
            @foreach ($countries as $code => $label)
                <option value="{{ $code }}" @selected(old('descarcare_tara', $cursa->descarcare_tara ?? '') === $code)>
                    {{ $label }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-6 col-lg-4">
        <label for="{{ $formId }}-plecare" class="form-label">Plecare la</label>
        <input type="datetime-local" class="form-control" id="{{ $formId }}-plecare" name="plecare_la"
            value="{{ old('plecare_la', optional($cursa->plecare_la ?? null)->format('Y-m-d\TH:i')) }}">
    </div>

    <div class="col-md-6 col-lg-4">
        <label for="{{ $formId }}-sosire" class="form-label">Sosire la</label>
        <input type="datetime-local" class="form-control" id="{{ $formId }}-sosire" name="sosire_la"
            value="{{ old('sosire_la', optional($cursa->sosire_la ?? null)->format('Y-m-d\TH:i')) }}">
    </div>

    <div class="col-md-6 col-lg-4">
        <label for="{{ $formId }}-ora" class="form-label">Ora</label>
        <input type="time" class="form-control" id="{{ $formId }}-ora" name="ora"
            value="{{ old('ora', $cursa->ora ? substr($cursa->ora, 0, 5) : '') }}"
            @if ($isFirstTrip || $ultimaCursaDefault) required @endif
            data-ora-input data-first-trip="{{ $isFirstTrip ? '1' : '0' }}">
        <small class="text-muted" data-ora-hint>Ora este obligatorie pentru prima cursă și pentru ultima cursă.</small>
    </div>

    <div class="col-md-6 col-lg-4">
        <label for="{{ $formId }}-km" class="form-label">Kilometraj bord</label>
        <input type="number" class="form-control" id="{{ $formId }}-km" name="km_bord" min="0"
            value="{{ old('km_bord', $cursa->km_bord ?? '') }}">
    </div>

    <div class="col-md-6 col-lg-4 d-flex align-items-end">
        <div class="form-check form-switch">
            <input type="hidden" name="ultima_cursa" value="0">
            <input class="form-check-input" type="checkbox" role="switch"
                id="{{ $formId }}-ultima-cursa" name="ultima_cursa" value="1"
                @checked((bool) $ultimaCursaDefault) data-ultima-checkbox>
            <label class="form-check-label" for="{{ $formId }}-ultima-cursa">Ultima cursă</label>
        </div>
    </div>

    <div class="col-12">
        <label for="{{ $formId }}-observatii" class="form-label">Observații</label>
        <textarea class="form-control" id="{{ $formId }}-observatii" name="observatii" rows="3">{{ old('observatii', $cursa->observatii ?? '') }}</textarea>
    </div>
</div>

@once
    @push('scripts')
        <script>
            (function () {
                const attachAutocomplete = (input) => {
                    if (input.dataset.enhanced === '1') {
                        return;
                    }

                    input.dataset.enhanced = '1';

                    const datalistId = input.getAttribute('data-autocomplete-target');
                    const datalist = document.getElementById(datalistId);
                    if (!datalist) {
                        return;
                    }

                    let controller;

                    input.addEventListener('input', () => {
                        const query = input.value.trim();

                        if (query.length < 2) {
                            datalist.innerHTML = '';
                            if (controller) {
                                controller.abort();
                                controller = undefined;
                            }
                            return;
                        }

                        if (controller) {
                            controller.abort();
                        }

                        controller = new AbortController();

                        fetch(`/api/valabilitati/localitati?q=${encodeURIComponent(query)}`, {
                            signal: controller.signal,
                            credentials: 'same-origin',
                        })
                            .then((response) => {
                                if (!response.ok) {
                                    throw new Error('Request failed');
                                }

                                return response.json();
                            })
                            .then((items) => {
                                datalist.innerHTML = '';

                                items.forEach((item) => {
                                    const option = document.createElement('option');
                                    option.value = item;
                                    datalist.appendChild(option);
                                });
                            })
                            .catch(() => {
                                datalist.innerHTML = '';
                            });
                    });
                };

                const updateOraRequirement = (form) => {
                    const oraInput = form.querySelector('[data-ora-input]');
                    if (!oraInput) {
                        return;
                    }

                    const ultimaCheckbox = form.querySelector('[data-ultima-checkbox]');
                    const firstTrip = oraInput.dataset.firstTrip === '1';
                    const requireOra = (ultimaCheckbox && ultimaCheckbox.checked) || firstTrip;

                    oraInput.required = requireOra;
                };

                const enhanceForm = (form) => {
                    if (!form || form.dataset.valabilitateEnhanced === '1') {
                        return;
                    }

                    form.dataset.valabilitateEnhanced = '1';

                    form.querySelectorAll('[data-localitate-autocomplete]').forEach(attachAutocomplete);

                    const ultimaCheckbox = form.querySelector('[data-ultima-checkbox]');
                    const oraInput = form.querySelector('[data-ora-input]');
                    const taraSelect = form.querySelector('[data-descarcare-tara]');

                    if (ultimaCheckbox && oraInput) {
                        ultimaCheckbox.addEventListener('change', () => {
                            updateOraRequirement(form);
                        });
                    }

                    if (taraSelect && ultimaCheckbox) {
                        taraSelect.addEventListener('change', () => {
                            if (taraSelect.value === 'RO') {
                                const confirmed = window.confirm('Cursa se încheie în România și va fi marcată ca ultima cursă?');
                                ultimaCheckbox.checked = confirmed;
                                ultimaCheckbox.dispatchEvent(new Event('change', { bubbles: true }));
                            }
                        });
                    }

                    updateOraRequirement(form);
                };

                document.querySelectorAll('form').forEach(enhanceForm);

                document.addEventListener('DOMContentLoaded', () => {
                    document.querySelectorAll('form').forEach(enhanceForm);
                });

                const observer = new MutationObserver((mutations) => {
                    for (const mutation of mutations) {
                        mutation.addedNodes.forEach((node) => {
                            if (!(node instanceof HTMLElement)) {
                                return;
                            }

                            if (node.tagName === 'FORM') {
                                enhanceForm(node);
                            } else {
                                node.querySelectorAll?.('form').forEach(enhanceForm);
                            }
                        });
                    }
                });

                observer.observe(document.body, { childList: true, subtree: true });
            })();
        </script>
    @endpush
@endonce
