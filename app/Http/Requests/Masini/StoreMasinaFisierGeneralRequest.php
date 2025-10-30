<?php

namespace App\Http\Requests\Masini;

use Illuminate\Foundation\Http\FormRequest;

class StoreMasinaFisierGeneralRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'fisier' => ['required', 'array', 'min:1'],
            'fisier.*' => [
                'file',
                'mimes:pdf,jpg,jpeg,png,gif,webp,bmp,svg,txt,csv,doc,docx,xls,xlsx,ppt,pptx',
                'max:51200',
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'fisier' => __('fișier'),
            'fisier.*' => __('fișier'),
        ];
    }
}
