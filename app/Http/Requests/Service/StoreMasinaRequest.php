<?php

namespace App\Http\Requests\Service;

use Illuminate\Foundation\Http\FormRequest;

class StoreMasinaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'denumire' => ['required', 'string', 'max:255'],
            'numar_inmatriculare' => ['required', 'string', 'max:255', 'unique:service_masini,numar_inmatriculare'],
            'serie_sasiu' => ['nullable', 'string', 'max:255'],
            'observatii' => ['nullable', 'string'],
        ];
    }
}
