<?php

namespace App\Http\Requests\Driver;

use App\Models\Valabilitate;
use App\Models\ValabilitateCursa;
use App\Services\Driver\ActiveValabilitatiService;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Arr;
use Illuminate\Foundation\Http\FormRequest;

class CursaRequest extends FormRequest
{
    protected bool $dateProvided = false;
    protected bool $timeProvided = false;

    public function authorize(): bool
    {
        $valabilitate = $this->route('valabilitate');

        if (! $valabilitate instanceof Valabilitate) {
            return false;
        }

        try {
            app(ActiveValabilitatiService::class)->ensureActiveForUser($this->user(), $valabilitate);
        } catch (ModelNotFoundException) {
            return false;
        }

        $cursa = $this->route('cursa');

        if ($cursa instanceof ValabilitateCursa && (int) $cursa->valabilitate_id !== (int) $valabilitate->id) {
            return false;
        }

        return true;
    }

    public function rules(): array
    {
        return [
            'incarcare_localitate' => ['required', 'string', 'max:255'],
            'incarcare_cod_postal' => ['nullable', 'string', 'max:255'],
            'incarcare_tara_id' => ['required', 'exists:tari,id'],
            'descarcare_localitate' => ['required', 'string', 'max:255'],
            'descarcare_cod_postal' => ['nullable', 'string', 'max:255'],
            'descarcare_tara_id' => ['required', 'exists:tari,id'],
            'data_cursa' => ['nullable', 'date'],
            'data_cursa_date' => ['nullable', 'date'],
            'data_cursa_time' => ['nullable', 'date_format:H:i'],
            'observatii' => ['nullable', 'string'],
            'km_bord' => ['nullable', 'integer', 'min:0'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $date = trim((string) $this->input('data_cursa_date', ''));
        $time = trim((string) $this->input('data_cursa_time', ''));

        $this->dateProvided = $date !== '';
        $this->timeProvided = $time !== '';

        if ($date === '') {
            $this->merge(['data_cursa' => null]);

            return;
        }

        if ($time === '') {
            $this->merge(['data_cursa' => $date]);

            return;
        }

        $this->merge([
            'data_cursa' => sprintf('%s %s', $date, $time),
        ]);
    }

    protected function passedValidation(): void
    {
        if (! $this->dateProvided) {
            $this->merge(['data_cursa' => null]);
        }
    }

    protected function withValidator($validator): void
    {
        $validator->after(function (Validator $validator): void {
            /** @var Valabilitate|null $valabilitate */
            $valabilitate = $this->route('valabilitate');

            if (! $valabilitate instanceof Valabilitate) {
                return;
            }

            $service = app(ActiveValabilitatiService::class);

            try {
                $service->ensureActiveForUser($this->user(), $valabilitate);
            } catch (ModelNotFoundException) {
                $validator->errors()->add('valabilitate', __('Valabilitatea nu este disponibilă.'));

                return;
            }

            $requiresTime = $this->requiresTime($valabilitate, $service);

            if ($requiresTime) {
                if (! $this->dateProvided) {
                    $validator->errors()->add('data_cursa_date', __('Data este obligatorie.'));
                }

                if (! $this->timeProvided) {
                    $validator->errors()->add('data_cursa_time', __('Ora este obligatorie.'));
                }
            }
        });
    }

    public function validated($key = null, $default = null)
    {
        $validated = Arr::except(parent::validated(), ['data_cursa_date', 'data_cursa_time']);

        return data_get($validated, $key, $validated) ?? $default;
    }

    private function requiresTime(Valabilitate $valabilitate, ActiveValabilitatiService $service): bool
    {
        if ($service->requiresRomaniaTime($this->resolveTaraId())) {
            return true;
        }

        $cursa = $this->route('cursa');

        $firstExisting = $valabilitate->curse()
            ->orderBy('data_cursa')
            ->orderBy('created_at')
            ->first();

        if (! $firstExisting) {
            return true;
        }

        if ($cursa instanceof ValabilitateCursa) {
            return (int) $cursa->id === (int) $firstExisting->id;
        }

        return false;
    }

    private function resolveTaraId(): ?int
    {
        $id = $this->input('descarcare_tara_id');

        if ($id === null || $id === '') {
            return null;
        }

        return (int) $id;
    }
}
