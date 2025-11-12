<?php

namespace App\Http\Requests;

use App\Models\Valabilitate;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SoferValabilitateCursaRequest extends FormRequest
{
    protected bool $shouldRequireTime = false;

    public function authorize(): bool
    {
        $valabilitate = $this->route('valabilitate');
        $userId = $this->user()?->id;

        if (! $valabilitate instanceof Valabilitate) {
            return false;
        }

        return (int) $valabilitate->sofer_id === (int) $userId;
    }

    public function rules(): array
    {
        return [
            'incarcare_localitate' => ['nullable', 'string', 'max:255'],
            'incarcare_cod_postal' => ['nullable', 'string', 'max:255'],
            'incarcare_tara_id' => ['nullable', Rule::exists('tari', 'id')],
            'descarcare_localitate' => ['nullable', 'string', 'max:255'],
            'descarcare_cod_postal' => ['nullable', 'string', 'max:255'],
            'descarcare_tara_id' => ['nullable', Rule::exists('tari', 'id')],
            'data_cursa_date' => ['nullable', 'date'],
            'data_cursa_time' => ['nullable', 'date_format:H:i'],
            'data_cursa' => ['nullable', 'date'],
            'observatii' => ['nullable', 'string'],
            'km_bord' => ['nullable', 'integer', 'min:0'],
            'final_return' => ['nullable', 'boolean'],
            'form_type' => ['nullable', 'string'],
            'form_id' => ['nullable', 'integer'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->shouldRequireTime = $this->determineIfTimeIsRequired();

        $dateInput = $this->input('data_cursa_date');
        $timeInput = $this->input('data_cursa_time');

        if ($dateInput === null && $timeInput === null) {
            return;
        }

        $date = trim((string) $dateInput);
        $time = trim((string) $timeInput);

        if ($date === '') {
            $this->merge(['data_cursa' => null]);

            return;
        }

        if ($time === '') {
            $time = '00:00';
        }

        $this->merge([
            'data_cursa' => sprintf('%s %s', $date, $time),
        ]);
    }

    public function validated($key = null, $default = null)
    {
        $validated = parent::validated($key, $default);

        unset(
            $validated['data_cursa_date'],
            $validated['data_cursa_time'],
            $validated['final_return'],
            $validated['form_type'],
            $validated['form_id'],
        );

        return $validated;
    }

    protected function failedValidation(Validator $validator): void
    {
        if ($this->expectsJson()) {
            parent::failedValidation($validator);

            return;
        }

        $valabilitate = $this->route('valabilitate');
        $modalKey = $this->determineModalKey();

        $response = redirect()
            ->route('sofer.valabilitati.show', $valabilitate)
            ->withErrors($validator)
            ->withInput($this->all())
            ->with('sofer_curse_modal', $modalKey);

        throw new ValidationException($validator, $response);
    }

    protected function withValidator($validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (! $this->shouldRequireTime) {
                return;
            }

            $date = trim((string) $this->input('data_cursa_date'));
            $time = trim((string) $this->input('data_cursa_time'));

            if ($date === '') {
                $validator->errors()->add('data_cursa_date', 'Completați data cursei.');
            }

            if ($time === '') {
                $validator->errors()->add('data_cursa_time', 'Completați ora cursei.');
            }
        });
    }

    private function determineIfTimeIsRequired(): bool
    {
        if ($this->boolean('final_return')) {
            return true;
        }

        $valabilitate = $this->route('valabilitate');

        if (! $valabilitate instanceof Valabilitate) {
            return false;
        }

        if ($this->isMethod('post')) {
            return ! $valabilitate->curse()->exists();
        }

        return false;
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
}
