<?php

namespace App\Http\Requests;

use App\Models\Valabilitate;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'nr_ordine' => ['required', 'integer', 'min:1'],
            'nr_cursa' => ['nullable', 'string', 'max:255'],
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
            'km_bord_incarcare' => ['nullable', 'integer', 'min:0'],
            'km_bord_descarcare' => ['nullable', 'integer', 'min:0'],
            'final_return' => ['nullable', 'boolean'],
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
        );

        return $validated;
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
}
