<?php

namespace App\Http\Requests;

use App\Models\Valabilitate;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SoferValabilitateCursaRequest extends FormRequest
{
    protected bool $shouldRequireDateTime = false;

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
            'nr_ordine' => ['sometimes', 'integer', 'min:1'],
            'nr_cursa' => ['nullable', 'string', 'max:255'],
            'incarcare_localitate' => ['nullable', 'string', 'max:255'],
            'incarcare_cod_postal' => ['nullable', 'string', 'max:255'],
            'incarcare_tara_id' => ['nullable', Rule::exists('tari', 'id')],
            'descarcare_localitate' => ['nullable', 'string', 'max:255'],
            'descarcare_cod_postal' => ['nullable', 'string', 'max:255'],
            'descarcare_tara_id' => ['nullable', Rule::exists('tari', 'id')],
            'data_cursa' => ['nullable', 'date'],
            'observatii' => ['nullable', 'string'],
            'km_bord_incarcare' => ['nullable', 'integer', 'min:0'],
            'km_bord_descarcare' => ['nullable', 'integer', 'min:0'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->shouldRequireDateTime = $this->determineIfDateTimeIsRequired();

        $dateTimeInput = $this->input('data_cursa');

        if ($dateTimeInput === null) {
            return;
        }

        $normalized = str_replace('T', ' ', trim((string) $dateTimeInput));

        if ($normalized === '') {
            $this->merge(['data_cursa' => null]);

            return;
        }

        $this->merge([
            'data_cursa' => $normalized,
        ]);
    }

    public function validated($key = null, $default = null)
    {
        $validated = parent::validated($key, $default);

        return $validated;
    }

    protected function withValidator($validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (! $this->shouldRequireDateTime) {
                return;
            }

            $dateTime = trim((string) $this->input('data_cursa'));

            if ($dateTime === '') {
                $validator->errors()->add('data_cursa', 'Completați data și ora cursei.');
            }
        });
    }

    private function determineIfDateTimeIsRequired(): bool
    {
        $valabilitate = $this->route('valabilitate');

        if (! $valabilitate instanceof Valabilitate) {
            return false;
        }

        if ($this->isMethod('post')) {
            return ! $valabilitate->curse()->exists();
        }

        return false;
    }
}
