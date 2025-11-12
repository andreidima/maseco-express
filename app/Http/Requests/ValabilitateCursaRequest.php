<?php

namespace App\Http\Requests;

use App\Models\Valabilitate;
use App\Support\Valabilitati\ValabilitatiCurseFilterState;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class ValabilitateCursaRequest extends FormRequest
{
    public function authorize(): bool
    {
        $valabilitate = $this->route('valabilitate');

        return $valabilitate && $this->user()?->can('update', $valabilitate);
    }

    public function rules(): array
    {
        return [
            'incarcare_localitate' => ['nullable', 'string', 'max:255'],
            'incarcare_cod_postal' => ['nullable', 'string', 'max:255'],
            'descarcare_localitate' => ['nullable', 'string', 'max:255'],
            'descarcare_cod_postal' => ['nullable', 'string', 'max:255'],
            'data_cursa' => ['nullable', 'date'],
            'observatii' => ['nullable', 'string'],
        ];
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

        if ($formType === 'edit') {
            $formId = (int) $this->input('form_id');

            return $formId > 0 ? 'edit:' . $formId : 'edit';
        }

        return 'create';
    }

    private function resolveRedirectUrl(?Valabilitate $valabilitate): string
    {
        if ($valabilitate instanceof Valabilitate) {
            return ValabilitatiCurseFilterState::route($valabilitate);
        }

        return url()->previous();
    }
}
