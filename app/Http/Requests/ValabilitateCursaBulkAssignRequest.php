<?php

namespace App\Http\Requests;

use App\Models\Valabilitate;
use App\Support\Valabilitati\ValabilitatiCurseFilterState;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ValabilitateCursaBulkAssignRequest extends FormRequest
{
    public function authorize(): bool
    {
        $valabilitate = $this->route('valabilitate');

        return $valabilitate && $this->user()?->can('update', $valabilitate);
    }

    public function rules(): array
    {
        $valabilitate = $this->route('valabilitate');

        return [
            'curse_ids' => ['required', 'array', 'min:1'],
            'curse_ids.*' => [
                'integer',
                'distinct',
                Rule::exists('valabilitati_curse', 'id')->where(function ($query) use ($valabilitate) {
                    if ($valabilitate instanceof Valabilitate) {
                        $query->where('valabilitate_id', $valabilitate->getKey());
                    }
                }),
            ],
            'cursa_grup_id' => [
                'required',
                'integer',
                Rule::exists('valabilitati_cursa_grupuri', 'id')->where(function ($query) use ($valabilitate) {
                    if ($valabilitate instanceof Valabilitate) {
                        $query->where('valabilitate_id', $valabilitate->getKey());
                    }
                }),
            ],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        if ($this->expectsJson()) {
            parent::failedValidation($validator);

            return;
        }

        $valabilitate = $this->route('valabilitate');

        $response = redirect($this->resolveRedirectUrl($valabilitate))
            ->withErrors($validator)
            ->withInput($this->all());

        throw new ValidationException($validator, $response);
    }

    private function resolveRedirectUrl(?Valabilitate $valabilitate): string
    {
        if ($valabilitate instanceof Valabilitate) {
            return ValabilitatiCurseFilterState::route($valabilitate);
        }

        return url()->previous();
    }
}
