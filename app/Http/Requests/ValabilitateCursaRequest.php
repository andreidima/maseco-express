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
            'localitate_plecare' => ['required', 'string', 'max:255'],
            'localitate_sosire' => ['nullable', 'string', 'max:255'],
            'plecare_la' => ['nullable', 'date'],
            'sosire_la' => ['nullable', 'date', 'after_or_equal:plecare_la'],
            'km_bord' => ['nullable', 'integer', 'min:0'],
            'observatii' => ['nullable', 'string'],
        ];
    }
}
