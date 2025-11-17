<?php

namespace App\Http\Requests;

use App\Models\Valabilitate;
use App\Models\ValabilitateCursaGrup;
use App\Support\Valabilitati\ValabilitatiCurseFilterState;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ValabilitateCursaGrupRequest extends FormRequest
{
    public function authorize(): bool
    {
        $valabilitate = $this->route('valabilitate');

        return $valabilitate && $this->user()?->can('update', $valabilitate);
    }

    public function rules(): array
    {
        return [
            'nume' => ['required', 'string', 'max:255'],
            'format_documente' => ['required', Rule::in(array_keys(ValabilitateCursaGrup::documentFormats()))],
            'suma_incasata' => ['nullable', 'numeric', 'min:0'],
            'suma_calculata' => ['nullable', 'numeric', 'min:0'],
            'data_factura' => ['nullable', 'date'],
            'numar_factura' => ['nullable', 'string', 'max:255'],
            'culoare_hex' => ['required', Rule::in(array_keys(ValabilitateCursaGrup::colorPalette()))],
        ];
    }

    protected function prepareForValidation(): void
    {
        $color = $this->input('culoare_hex');

        if (is_string($color)) {
            $this->merge([
                'culoare_hex' => strtoupper($color),
            ]);
        }
    }

    protected function failedValidation(Validator $validator): void
    {
        if ($this->expectsJson()) {
            parent::failedValidation($validator);

            return;
        }

        $valabilitate = $this->route('valabilitate');
        $modalKey = $this->determineModalKey();

        $response = redirect($this->resolveRedirectUrl($valabilitate))
            ->withErrors($validator)
            ->withInput($this->all())
            ->with('curse.modal', $modalKey);

        throw new ValidationException($validator, $response);
    }

    private function determineModalKey(): string
    {
        $formType = (string) $this->input('form_type', '');

        if ($formType === 'group-edit') {
            $formId = (int) $this->input('form_id');

            return $formId > 0 ? 'group-edit:' . $formId : 'group-edit';
        }

        return 'group-create';
    }

    private function resolveRedirectUrl(?Valabilitate $valabilitate): string
    {
        if ($valabilitate instanceof Valabilitate) {
            return ValabilitatiCurseFilterState::route($valabilitate);
        }

        return url()->previous();
    }
}
