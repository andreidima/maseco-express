<?php

namespace App\Http\Resources\Driver;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class CursaResource extends JsonResource
{
    public function toArray($request): array
    {
        $dataCursa = $this->data_cursa;

        return [
            'id' => $this->id,
            'incarcare_localitate' => $this->incarcare_localitate,
            'incarcare_cod_postal' => $this->incarcare_cod_postal,
            'incarcare_tara_id' => $this->incarcare_tara_id,
            'incarcare_tara' => $this->whenLoaded('incarcareTara', fn () => $this->incarcareTara?->nume),
            'descarcare_localitate' => $this->descarcare_localitate,
            'descarcare_cod_postal' => $this->descarcare_cod_postal,
            'descarcare_tara_id' => $this->descarcare_tara_id,
            'descarcare_tara' => $this->whenLoaded('descarcareTara', fn () => $this->descarcareTara?->nume),
            'data_cursa' => $dataCursa?->toDateTimeString(),
            'data_date' => $dataCursa?->format('Y-m-d'),
            'data_time' => $dataCursa?->format('H:i'),
            'observatii' => $this->observatii,
            'km_bord' => $this->km_bord,
            'requires_time_for_romania' => $this->requiresTimeForRomania(),
        ];
    }

    private function requiresTimeForRomania(): bool
    {
        $tara = $this->relationLoaded('descarcareTara') ? $this->descarcareTara : null;

        if (! $tara) {
            return false;
        }

        return Str::lower((string) $tara->nume) === 'romania';
    }
}
