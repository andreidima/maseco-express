<?php

namespace App\Http\Requests\Service;

use Illuminate\Foundation\Http\FormRequest;

abstract class ServiceSheetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $items = collect($this->input('items', []))
            ->map(function ($item) {
                $item = is_array($item) ? $item : [];

                return [
                    'description' => trim((string) ($item['description'] ?? '')),
                    'quantity' => trim((string) ($item['quantity'] ?? '')),
                    'notes' => trim((string) ($item['notes'] ?? '')),
                ];
            })
            ->filter(function (array $item) {
                return $item['description'] !== ''
                    || $item['quantity'] !== ''
                    || $item['notes'] !== '';
            })
            ->values()
            ->all();

        $this->merge([
            'items' => $items,
        ]);
    }

    public function rules(): array
    {
        return [
            'km_bord' => ['required', 'integer', 'min:0', 'max:1000000'],
            'data_service' => ['required', 'date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['nullable', 'string', 'max:50'],
            'items.*.notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function attributes(): array
    {
        return [
            'km_bord' => 'km bord',
            'data_service' => 'data service',
            'items.*.description' => 'descriere intervenție',
            'items.*.quantity' => 'cantitate',
            'items.*.notes' => 'observații / manoperă',
        ];
    }
}
