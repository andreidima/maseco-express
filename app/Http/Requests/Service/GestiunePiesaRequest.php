<?php

namespace App\Http\Requests\Service;

use App\Models\Service\GestiunePiesa;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Validator;

class GestiunePiesaRequest extends FormRequest
{
    private float $usedQuantity = 0.0;

    private ?GestiunePiesa $resolvedPiece = null;

    private bool $pieceResolved = false;

    private bool $usedQuantityLoaded = false;

    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'factura_id' => ['nullable', 'integer', 'exists:service_ff_facturi,id'],
            'denumire' => ['required', 'string', 'max:255'],
            'cod' => ['nullable', 'string', 'max:255'],
            'cantitate_initiala' => ['nullable', 'numeric', 'min:0'],
            'nr_bucati' => ['nullable', 'numeric', 'min:0'],
            'pret' => ['nullable', 'numeric', 'min:0'],
            'tva_cota' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'pret_brut' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $numericFields = [
            'cantitate_initiala',
            'nr_bucati',
            'pret',
            'tva_cota',
            'pret_brut',
        ];

        $normalized = [];

        foreach ($numericFields as $field) {
            $value = $this->input($field);

            if (is_string($value)) {
                $value = str_replace([' ', ','], ['', '.'], $value);
            }

            if ($value === '') {
                $value = null;
            }

            if ($value !== null) {
                $normalized[$field] = is_numeric($value) ? (float) $value : $value;
            }
        }

        if (! empty($normalized)) {
            $this->merge($normalized);
        }
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $initial = $this->input('cantitate_initiala');
            $piece = $this->resolvePiece();

            $used = $this->ensureUsedQuantityIsLoaded();

            if ($piece instanceof GestiunePiesa && $initial === null) {
                $initial = $piece->cantitate_initiala;
            }

            if ($initial !== null) {
                $initial = round((float) $initial, 2);

                if ($used > $initial) {
                    $validator->errors()->add('cantitate_initiala', sprintf(
                        'Cantitatea inițială nu poate fi mai mică decât totalul montat pe mașini (%.2f).',
                        $used
                    ));
                }
            } elseif ($used > 0) {
                $validator->errors()->add('cantitate_initiala', 'Cantitatea inițială trebuie să fie cel puțin egală cu totalul montat pe mașini.');
            }
        });
    }

    public function validated($key = null, $default = null): array
    {
        $data = parent::validated($key, $default);

        $initial = $data['cantitate_initiala'] ?? null;
        $piece = $this->resolvePiece();

        if ($initial === null && $piece instanceof GestiunePiesa && $piece->cantitate_initiala !== null) {
            $initial = (float) $piece->cantitate_initiala;
            $data['cantitate_initiala'] = $initial;
        }

        $used = $this->ensureUsedQuantityIsLoaded();

        if ($initial !== null) {
            $initial = round((float) $initial, 2);
            $data['cantitate_initiala'] = $initial;
            $data['nr_bucati'] = round(max($initial - $used, 0), 2);
        } else {
            $data['nr_bucati'] = null;
        }

        return $data;
    }

    public function usedQuantity(): float
    {
        return $this->ensureUsedQuantityIsLoaded();
    }

    private function resolvePiece(): ?GestiunePiesa
    {
        if (! $this->pieceResolved) {
            $piece = $this->route('gestiune_piesa');

            if ($piece && ! $piece instanceof GestiunePiesa) {
                $piece = GestiunePiesa::query()->find($piece);
            }

            $this->resolvedPiece = $piece instanceof GestiunePiesa ? $piece : null;
            $this->pieceResolved = true;
        }

        return $this->resolvedPiece;
    }

    private function ensureUsedQuantityIsLoaded(): float
    {
        if (! $this->usedQuantityLoaded) {
            $piece = $this->resolvePiece();

            if ($piece instanceof GestiunePiesa) {
                $this->usedQuantity = (float) DB::table('service_masina_service_entries')
                    ->where('gestiune_piesa_id', $piece->getKey())
                    ->where('tip', 'piesa')
                    ->whereNotNull('cantitate')
                    ->sum('cantitate');
            } else {
                $this->usedQuantity = 0.0;
            }

            $this->usedQuantityLoaded = true;
        }

        return round($this->usedQuantity, 2);
    }
}
