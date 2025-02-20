<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DocumentWordRequest extends FormRequest
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
            'nume' => 'required|max:255',
            'nivel_acces' => 'nullable|integer',
            'continut' => 'json',
        ];
    }

    protected function prepareForValidation()
    {
        // Provide default if 'nivel_acces' is not present
        if (!$this->has('nivel_acces')) {
            $this->merge(['nivel_acces' => 2]);
        }
    }
}
