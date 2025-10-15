<?php

namespace App\Http\Requests\Service;

use App\Models\Service\GestiunePiesa;
use App\Models\Service\MasinaServiceEntry;

class UpdateMasinaServiceEntryRequest extends MasinaServiceEntryRequest
{
    protected function additionalPieceStock(GestiunePiesa $piesa): float
    {
        /** @var MasinaServiceEntry|null $entry */
        $entry = $this->route('entry');

        if ($entry && $entry->gestiune_piesa_id === $piesa->id && $entry->cantitate !== null) {
            return (float) $entry->cantitate;
        }

        return 0.0;
    }
}
