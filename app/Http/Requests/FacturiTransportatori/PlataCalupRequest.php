<?php

namespace App\Http\Requests\FacturiTransportatori;

use Illuminate\Foundation\Http\FormRequest;

class PlataCalupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'denumire_calup' => ['required', 'string', 'max:150'],
            'data_plata' => ['nullable', 'date'],
            'observatii' => ['nullable', 'string'],
            'fisiere_pdf' => ['nullable', 'array'],
            'fisiere_pdf.*' => ['file', 'mimetypes:application/pdf', 'max:10240'],
            'comenzi' => ['nullable', 'array'],
            'comenzi.*' => ['integer', 'exists:comenzi,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'denumire_calup.required' => 'Denumirea calupului este obligatorie.',
            'denumire_calup.max' => 'Denumirea calupului poate avea cel mult 150 de caractere.',
            'data_plata.date' => 'Data platii trebuie sa fie o data valida.',
            'fisiere_pdf.array' => 'Lista de fisiere nu este valida.',
            'fisiere_pdf.*.mimetypes' => 'Fiecare fisier incarcat trebuie sa fie PDF.',
            'fisiere_pdf.*.max' => 'Fiecare fisier PDF poate avea cel mult 10MB.',
            'comenzi.array' => 'Lista de comenzi nu este valida.',
            'comenzi.*.exists' => 'Cel putin una dintre comenzile selectate nu mai exista.',
        ];
    }
}
