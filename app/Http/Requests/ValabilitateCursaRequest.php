<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ValabilitateCursaRequest extends FormRequest
{
    public function authorize(): bool
    {
        $valabilitate = $this->route('valabilitate');

        return $valabilitate && $this->user()?->can('update', $valabilitate);
    }

    public function rules(): array
    {
        return [
            'incarcare_localitate' => ['nullable', 'string', 'max:255'],
            'incarcare_cod_postal' => ['nullable', 'string', 'max:255'],
            'descarcare_localitate' => ['nullable', 'string', 'max:255'],
            'descarcare_cod_postal' => ['nullable', 'string', 'max:255'],
            'data_cursa' => ['nullable', 'date'],
            'observatii' => ['nullable', 'string'],
        ];
    }
}
