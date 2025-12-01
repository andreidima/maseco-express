<?php

namespace App\Http\Requests;

use App\Models\Valabilitate;
use App\Support\Valabilitati\ValabilitatiCurseFilterState;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Arr;

class ValabilitateCursaRequest extends FormRequest
{
    public function authorize(): bool
    {
        $valabilitate = $this->route('valabilitate');

        return $valabilitate && $this->user()?->can('update', $valabilitate);
    }

    public function rules(): array
    {
        $valabilitate = $this->route('valabilitate');
        $isFlashDivizie = $this->isFlashDivizie($valabilitate);

        $rules = [
            'nr_ordine' => ['sometimes', 'integer', 'min:1'],
            'nr_cursa' => ['nullable', 'string', 'max:255'],
            'incarcare_localitate' => ['nullable', 'string', 'max:255'],
            'incarcare_cod_postal' => ['nullable', 'string', 'max:255'],
            'incarcare_tara_id' => ['nullable', 'exists:tari,id'],
            'descarcare_localitate' => ['nullable', 'string', 'max:255'],
            'descarcare_cod_postal' => ['nullable', 'string', 'max:255'],
            'descarcare_tara_id' => ['nullable', 'exists:tari,id'],
            'data_cursa' => ['nullable', 'date'],
            'observatii' => ['nullable', 'string'],
            'km_bord_incarcare' => ['nullable', 'integer', 'min:0'],
            'km_bord_descarcare' => ['nullable', 'integer', 'min:0'],
            'km_maps_gol' => ['nullable', 'numeric', 'min:0'],
            'km_maps_plin' => ['nullable', 'numeric', 'min:0'],
            'km_cu_taxa' => ['nullable', 'numeric', 'min:0'],
            'km_flash_gol' => ['nullable', 'numeric', 'min:0'],
            'km_flash_plin' => ['nullable', 'numeric', 'min:0'],
            'alte_taxe' => ['nullable', 'numeric'],
            'fuel_tax' => ['nullable', 'numeric'],
            'suma_incasata' => ['nullable', 'numeric'],
            'daily_contribution_incasata' => ['nullable', 'numeric'],
            'cursa_grup_id' => [
                'nullable',
                Rule::exists('valabilitati_cursa_grupuri', 'id')->where(function ($query) use ($valabilitate) {
                    if ($valabilitate instanceof Valabilitate) {
                        $query->where('valabilitate_id', $valabilitate->getKey());
                    }
                }),
            ],
        ];

        if ($isFlashDivizie) {
            $rules = array_merge($rules, [
                'stops' => ['sometimes', 'array'],
                'stops.*.type' => ['required_with:stops', 'in:incarcare,descarcare'],
                'stops.*.cod_postal' => ['nullable', 'string', 'max:255'],
                'stops.*.localitate' => ['required_with:stops', 'string', 'max:255'],
                'stops.*.tara' => ['nullable', 'string', 'max:255'],
                'stops.*.position' => ['nullable', 'integer', 'min:1'],
            ]);
        }

        return $rules;
    }

    protected function prepareForValidation(): void
    {
        $valabilitate = $this->route('valabilitate');
        if (! $this->isFlashDivizie($valabilitate)) {
            $this->replace(Arr::except($this->all(), ['stops']));
        }

        $dateInput = $this->input('data_cursa_date');
        $timeInput = $this->input('data_cursa_time');

        if ($dateInput === null && $timeInput === null) {
            return;
        }

        $date = trim((string) $dateInput);
        $time = trim((string) $timeInput);

        if ($date === '') {
            $this->merge(['data_cursa' => null]);

            return;
        }

        if ($time === '') {
            $time = '00:00';
        }

        $this->merge([
            'data_cursa' => sprintf('%s %s', $date, $time),
        ]);
    }

    protected function failedValidation(Validator $validator): void
    {
        if ($this->expectsJson()) {
            parent::failedValidation($validator);

            return;
        }

        $valabilitate = $this->route('valabilitate');
        $modalKey = $this->determineModalKey();

        $response = redirect($this->resolveRedirectUrl($valabilitate))
            ->withErrors($validator)
            ->withInput($this->all())
            ->with('curse.modal', $modalKey);

        throw new ValidationException($validator, $response);
    }

    private function determineModalKey(): string
    {
        $formType = (string) $this->input('form_type', '');

        if (in_array($formType, ['edit', 'stops'], true)) {
            $formId = (int) $this->input('form_id');

            if ($formId > 0) {
                return $formType . ':' . $formId;
            }

            return $formType;
        }

        return 'create';
    }

    private function resolveRedirectUrl(?Valabilitate $valabilitate): string
    {
        if ($valabilitate instanceof Valabilitate) {
            return ValabilitatiCurseFilterState::route($valabilitate);
        }

        return url()->previous();
    }

    private function isFlashDivizie(?Valabilitate $valabilitate): bool
    {
        if (! $valabilitate instanceof Valabilitate) {
            return false;
        }

        return (int) $valabilitate->divizie_id === 1;
    }
}
