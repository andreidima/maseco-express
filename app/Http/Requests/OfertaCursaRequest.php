<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OfertaCursaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Adjust this as needed (e.g. check roles/permissions).
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string,\Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'email_subiect'           => 'nullable|string|max:255',
            'email_expeditor'         => 'nullable|email|max:255',
            'data_primirii'           => 'nullable|date',
            'gmail_link'              => 'nullable|url',

            'incarcare_cod_postal'    => 'nullable|string|max:255',
            'incarcare_localitate'    => 'nullable|string|max:255',
            'incarcare_data_ora'      => 'nullable|string|max:255',

            'descarcare_cod_postal'   => 'nullable|string|max:255',
            'descarcare_localitate'   => 'nullable|string|max:255',
            'descarcare_data_ora'     => 'nullable|string|max:255',

            'greutate'           => 'nullable|numeric|min:0|max:999999999',
            'detalii_cursa'           => 'nullable|string',
        ];
    }
}
