<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FlotaStatusCRequest extends FormRequest
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
            'dimenssions'  => 'nullable|string|max:255',
            'type'         => 'nullable|string|max:255',
            'out_of_eu'    => 'nullable|string|max:255',
            'info_i'       => 'nullable|string|max:255',
            'info_ii'      => 'nullable|string|max:255',
            'color'       => [
                'nullable',
                Rule::in([
                    '#FF0000', '#00FF00', '#0000FF', '#FFFF00', '#FFA500',
                    '#800080', '#008080', '#FFC0CB', '#A52A2A', '#808080',
                ]),
            ],
            // New validation rule for “ordine”
            'ordine'      => 'nullable|integer|min:0',
        ];
    }
}
