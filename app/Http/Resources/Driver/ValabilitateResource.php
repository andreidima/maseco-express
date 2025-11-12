<?php

namespace App\Http\Resources\Driver;

use Illuminate\Http\Resources\Json\JsonResource;

class ValabilitateResource extends JsonResource
{
    public function toArray($request): array
    {
        $orderedCurse = $this->relationLoaded('curse')
            ? $this->curse->sortBy([
                ['data_cursa', 'asc'],
                ['created_at', 'asc'],
            ])
            : null;

        $curse = $this->whenLoaded('curse', function () use ($orderedCurse) {
            return CursaResource::collection(($orderedCurse ?? collect())->values());
        });

        $firstCursaId = $orderedCurse?->first()?->id;

        return [
            'id' => $this->id,
            'numar_auto' => $this->numar_auto,
            'denumire' => $this->denumire,
            'data_inceput' => $this->data_inceput?->format('Y-m-d'),
            'data_sfarsit' => $this->data_sfarsit?->format('Y-m-d'),
            'curse_count' => $this->curse_count ?? ($this->relationLoaded('curse') ? $this->curse->count() : 0),
            'curse' => $curse,
            'first_cursa_id' => $firstCursaId,
            'has_curse' => ($this->curse_count ?? 0) > 0 || ($orderedCurse?->isNotEmpty() ?? false),
        ];
    }
}
