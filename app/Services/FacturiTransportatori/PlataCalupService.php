<?php

namespace App\Services\FacturiTransportatori;

use App\Models\Comanda;
use App\Models\FacturiTransportatori\PlataCalup;
use Illuminate\Database\DatabaseManager;
use Illuminate\Validation\ValidationException;

class PlataCalupService
{
    public function __construct(private DatabaseManager $db)
    {
    }

    public function attachComenzi(PlataCalup $calup, array $comandaIds): void
    {
        $this->db->transaction(function () use ($calup, $comandaIds) {
            $comenzi = Comanda::query()->whereIn('id', $comandaIds)->lockForUpdate()->get();

            if (count($comandaIds) !== $comenzi->count()) {
                throw ValidationException::withMessages([
                    'comenzi' => 'Cel putin una dintre comenzile selectate nu mai exista.',
                ]);
            }

            $comenzi->load('calupuriFacturiTransportatori:id');

            foreach ($comenzi as $comanda) {
                if ($comanda->calupuriFacturiTransportatori->isNotEmpty()) {
                    throw ValidationException::withMessages([
                        'comenzi' => "Comanda {$comanda->transportator_contract} este deja atasata unui calup.",
                    ]);
                }
            }

            $syncPayload = [];
            $now = now();

            foreach ($comandaIds as $comandaId) {
                $syncPayload[$comandaId] = ['created_at' => $now, 'updated_at' => $now];
            }

            $calup->comenzi()->attach($syncPayload);
        });
    }

    public function moveComenzi(PlataCalup $calup, array $comandaIds): void
    {
        $this->db->transaction(function () use ($calup, $comandaIds) {
            $comenzi = Comanda::query()->whereIn('id', $comandaIds)->lockForUpdate()->get();

            if (count($comandaIds) !== $comenzi->count()) {
                throw ValidationException::withMessages([
                    'comenzi' => 'Cel putin una dintre comenzile selectate nu mai exista.',
                ]);
            }

            $comenzi->load('calupuriFacturiTransportatori:id');

            foreach ($comenzi as $comanda) {
                $currentCalupIds = $comanda->calupuriFacturiTransportatori
                    ->pluck('id')
                    ->filter(fn ($id) => (int) $id !== (int) $calup->id)
                    ->all();

                if (! empty($currentCalupIds)) {
                    $comanda->calupuriFacturiTransportatori()->detach($currentCalupIds);
                }
            }

            $existingIdsInTarget = $calup->comenzi()
                ->whereIn('comenzi.id', $comandaIds)
                ->pluck('comenzi.id')
                ->map(fn ($id) => (int) $id)
                ->all();

            $idsToAttach = array_values(array_diff(array_map('intval', $comandaIds), $existingIdsInTarget));

            if (empty($idsToAttach)) {
                return;
            }

            $syncPayload = [];
            $now = now();

            foreach ($idsToAttach as $comandaId) {
                $syncPayload[$comandaId] = ['created_at' => $now, 'updated_at' => $now];
            }

            $calup->comenzi()->attach($syncPayload);
        });
    }

    public function detachComanda(PlataCalup $calup, Comanda $comanda): void
    {
        $this->db->transaction(function () use ($calup, $comanda) {
            $calup->comenzi()->detach($comanda->id);
        });
    }
}
