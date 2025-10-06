<?php

namespace App\Services\FacturiFurnizori;

use App\Models\FacturiFurnizori\FacturaFurnizor;
use App\Models\FacturiFurnizori\PlataCalup;
use Carbon\CarbonInterface;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Facades\Log;
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
                if ($factura->status === FacturaFurnizor::STATUS_PLATITA) {
                    throw ValidationException::withMessages([
                        'facturi' => "Factura {$factura->numar_factura} este deja platita.",
                    ]);
                }

                if ($factura->status === FacturaFurnizor::STATUS_IN_CALUP) {
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

            FacturaFurnizor::query()
                ->whereIn('id', $facturaIds)
                ->update(['status' => FacturaFurnizor::STATUS_IN_CALUP]);
        });
    }

    /**
     * Detach an invoice from the batch and revert its status.
     */
    public function detachFactura(PlataCalup $calup, FacturaFurnizor $factura): void
    {
        $this->db->transaction(function () use ($calup, $factura) {
            $calup->facturi()->where('ff_facturi.id', $factura->id)->detach();

            if ($factura->status === FacturaFurnizor::STATUS_IN_CALUP) {
                $factura->update(['status' => FacturaFurnizor::STATUS_NEPLATITA]);
            }
        });
    }

    /**
     * Mark the batch as paid, update payment date and invoice statuses.
     */
    public function markAsPaid(PlataCalup $calup, ?CarbonInterface $dataPlata = null): void
    {
        $dataPlata = $dataPlata ?? now();

        $this->db->transaction(function () use ($calup, $dataPlata) {
            $calup->update([
                'status' => PlataCalup::STATUS_PLATIT,
                'data_plata' => $dataPlata,
            ]);

            $facturiIds = $calup->facturi()->pluck('ff_facturi.id');

            if ($facturiIds->isEmpty()) {
                Log::warning('Calup marcat ca platit fara facturi atasate', ['calup_id' => $calup->id]);
                return;
            }

            FacturaFurnizor::query()
                ->whereIn('id', $facturiIds)
                ->update(['status' => FacturaFurnizor::STATUS_PLATITA]);
        });
    }

    /**
     * Reopen a paid batch turning invoices back to unpaid.
     */
    public function reopen(PlataCalup $calup): void
    {
        $this->db->transaction(function () use ($calup) {
            $calup->update([
                'status' => PlataCalup::STATUS_DESCHIS,
                'data_plata' => null,
            ]);

            $facturiIds = $calup->facturi()->pluck('ff_facturi.id');

            if ($facturiIds->isNotEmpty()) {
                FacturaFurnizor::query()
                    ->whereIn('id', $facturiIds)
                    ->update(['status' => FacturaFurnizor::STATUS_IN_CALUP]);
            }
        });
    }
}
