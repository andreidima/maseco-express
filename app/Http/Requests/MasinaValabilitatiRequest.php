<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MasinaValabilitatiRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Assuming any authenticated user can create/update. Adjust as needed.
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     * All fields are nullable strings up to 255 characters.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'nr_auto'      => 'nullable|string|max:255',
            'nume_sofer'  => 'nullable|string|max:255',
            'detalii_sofer'  => 'nullable|string|max:255',
            'divizie'         => 'nullable|string|max:255',
            'valabilitate_1_inceput' => 'nullable|date',
            'valabilitate_1_sfarsit'  => 'nullable|date|after_or_equal:valabilitate_1_inceput',
            'observatii_1'       => 'nullable|string|max:255',
            'valabilitate_2_inceput' => 'nullable|date',
            'valabilitate_2_sfarsit'  => 'nullable|date|after_or_equal:valabilitate_2_inceput',
            'observatii_2'       => 'nullable|string|max:255',
        ];
    }
}
