<?php

namespace App\Services\FacturiFurnizori;

use App\Models\FacturiFurnizori\FacturaFurnizor;
use App\Models\FacturiFurnizori\PlataCalup;
use Illuminate\Database\DatabaseManager;
use Illuminate\Validation\ValidationException;

class PlataCalupService
{
    public function __construct(private DatabaseManager $db)
    {
    }

    /**
     * Attach invoices to the given batch and mark them as "in calup".
     */
    public function attachFacturi(PlataCalup $calup, array $facturaIds): void
    {
        $this->db->transaction(function () use ($calup, $facturaIds) {
            $facturi = FacturaFurnizor::query()->whereIn('id', $facturaIds)->lockForUpdate()->get();

            if (count($facturaIds) !== $facturi->count()) {
                throw ValidationException::withMessages([
                    'facturi' => 'Cel putin una dintre facturile selectate nu mai exista.',
                ]);
            }

            $facturi->each(function (FacturaFurnizor $factura) {
                if ($factura->calupuri()->exists()) {
                    throw ValidationException::withMessages([
                        'facturi' => "Factura {$factura->numar_factura} este deja atasata unui calup.",
                    ]);
                }
            });

            $syncPayload = [];
            $now = now();
            foreach ($facturaIds as $facturaId) {
                $syncPayload[$facturaId] = ['created_at' => $now, 'updated_at' => $now];
            }

            $calup->facturi()->attach($syncPayload);
        });
    }

    /**
     * Detach an invoice from the batch.
     */
    public function detachFactura(PlataCalup $calup, FacturaFurnizor $factura): void
    {
        $this->db->transaction(function () use ($calup, $factura) {
            $calup->facturi()->detach($factura->id);
        });
    }
}
