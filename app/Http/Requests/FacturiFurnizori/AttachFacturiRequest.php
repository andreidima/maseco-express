<?php

namespace App\Http\Requests\FacturiFurnizori;

use Illuminate\Foundation\Http\FormRequest;

class AttachFacturiRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'facturi' => ['required', 'array', 'min:1'],
            'facturi.*' => ['integer', 'exists:ff_facturi,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'facturi.required' => 'Selectati cel putin o factura pentru a continua.',
            'facturi.array' => 'Formatul facturilor trimise nu este valid.',
            'facturi.min' => 'Selectati cel putin o factura pentru a continua.',
            'facturi.*.exists' => 'Cel putin una dintre facturile selectate nu mai exista.',
        ];
    }

    public function attributes(): array
    {
        return [
            'facturi' => 'facturi selectate',
        ];
    }
}
