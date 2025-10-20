<?php

namespace App\Http\Requests\Service;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class GestiunePiesaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'factura_id' => ['nullable', 'integer', 'exists:service_ff_facturi,id'],
            'denumire' => ['required', 'string', 'max:255'],
            'cod' => ['nullable', 'string', 'max:255'],
            'cantitate_initiala' => ['nullable', 'numeric', 'min:0'],
            'nr_bucati' => ['nullable', 'numeric', 'min:0'],
            'pret' => ['nullable', 'numeric', 'min:0'],
            'tva_cota' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'pret_brut' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $numericFields = [
            'cantitate_initiala',
            'nr_bucati',
            'pret',
            'tva_cota',
            'pret_brut',
        ];

        $normalized = [];

        foreach ($numericFields as $field) {
            $value = $this->input($field);

            if (is_string($value)) {
                $value = str_replace([' ', ','], ['', '.'], $value);
            }

            if ($value === '') {
                $value = null;
            }

            if ($value !== null) {
                $normalized[$field] = is_numeric($value) ? (float) $value : $value;
            }
        }

        if (! empty($normalized)) {
            $this->merge($normalized);
        }
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $initial = $this->input('cantitate_initiala');
            $remaining = $this->input('nr_bucati');

            if ($initial !== null && $remaining !== null && (float) $remaining > (float) $initial) {
                $validator->errors()->add('nr_bucati', 'Stocul disponibil nu poate depăși cantitatea inițială.');
            }
        });
    }

    public function validated($key = null, $default = null): array
    {
        $data = parent::validated($key, $default);

        $initial = $data['cantitate_initiala'] ?? null;
        $remaining = $data['nr_bucati'] ?? null;

        if ($initial === null && $remaining !== null) {
            $data['cantitate_initiala'] = $remaining;
        } elseif ($initial !== null && $remaining === null) {
            $data['nr_bucati'] = $initial;
        }

        return $data;
    }
}
