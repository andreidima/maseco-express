<?php

namespace App\Http\Requests\FacturiFurnizori;

use Illuminate\Foundation\Http\FormRequest;

class FacturaFurnizorRequest extends FormRequest
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
            'denumire_furnizor' => ['required', 'string', 'max:150'],
            'numar_factura' => ['required', 'string', 'max:100'],
            'data_factura' => ['required', 'date'],
            'data_scadenta' => ['required', 'date', 'after_or_equal:data_factura'],
            'suma' => ['required', 'numeric', 'min:0'],
            'moneda' => ['required', 'string', 'size:3'],
            'cont_iban' => ['nullable', 'string', 'max:255'],
            'departament_vehicul' => ['nullable', 'string', 'max:150'],
            'observatii' => ['nullable', 'string'],
        ];
    }

    /**
     * Custom validation messages in Romanian.
     */
    public function messages(): array
    {
        return [
            'denumire_furnizor.required' => 'Denumirea furnizorului este obligatorie.',
            'numar_factura.required' => 'Numarul facturii este obligatoriu.',
            'data_factura.required' => 'Data facturii este obligatorie.',
            'data_scadenta.after_or_equal' => 'Data scadentei trebuie sa fie egala sau ulterioara datei facturii.',
            'suma.required' => 'Suma este obligatorie.',
            'suma.numeric' => 'Suma trebuie sa fie un numar.',
            'moneda.size' => 'Moneda trebuie sa contina exact 3 caractere.',
        ];
    }

    /**
     * Custom attribute names in Romanian.
     */
    public function attributes(): array
    {
        return [
            'denumire_furnizor' => 'denumire furnizor',
            'numar_factura' => 'numar factura',
            'data_factura' => 'data facturii',
            'data_scadenta' => 'data scadentei',
            'suma' => 'suma',
            'moneda' => 'moneda',
            'cont_iban' => 'cont IBAN',
            'departament_vehicul' => 'departament / numar auto',
            'observatii' => 'observatii',
        ];
    }
}
