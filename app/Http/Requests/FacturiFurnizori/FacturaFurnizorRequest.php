<?php

namespace App\Http\Requests\FacturiFurnizori;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class FacturaFurnizorRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'denumire_furnizor' => ['required', 'string', 'max:150'],
            'numar_factura' => ['required', 'string', 'max:100'],
            'data_factura' => ['required', 'date'],
            'data_scadenta' => ['required', 'date', 'after_or_equal:data_factura'],
            'suma' => ['required', 'numeric'],
            'moneda' => ['required', 'string', 'size:3'],
            'cont_iban' => ['nullable', 'string', 'max:255'],
            'departament_vehicul' => ['nullable', 'string', 'max:150'],
            'observatii' => ['nullable', 'string'],
            'produse' => ['nullable', 'array'],
            'produse.*.id' => ['nullable', 'integer', 'exists:service_gestiune_piese,id'],
            'produse.*.denumire' => ['nullable', 'string', 'max:255'],
            'produse.*.cod' => ['nullable', 'string', 'max:100'],
            'produse.*.cantitate_initiala' => ['nullable', 'numeric'],
            'produse.*.nr_bucati' => ['nullable', 'numeric'],
            'produse.*.pret' => ['nullable', 'numeric'],
            'produse.*.tva_cota' => ['nullable', 'numeric', Rule::in([11, 21])],
            'produse.*.pret_brut' => ['nullable', 'numeric'],
            'fisiere_pdf' => ['nullable', 'array'],
            'fisiere_pdf.*' => ['file', 'mimes:pdf', 'max:10240'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if (!$this->has('produse')) {
            return;
        }

        $produse = collect($this->input('produse', []))
            ->map(function ($row, $index) {
                $id = isset($row['id']) && $row['id'] !== ''
                    ? (int) $row['id']
                    : null;
                $denumire = isset($row['denumire']) ? trim((string) $row['denumire']) : null;
                $cod = isset($row['cod']) ? trim((string) $row['cod']) : null;
                $cantitateInitiala = array_key_exists('cantitate_initiala', $row) && $row['cantitate_initiala'] !== ''
                    ? $row['cantitate_initiala']
                    : null;
                $nrBucati = array_key_exists('nr_bucati', $row) && $row['nr_bucati'] !== ''
                    ? $row['nr_bucati']
                    : null;
                $pret = array_key_exists('pret', $row) && $row['pret'] !== ''
                    ? $row['pret']
                    : null;
                $tvaCota = array_key_exists('tva_cota', $row) && $row['tva_cota'] !== ''
                    ? $row['tva_cota']
                    : null;
                $pretBrut = array_key_exists('pret_brut', $row) && $row['pret_brut'] !== ''
                    ? $row['pret_brut']
                    : null;

                return [
                    'id' => $id,
                    'denumire' => $denumire,
                    'cod' => $cod,
                    'cantitate_initiala' => $cantitateInitiala,
                    'nr_bucati' => $nrBucati,
                    'pret' => $pret,
                    'tva_cota' => $tvaCota,
                    'pret_brut' => $pretBrut,
                    'form_index' => $index,
                ];
            })
            ->toArray();

        $this->merge([
            'produse' => $produse,
        ]);
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($innerValidator) {
            $produse = $this->input('produse', []);

            foreach ($produse as $index => $produs) {
                $denumire = trim((string) ($produs['denumire'] ?? ''));
                $cod = trim((string) ($produs['cod'] ?? ''));
                $nrBucati = $produs['nr_bucati'] ?? null;
                $cantitateInitiala = $produs['cantitate_initiala'] ?? null;
                $pret = $produs['pret'] ?? null;
                $tvaCota = $produs['tva_cota'] ?? null;
                $pretBrut = $produs['pret_brut'] ?? null;

                $hasOtherData = $cod !== ''
                    || ($cantitateInitiala !== null && $cantitateInitiala !== '')
                    || ($nrBucati !== null && $nrBucati !== '')
                    || ($pret !== null && $pret !== '')
                    || ($tvaCota !== null && $tvaCota !== '')
                    || ($pretBrut !== null && $pretBrut !== '');

                if ($hasOtherData && $denumire === '') {
                    $innerValidator->errors()->add("produse.$index.denumire", 'Denumirea produsului este obligatorie.');
                }
            }
        });
    }

    /**
     * Custom validation messages in Romanian.
     */
    public function messages(): array
    {
        return [
            'denumire_furnizor.required' => 'Denumirea furnizorului este obligatorie.',
            'numar_factura.required' => 'Numarul facturii este obligatoriu.',
            'data_factura.required' => 'Data facturii este obligatorie.',
            'data_scadenta.after_or_equal' => 'Data scadentei trebuie sa fie egala sau ulterioara datei facturii.',
            'suma.required' => 'Suma este obligatorie.',
            'suma.numeric' => 'Suma trebuie sa fie un numar.',
            'moneda.size' => 'Moneda trebuie sa contina exact 3 caractere.',
            'produse.array' => 'Lista de produse trebuie sa fie un format valid.',
            'produse.*.nr_bucati.numeric' => 'Cantitatea produsului trebuie sa fie un numar.',
            'produse.*.pret.numeric' => 'Pretul produsului trebuie sa fie un numar.',
            'produse.*.cantitate_initiala.numeric' => 'Cantitatea inițială trebuie să fie un număr.',
        ];
    }

    /**
     * Custom attribute names in Romanian.
     */
    public function attributes(): array
    {
        return [
            'denumire_furnizor' => 'denumire furnizor',
            'numar_factura' => 'numar factura',
            'data_factura' => 'data facturii',
            'data_scadenta' => 'data scadentei',
            'suma' => 'suma',
            'moneda' => 'moneda',
            'cont_iban' => 'cont IBAN',
            'departament_vehicul' => 'departament / numar auto',
            'observatii' => 'observatii',
            'produse' => 'produse',
            'produse.*.cantitate_initiala' => 'cantitate inițială',
            'produse.*.denumire' => 'denumire produs',
            'produse.*.cod' => 'cod produs',
            'produse.*.nr_bucati' => 'cantitate produs',
            'produse.*.pret' => 'pret produs',
        ];
    }
}
