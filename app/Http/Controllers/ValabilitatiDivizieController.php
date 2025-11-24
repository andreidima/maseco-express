<?php

namespace App\Http\Controllers;

use App\Models\ValabilitatiDivizie;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ValabilitatiDivizieController extends Controller
{
    public function show(ValabilitatiDivizie $divizie): JsonResponse
    {
        return response()->json([
            'divizie' => $this->transformDivizie($divizie),
        ]);
    }

    public function update(Request $request, ValabilitatiDivizie $divizie): JsonResponse
    {
        $validated = $this->validatePrices($request);

        $divizie->update($validated);

        return response()->json([
            'message' => 'Tarifele diviziei au fost actualizate.',
            'divizie' => $this->transformDivizie($divizie->fresh()),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validatePrices(Request $request): array
    {
        $validated = $request->validate([
            'flash_pret_km_gol' => ['nullable', 'numeric', 'min:0', 'max:9999999.999'],
            'flash_pret_km_plin' => ['nullable', 'numeric', 'min:0', 'max:9999999.999'],
            'flash_pret_km_cu_taxa' => ['nullable', 'numeric', 'min:0', 'max:9999999.999'],
            'flash_contributie_zilnica' => ['nullable', 'numeric', 'min:0', 'max:9999999.999'],
            'timestar_pret_km_bord' => ['nullable', 'numeric', 'min:0', 'max:9999999.999'],
            'timestar_pret_nr_zile_lucrate' => ['nullable', 'numeric', 'min:0', 'max:9999999.999'],
        ]);

        return $this->formatPrices($validated);
    }

    /**
     * @param array<string, mixed> $values
     *
     * @return array<string, mixed>
     */
    private function formatPrices(array $values): array
    {
        foreach ([
            'flash_pret_km_gol',
            'flash_pret_km_plin',
            'flash_pret_km_cu_taxa',
            'flash_contributie_zilnica',
            'timestar_pret_km_bord',
            'timestar_pret_nr_zile_lucrate',
        ] as $field) {
            if (! array_key_exists($field, $values)) {
                continue;
            }

            $values[$field] = $this->formatPrice($values[$field]);
        }

        return $values;
    }

    private function formatPrice(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return number_format((float) $value, 3, '.', '');
    }

    /**
     * @return array<string, mixed>
     */
    private function transformDivizie(ValabilitatiDivizie $divizie): array
    {
        return [
            'id' => $divizie->id,
            'nume' => $divizie->nume,
            'flash_pret_km_gol' => $this->formatPrice($divizie->flash_pret_km_gol),
            'flash_pret_km_plin' => $this->formatPrice($divizie->flash_pret_km_plin),
            'flash_pret_km_cu_taxa' => $this->formatPrice($divizie->flash_pret_km_cu_taxa),
            'flash_contributie_zilnica' => $this->formatPrice($divizie->flash_contributie_zilnica),
            'timestar_pret_km_bord' => $this->formatPrice($divizie->timestar_pret_km_bord),
            'timestar_pret_nr_zile_lucrate' => $this->formatPrice($divizie->timestar_pret_nr_zile_lucrate),
        ];
    }
}
