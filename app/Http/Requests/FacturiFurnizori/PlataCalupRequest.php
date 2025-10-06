<?php

namespace App\Http\Requests\FacturiFurnizori;

use App\Models\FacturiFurnizori\PlataCalup;
use Illuminate\Foundation\Http\FormRequest;

class PlataCalupRequest extends FormRequest
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
            'denumire_calup' => ['required', 'string', 'max:150'],
            'data_plata' => ['nullable', 'date'],
            'observatii' => ['nullable', 'string'],
            'status' => ['nullable', 'in:' . implode(',', [
                PlataCalup::STATUS_DESCHIS,
                PlataCalup::STATUS_PLATIT,
                PlataCalup::STATUS_ANULAT,
            ])],
            'fisier_pdf' => ['nullable', 'file', 'mimetypes:application/pdf', 'max:10240'],
            'facturi' => ['nullable', 'array'],
            'facturi.*' => ['integer', 'exists:ff_facturi,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'denumire_calup.required' => 'Denumirea calupului este obligatorie.',
            'denumire_calup.max' => 'Denumirea calupului poate avea cel mult 150 de caractere.',
            'data_plata.date' => 'Data platii trebuie sa fie o data valida.',
            'status.in' => 'Statusul selectat nu este valid.',
            'fisier_pdf.mimetypes' => 'Fisierul incarcat trebuie sa fie PDF.',
            'fisier_pdf.max' => 'Fisierul PDF poate avea cel mult 10MB.',
            'facturi.array' => 'Lista de facturi nu este valida.',
            'facturi.*.exists' => 'Cel putin una dintre facturile selectate nu mai exista.',
        ];
    }

    public function attributes(): array
    {
        return [
            'denumire_calup' => 'denumire calup',
            'data_plata' => 'data platii',
            'observatii' => 'observatii',
            'status' => 'status',
            'fisier_pdf' => 'fisier PDF',
            'facturi' => 'facturi selectate',
        ];
    }
}
