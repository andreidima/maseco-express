<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Firma;

class ComandaRequest extends FormRequest
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
            'data_creare' => 'required',
            'interval_notificari' => 'required',
            // 'transportator_contract' => 'required|max:20',
            'transportator_limba_id' => '',
            'transportator_valoare_contract' => 'required|numeric|min:-9999999|max:9999999',
            'transportator_moneda_id' => 'required|in:2',
            'transportator_zile_scadente' => 'nullable|numeric|min:-100|max:300',
            'transportator_termen_plata_id' => '',
            'transportator_transportator_id' => 'required',
            'transportator_procent_tva_id' => '',
            'transportator_metoda_de_plata_id' => '',
            'transportator_format_documente' => 'required',
            'transportator_tarif_pe_km' => '',
            'transportator_pret_km_goi' => 'required_if:transportator_tarif_pe_km,1|numeric|min:0|max:99999',
            'transportator_pret_km_plini' => 'required_if:transportator_tarif_pe_km,1|numeric|min:0|max:99999',
            'transportator_km_goi' => 'required_if:transportator_tarif_pe_km,1|numeric|min:0|max:99999',
            'transportator_km_plini' => 'required_if:transportator_tarif_pe_km,1|numeric|min:0|max:99999',
            'transportator_valoare_km_goi' => 'required_if:transportator_tarif_pe_km,1|numeric|min:0|max:99999',
            'transportator_valoare_km_plini' => 'required_if:transportator_tarif_pe_km,1|numeric|min:0|max:99999',
            'transportator_pret_autostrada' => 'required_if:transportator_tarif_pe_km,1|numeric|min:0|max:99999',

            // Removed on 18.02.2025 - ferry price is not used anymore, because is not exactly known when the command is registered, so it's added later in 'intermedieri'.
            // 'transportator_pret_ferry' => 'required_if:transportator_tarif_pe_km,1|numeric|min:0|max:99999',


            // Comented on 14.01.2025 - after that we went to more that one client to a command
            // 'client_contract' => 'nullable|max:255',
            // 'client_limba_id' => '',
            // 'client_valoare_contract_initiala' => 'required|numeric|min:-9999999|max:9999999',
            // 'client_valoare_contract' => 'required|numeric|min:-9999999|max:9999999',
            // 'client_moneda_id' => 'required',
            // 'client_zile_scadente' => 'nullable|numeric|min:-100|max:300',
            // 'client_termen_plata_id' => '',
            // 'client_client_id' => 'required',
            // 'client_procent_tva_id' => '',
            // 'client_metoda_de_plata_id' => '',
            // 'client_tarif_pe_km' => '',

            // This was commented before 2024
            // 'client_data_factura' => '',
            // 'client_zile_inainte_de_scadenta_memento_factura' =>
            //     function ($attribute, $value, $fail) use ($request) {
            //         if ($value){
            //             $zileInainte = preg_split ("/\,/", $value);
            //             foreach ($zileInainte as $ziInainte){
            //                 if (!(intval($ziInainte) == $ziInainte)){
            //                     $fail('Câmpul „Cu câte zile înainte de scadență să se trimită memento” nu este completat corect');
            //                 }elseif ($ziInainte < 0){
            //                     $fail('Câmpul „Cu câte zile înainte de scadență să se trimită memento” nu poate conține valori negative');
            //                 }elseif ($ziInainte > 100){
            //                     $fail('Câmpul „Cu câte zile înainte de scadență să se trimită memento” nu poate conține valori mai mari de 100');
            //                 }
            //             }
            //         }
            //     },

            // Added on 14.01.2025 - after that we went to more that one client to a command
            'clienti.*.id' => 'required',
            'clienti.*.pivot.contract' => 'required|max:255',
            'clienti.*.pivot.valoare_contract_initiala' => 'required|numeric|between:-9999999,9999999',
            'clienti.*.pivot.moneda_id' => 'required|in:2',
            'clienti.*.pivot.client_procent_tva_id' => '',
            'clienti.*.pivot.client_metoda_de_plata_id' => '',
            'clienti.*.pivot.client_termen_plata_id' => '',
            'clienti.*.pivot.client_zile_scadente' => 'nullable|numeric|min:-100|max:300',
            'clienti.*.pivot.client_tarif_pe_km' => '',

            // those 2 fields are used for all raports: is the summ value for all clients
            'client_valoare_contract' => 'required|numeric|min:-9999999|max:9999999',
            'client_moneda_id' => 'required|in:2',

            'descriere_marfa' => 'nullable|max:500',
            'camion_id' => 'required',

            'incarcari.*.id' => 'required',
            // 'incarcari.*.nume' => 'required|max:500',
            // 'incarcari.*.oras' => 'nullable|max:500',
            'incarcari.*.pivot.data_ora' => 'required',
            'incarcari.*.pivot.durata' => 'required',

            'descarcari.*.id' => 'required',
            'descarcari.*.pivot.data_ora' => 'required',
            'descarcari.*.pivot.durata' => 'required',

            'observatii_interne' => 'nullable|max:2000',
            'observatii_externe' => 'nullable|max:2000',

            'debit_note_suma' => 'nullable|numeric|min:-9999999|max:9999999',
            'debit_note_ore' => 'nullable|numeric|min:-100|max:300',
            'debit_note_adresa' => 'nullable|max:2000',

            'user_id' => 'required',
            'operator_user_id' => 'nullable',

            // 'observatii' => 'nullable|max:2000',
        ];
    }

    public function messages()
    {
        return [
            'transportator_transportator_id.required' => 'Câmpul Transportator este obligatoriu',
            'transportator_moneda_id.in' => 'Câmpul Transportator Moneda trebuie să fie EUR',

            // Comented on 14.01.2025 - after that we went to more that one client to a command
            'client_client_id.required' => 'Câmpul Client este obligatoriu',
            // Added on 14.01.2025 - after that we went to more that one client to a command
            'clienti.*.id' => 'Clientul #:position este obligatoriu de selectat din baza de date',
            'clienti.*.pivot.contract.required' => 'Câmpul Contract pentru clientul #:position este obligatoriu',
            'clienti.*.pivot.contract.max' => 'Câmpul Contract pentru clientul #:position nu poate avea mai mult de 255 de caractere',
            'clienti.*.pivot.moneda_id.required' => 'Câmpul Moneda pentru clientul #:position este obligatoriu',
            'clienti.*.pivot.moneda_id.in' => 'Câmpul Moneda pentru clientul #:position trebuie să fie EUR',
            'clienti.*.pivot.valoare_contract_initiala.required' => 'Câmpul Valoare Contract Inițială pentru clientul #:position este obligatoriu',
            'clienti.*.pivot.valoare_contract_initiala.numeric' => 'Câmpul Valoare Contract Inițială pentru clientul #:position trebuie să fie un număr',
            'clienti.*.pivot.valoare_contract_initiala.between' => 'Câmpul Valoare Contract Inițială pentru clientul #:position trebuie să fie între -9999999 și 9999999',

            'client_valoare_contract.required' => 'Câmpul Valoare contract finală este obligatoriu',
            'client_valoare_contract.numeric' => 'Câmpul Valoare contract finală trebuie să fie numeric',
            'client_valoare_contract.min' => 'Câmpul Valoare contract finală poate fi minim 9999999',
            'client_valoare_contract.max' => 'Câmpul Valoare contract finală poate fi maxim 9999999',
            'client_moneda_id.required' => 'Câmpul Moneda de la Valoare contract finală este obligatoriu',
            'client_moneda_id.in' => 'Câmpul Moneda de la Valoare contract finală este obligatoriu să fie EUR',

            'camion_id.required' => 'Câmpul Camion este obligatoriu',

            'incarcari.*.id' => 'Încărcarea #:position este obligatoriu de selectat din baza de date',
            'incarcari.*.pivot.data_ora' => 'Câmpul Data și ora pentru încărcarea #:position este obligatoriu',
            'incarcari.*.pivot.durata' => 'Câmpul Durata pentru încărcarea #:position este obligatoriu',
            'descarcari.*.id' => 'Descărcarea #:position este obligatoriu de selectat din baza de date',
            'descarcari.*.pivot.data_ora' => 'Câmpul Data și ora pentru descărcarea #:position este obligatoriu',
            'descarcari.*.pivot.durata' => 'Câmpul Durata pentru descărcarea #:position este obligatoriu',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $data = $this->all();


            /**
             * Check if client final value equals the sum of all clients' initial values.
             */
            $finalValue = isset($data['client_valoare_contract']) ? (float)$data['client_valoare_contract'] : 0;
            $sum = 0;

            if (isset($data['clienti']) && is_array($data['clienti'])) {
                foreach ($data['clienti'] as $client) {
                    $initial = isset($client['pivot']['valoare_contract_initiala'])
                        ? (float)$client['pivot']['valoare_contract_initiala']
                        : 0;
                    $sum += $initial;
                }
            }

            if ($finalValue !== $sum) {
                $validator->errors()->add(
                    'client_valoare_contract',
                    'Valoarea finală a contractului trebuie să fie egală cu suma valorilor inițiale ale contractelor tuturor clienților.'
                );
            }



            // Validation: Check if any Firma (transportator or clienti) has format_documente set to 1.
            $requiresFormatDocumente = false;
            $firmeCuFormat = [];

            // Check the transportator Firma record.
            $transportatorId = $this->input('transportator_transportator_id');
            if ($transportatorId) {
                $transportatorFirma = Firma::find($transportatorId);
                if ($transportatorFirma && (int)$transportatorFirma->format_documente === 1) {
                    $requiresFormatDocumente = true;
                    $firmeCuFormat[] = $transportatorFirma->nume; // adjust property if needed
                }
            }

            // Check each client Firma record.
            if (isset($data['clienti']) && is_array($data['clienti'])) {
                foreach ($data['clienti'] as $client) {
                    $clientId = $client['id'] ?? null;
                    if ($clientId) {
                        $clientFirma = \App\Models\Firma::find($clientId);
                        if ($clientFirma && (int)$clientFirma->format_documente === 1) {
                            $requiresFormatDocumente = true;
                            $firmeCuFormat[] = $clientFirma->nume; // adjust property if needed
                        }
                    }
                }
            }

            // Remove duplicate firm names.
            $firmeCuFormat = array_unique($firmeCuFormat);

            // If any Firma requires format_documente to be 1, validate the incoming value.
            if ($requiresFormatDocumente) {
                $providedFormatDocumente = $this->input('transportator_format_documente');
                if ((int)$providedFormatDocumente !== 1) {
                    $firmeList = implode(', ', $firmeCuFormat);
                    $validator->errors()->add(
                        'transportator_format_documente',
                        'Câmpul Format documente trebuie să fie Per post, deoarece următoarele firme (' . $firmeList . ') au format documente setat Per post.'
                    );
                }
            }
        });
    }
}
