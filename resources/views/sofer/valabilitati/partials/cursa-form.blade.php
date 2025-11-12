@php
    $formType = $formType ?? 'create';
    $formId = $formId ?? null;
    $modalId = $modalId ?? 'cursaCreateModal';
    $modalTitle = $modalTitle ?? 'Cursă';
    $submitLabel = $submitLabel ?? 'Salvează';
    $cursa = $cursa ?? null;
    $tari = $tari ?? collect();
    $requiresTimeByDefault = $requiresTimeByDefault ?? false;
    $lockTime = $lockTime ?? false;

    $modalKey = $formType === 'edit' ? 'edit:' . $formId : 'create';
    $oldMatches = old('form_type') === $formType && ($formType === 'create' || (int) old('form_id') === (int) $formId);

    $value = static function (string $field, $default, bool $useOld) {
        return $useOld ? old($field, $default) : $default;
    };

    $dateDefault = optional(optional($cursa)->data_cursa)->format('Y-m-d');
    $timeDefault = optional(optional($cursa)->data_cursa)->format('H:i');

    $dateValue = $value('data_cursa_date', $dateDefault, $oldMatches);
    $timeValue = $value('data_cursa_time', $timeDefault, $oldMatches);

    $finalReturnValue = (int) $value('final_return', 0, $oldMatches);

    $showTime = $requiresTimeByDefault || $lockTime;

    if ($oldMatches) {
        $showTime = (bool) old('final_return', $showTime);
        if (! $showTime && filled($timeValue)) {
            $showTime = true;
        }
    } elseif (! $showTime && filled($timeValue)) {
        $showTime = true;
    }

    $showErrors = $oldMatches;
    $requireTimeDefault = $requiresTimeByDefault || $finalReturnValue === 1;
@endphp

<div
    class="modal fade"
    id="{{ $modalId }}"
    tabindex="-1"
    aria-hidden="true"
    data-modal-key="{{ $modalKey }}"
