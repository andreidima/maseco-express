<?php

namespace App\Http\Requests\FacturiTransportatori;

use Illuminate\Foundation\Http\FormRequest;

class AttachComenziRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'comenzi' => ['required', 'array', 'min:1'],
            'comenzi.*' => ['integer', 'exists:comenzi,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'comenzi.required' => 'Selectati cel putin o comanda pentru a continua.',
            'comenzi.array' => 'Formatul comenzilor trimise nu este valid.',
            'comenzi.min' => 'Selectati cel putin o comanda pentru a continua.',
            'comenzi.*.exists' => 'Cel putin una dintre comenzile selectate nu mai exista.',
        ];
    }

    public function attributes(): array
    {
        return [
            'comenzi' => 'comenzi selectate',
        ];
    }
}
