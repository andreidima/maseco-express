<?php

namespace App\Http\Requests;

use App\Models\ValabilitateCursa;
use Illuminate\Foundation\Http\FormRequest;

class ValabilitateCursaRequest extends FormRequest
{
    public function authorize(): bool
    {
        $valabilitate = $this->route('valabilitate');

        return $valabilitate && $this->user()?->can('update', $valabilitate);
    }

    public function rules(): array
    {
        $valabilitate = $this->route('valabilitate');
        $cursa = $this->route('cursa');

        $isFirstTrip = false;

        if ($valabilitate) {
            $query = $valabilitate->curse();

            if ($cursa instanceof ValabilitateCursa) {
                $query->whereKeyNot($cursa->getKey());
            }

            $isFirstTrip = $query->doesntExist();
        }

        $ultimaCursaRequested = $this->boolean('ultima_cursa');

        $oraRules = ['nullable', 'date_format:H:i'];

        if ($isFirstTrip || $ultimaCursaRequested) {
            $oraRules[0] = 'required';
        }

        return [
            'localitate_plecare' => ['required', 'string', 'max:255'],
            'localitate_sosire' => ['nullable', 'string', 'max:255'],
            'descarcare_tara' => ['nullable', 'string', 'size:2'],
            'plecare_la' => ['nullable', 'date'],
            'sosire_la' => ['nullable', 'date', 'after_or_equal:plecare_la'],
            'ora' => $oraRules,
            'km_bord' => ['nullable', 'integer', 'min:0'],
            'observatii' => ['nullable', 'string'],
            'ultima_cursa' => ['required', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $ora = $this->input('ora');
        $descarcareTara = $this->input('descarcare_tara');
        $descarcareTara = $descarcareTara === '' ? null : strtoupper((string) $descarcareTara);

        $this->merge([
            'ultima_cursa' => $this->has('ultima_cursa')
                ? filter_var($this->input('ultima_cursa'), FILTER_VALIDATE_BOOLEAN)
                : false,
            'ora' => $ora === '' ? null : $ora,
            'descarcare_tara' => $descarcareTara,
        ]);
    }
}
