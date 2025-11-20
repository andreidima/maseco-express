<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ValabilitateAlimentareRequest extends FormRequest
{
    public function authorize(): bool
    {
        $valabilitate = $this->route('valabilitate');

        return $valabilitate && $this->user()?->can('update', $valabilitate);
    }

    public function rules(): array
    {
        return [
            'data_ora_alimentare' => ['required', 'date'],
            'litrii' => ['required', 'numeric', 'min:0'],
            'pret_pe_litru' => ['required', 'numeric', 'min:0'],
            'total_pret' => ['required', 'numeric', 'min:0'],
            'observatii' => ['nullable', 'string'],
        ];
    }
}
