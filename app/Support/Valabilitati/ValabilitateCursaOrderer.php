<?php

namespace App\Support\Valabilitati;

use App\Models\ValabilitateCursa;
use Illuminate\Support\Facades\DB;

class ValabilitateCursaOrderer
{
    public static function moveUp(ValabilitateCursa $cursa): bool
    {
        return self::move($cursa, 'up');
    }

    public static function moveDown(ValabilitateCursa $cursa): bool
    {
        return self::move($cursa, 'down');
    }

    public static function move(ValabilitateCursa $cursa, string $direction): bool
    {
        $direction = strtolower($direction);

        $query = ValabilitateCursa::query()
            ->where('valabilitate_id', $cursa->valabilitate_id);

        if ($direction === 'up') {
            $swap = (clone $query)
                ->where('nr_ordine', '<', $cursa->nr_ordine)
                ->orderByDesc('nr_ordine')
                ->first();
        } elseif ($direction === 'down') {
            $swap = (clone $query)
                ->where('nr_ordine', '>', $cursa->nr_ordine)
                ->orderBy('nr_ordine')
                ->first();
        } else {
            return false;
        }

        if (! $swap) {
            return false;
        }

        return (bool) DB::transaction(static function () use ($cursa, $swap) {
            $currentOrder = $cursa->nr_ordine;

            $cursa->forceFill(['nr_ordine' => $swap->nr_ordine])->save();
            $swap->forceFill(['nr_ordine' => $currentOrder])->save();

            return true;
        });
    }
}
