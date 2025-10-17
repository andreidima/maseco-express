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
                    'descriere' => trim((string) ($item['descriere'] ?? '')),
                ];
            })
            ->filter(function (array $item) {
                return $item['descriere'] !== '';
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
            'items.*.descriere' => ['required', 'string', 'max:255'],
        ];
    }

    public function attributes(): array
    {
        return [
            'km_bord' => 'km bord',
            'data_service' => 'data service',
            'items.*.descriere' => 'descriere intervenție',
        ];
    }
}
