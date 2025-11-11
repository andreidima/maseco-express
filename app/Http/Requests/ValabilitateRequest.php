<?php

namespace App\Http\Requests;

use App\Models\Valabilitate;
use Illuminate\Foundation\Http\FormRequest;

class ValabilitateRequest extends FormRequest
{
    public function authorize(): bool
    {
        $ability = $this->isMethod('POST') ? 'create' : 'update';

        $subject = $this->route('valabilitate');

        if (! $subject instanceof Valabilitate) {
            $subject = Valabilitate::class;
        }

        return $this->user()?->can($ability, $subject) ?? false;
    }

    public function rules(): array
    {
        return [
            'masina_id' => ['nullable', 'integer', 'exists:masini,id'],
            'referinta' => ['nullable', 'string', 'max:255'],
            'prima_cursa' => ['nullable', 'date'],
            'ultima_cursa' => ['nullable', 'date', 'after_or_equal:prima_cursa'],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        if (array_key_exists('masina_id', $data)) {
            $data['masina_id'] = $data['masina_id'] ? (int) $data['masina_id'] : null;
        }

        return $data;
    }
}