>
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form
            method="POST"
            action="{{ $formAction }}"
            class="modal-content border-0 shadow-sm"
            data-cursa-form
            data-initial-time-visible="{{ $showTime ? 'true' : 'false' }}"
            data-lock-time-visible="{{ $lockTime ? 'true' : 'false' }}"
            data-require-time-default="{{ $requireTimeDefault ? 'true' : 'false' }}"
            data-initial-final-return="{{ $finalReturnValue }}"
        >
            @csrf
            @if ($method !== 'POST')
                @method($method)
            @endif

            <input type="hidden" name="form_type" value="{{ $formType }}">
            @if ($formType === 'edit')
                <input type="hidden" name="form_id" value="{{ $formId }}">
            @endif
            <input type="hidden" name="final_return" value="{{ $finalReturnValue }}" data-final-return-input>

            <div class="modal-header border-0 pb-0">
                <h2 class="h5 modal-title fw-bold">{{ $modalTitle }}</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Închide"></button>
            </div>

            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label small text-uppercase fw-semibold">Localitate încărcare</label>
                        <input
                            type="text"
                            name="incarcare_localitate"
                            class="form-control form-control-sm @if ($showErrors && $errors->has('incarcare_localitate')) is-invalid @endif"
                            value="{{ $value('incarcare_localitate', optional($cursa)->incarcare_localitate, $oldMatches) }}"
                            autocomplete="off"
                        >
                        @if ($showErrors && $errors->has('incarcare_localitate'))
                            <div class="invalid-feedback">{{ $errors->first('incarcare_localitate') }}</div>
                        @endif
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label small text-uppercase fw-semibold">Cod poștal încărcare</label>
                        <input
                            type="text"
                            name="incarcare_cod_postal"
                            class="form-control form-control-sm @if ($showErrors && $errors->has('incarcare_cod_postal')) is-invalid @endif"
                            value="{{ $value('incarcare_cod_postal', optional($cursa)->incarcare_cod_postal, $oldMatches) }}"
                            autocomplete="off"
                        >
                        @if ($showErrors && $errors->has('incarcare_cod_postal'))
                            <div class="invalid-feedback">{{ $errors->first('incarcare_cod_postal') }}</div>
                        @endif
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label small text-uppercase fw-semibold">Țara încărcării</label>
                        <select
                            name="incarcare_tara_id"
                            class="form-select form-select-sm @if ($showErrors && $errors->has('incarcare_tara_id')) is-invalid @endif"
                        >
                            <option value="">Selectați țara</option>
                            @foreach ($tari as $tara)
                                <option
                                    value="{{ $tara->id }}"
                                    @selected((int) $value('incarcare_tara_id', optional($cursa)->incarcare_tara_id, $oldMatches) === (int) $tara->id)
                                >
                                    {{ $tara->nume }}
                                </option>
                            @endforeach
                        </select>
                        @if ($showErrors && $errors->has('incarcare_tara_id'))
                            <div class="invalid-feedback">{{ $errors->first('incarcare_tara_id') }}</div>
                        @endif
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label small text-uppercase fw-semibold">Localitate descărcare</label>
                        <input
                            type="text"
                            name="descarcare_localitate"
                            class="form-control form-control-sm @if ($showErrors && $errors->has('descarcare_localitate')) is-invalid @endif"
                            value="{{ $value('descarcare_localitate', optional($cursa)->descarcare_localitate, $oldMatches) }}"
                            autocomplete="off"
                        >
                        @if ($showErrors && $errors->has('descarcare_localitate'))
                            <div class="invalid-feedback">{{ $errors->first('descarcare_localitate') }}</div>
                        @endif
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label small text-uppercase fw-semibold">Cod poștal descărcare</label>
                        <input
                            type="text"
                            name="descarcare_cod_postal"
                            class="form-control form-control-sm @if ($showErrors && $errors->has('descarcare_cod_postal')) is-invalid @endif"
                            value="{{ $value('descarcare_cod_postal', optional($cursa)->descarcare_cod_postal, $oldMatches) }}"
                            autocomplete="off"
                        >
                        @if ($showErrors && $errors->has('descarcare_cod_postal'))
                            <div class="invalid-feedback">{{ $errors->first('descarcare_cod_postal') }}</div>
                        @endif
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label small text-uppercase fw-semibold">Țara descărcării</label>
                        <select
                            name="descarcare_tara_id"
                            class="form-select form-select-sm @if ($showErrors && $errors->has('descarcare_tara_id')) is-invalid @endif"
                            data-descarcare-select
                        >
                            <option value="">Selectați țara</option>
                            @foreach ($tari as $tara)
                                <option
                                    value="{{ $tara->id }}"
                                    @selected((int) $value('descarcare_tara_id', optional($cursa)->descarcare_tara_id, $oldMatches) === (int) $tara->id)
                                >
                                    {{ $tara->nume }}
                                </option>
                            @endforeach
                        </select>
                        @if ($showErrors && $errors->has('descarcare_tara_id'))
                            <div class="invalid-feedback">{{ $errors->first('descarcare_tara_id') }}</div>
                        @endif
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label small text-uppercase fw-semibold">Data cursei</label>
                        <input
                            type="date"
                            name="data_cursa_date"
                            class="form-control form-control-sm @if ($showErrors && $errors->has('data_cursa_date')) is-invalid @endif"
                            value="{{ $dateValue }}"
                        >
                        @if ($showErrors && $errors->has('data_cursa_date'))
                            <div class="invalid-feedback">{{ $errors->first('data_cursa_date') }}</div>
                        @endif
                    </div>
                    <div class="col-12 col-md-6 cursa-time-field @if (! $showTime) d-none @endif" data-time-container>
                        <label class="form-label small text-uppercase fw-semibold">Ora cursei</label>
                        <input
                            type="time"
                            name="data_cursa_time"
                            class="form-control form-control-sm @if ($showErrors && $errors->has('data_cursa_time')) is-invalid @endif"
                            value="{{ $timeValue }}"
                            data-time-input
                        >
                        @if ($showErrors && $errors->has('data_cursa_time'))
                            <div class="invalid-feedback">{{ $errors->first('data_cursa_time') }}</div>
                        @endif
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label small text-uppercase fw-semibold">Kilometraj bord</label>
                        <input
                            type="number"
                            name="km_bord"
                            class="form-control form-control-sm @if ($showErrors && $errors->has('km_bord')) is-invalid @endif"
                            value="{{ $value('km_bord', optional($cursa)->km_bord, $oldMatches) }}"
                            min="0"
                            step="1"
                        >
                        @if ($showErrors && $errors->has('km_bord'))
                            <div class="invalid-feedback">{{ $errors->first('km_bord') }}</div>
                        @endif
                    </div>
                    <div class="col-12">
                        <label class="form-label small text-uppercase fw-semibold">Observații</label>
                        <textarea
                            name="observatii"
                            class="form-control form-control-sm @if ($showErrors && $errors->has('observatii')) is-invalid @endif"
                            rows="3"
                        >{{ $value('observatii', optional($cursa)->observatii, $oldMatches) }}</textarea>
                        @if ($showErrors && $errors->has('observatii'))
                            <div class="invalid-feedback">{{ $errors->first('observatii') }}</div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Renunță</button>
                <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
            </div>
        </form>
    </div>
</div>
