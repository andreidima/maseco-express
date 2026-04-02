<?php

namespace App\Http\Requests\FacturiTransportatori;

class MoveComenziToCalupRequest extends AttachComenziRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'plata_calup_id' => ['required', 'integer', 'exists:facturi_transportatori_plati_calupuri,id'],
        ]);
    }

    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'plata_calup_id.required' => 'Selectati calupul in care doriti sa mutati comenzile.',
            'plata_calup_id.exists' => 'Calupul selectat nu mai exista.',
        ]);
    }

    public function attributes(): array
    {
        return array_merge(parent::attributes(), [
            'plata_calup_id' => 'calup selectat',
        ]);
    }
}
