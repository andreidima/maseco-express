<?php

namespace App\Http\Requests\Service;

use App\Models\Service\GestiunePiesa;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreMasinaServiceEntryRequest extends FormRequest
{
    protected ?GestiunePiesa $piesa = null;

    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'tip' => ['required', 'in:piesa,manual'],
            'gestiune_piesa_id' => ['nullable', 'integer', 'exists:service_gestiune_piese,id'],
            'cantitate' => ['nullable', 'numeric', 'min:0.01'],
            'denumire_interventie' => ['nullable', 'string', 'max:255'],
            'data_montaj' => ['required', 'date'],
            'nume_mecanic' => ['required', 'string', 'max:255'],
            'observatii' => ['nullable', 'string'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $tip = (string) $this->input('tip', '');

            if ($tip === 'piesa') {
                if (! $this->filled('gestiune_piesa_id')) {
                    $validator->errors()->add('gestiune_piesa_id', 'Selectează o piesă din gestiune.');

                    return;
                }

                $cantitate = (float) $this->input('cantitate', 0);
                if ($cantitate <= 0) {
                    $validator->errors()->add('cantitate', 'Cantitatea trebuie să fie mai mare decât zero.');
                }

                $this->piesa = GestiunePiesa::query()->find($this->input('gestiune_piesa_id'));

                if (! $this->piesa) {
                    $validator->errors()->add('gestiune_piesa_id', 'Piesa selectată nu există.');
                } elseif ((float) $this->piesa->nr_bucati < $cantitate) {
                    $validator->errors()->add('cantitate', 'Cantitatea depășește stocul disponibil.');
                }
            } elseif ($tip === 'manual') {
                if (! $this->filled('denumire_interventie')) {
                    $validator->errors()->add('denumire_interventie', 'Completează denumirea intervenției.');
                }
            }
        });
    }

    public function piece(): ?GestiunePiesa
    {
        return $this->piesa;
    }
}
