<?php

namespace App\Http\Requests\Service;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMasinaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $masinaId = (int) optional($this->route('masina'))->id;

        return [
            'denumire' => ['required', 'string', 'max:255'],
            'numar_inmatriculare' => [
                'required',
                'string',
                'max:255',
                Rule::unique('service_masini', 'numar_inmatriculare')->ignore($masinaId),
            ],
            'serie_sasiu' => ['nullable', 'string', 'max:255'],
            'observatii' => ['nullable', 'string'],
        ];
    }
}
