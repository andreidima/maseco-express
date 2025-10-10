<?php

namespace Tests\Feature\FacturiFurnizori;

use App\Models\FacturiFurnizori\FacturaFurnizor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FacturaFurnizorTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_allows_storing_negative_amounts(): void
    {
        $user = User::factory()->create();

        $payload = [
            'denumire_furnizor' => 'Furnizor Negativ SRL',
            'numar_factura' => 'NEG-001',
            'data_factura' => '2025-01-15',
            'data_scadenta' => '2025-01-20',
            'suma' => -150.25,
            'moneda' => 'RON',
            'cont_iban' => '',
            'departament_vehicul' => '',
            'observatii' => '',
        ];

        $response = $this->actingAs($user)
            ->post(route('facturi-furnizori.facturi.store'), $payload);

        $response->assertRedirect(route('facturi-furnizori.facturi.index'));

        $this->assertDatabaseHas('ff_facturi', [
            'numar_factura' => 'NEG-001',
            'suma' => -150.25,
        ]);
    }

    public function test_it_allows_updating_to_negative_amounts(): void
    {
        $user = User::factory()->create();
        $factura = FacturaFurnizor::factory()->create([
            'suma' => 450.00,
            'moneda' => 'RON',
        ]);

        $payload = [
            'denumire_furnizor' => $factura->denumire_furnizor,
            'numar_factura' => $factura->numar_factura,
            'data_factura' => $factura->data_factura->format('Y-m-d'),
            'data_scadenta' => $factura->data_scadenta->format('Y-m-d'),
            'suma' => -99.99,
            'moneda' => $factura->moneda,
            'cont_iban' => $factura->cont_iban ?? '',
            'departament_vehicul' => $factura->departament_vehicul ?? '',
            'observatii' => $factura->observatii ?? '',
        ];

        $response = $this->actingAs($user)
            ->put(route('facturi-furnizori.facturi.update', $factura), $payload);

        $response->assertRedirect(route('facturi-furnizori.facturi.index'));

        $this->assertDatabaseHas('ff_facturi', [
            'id' => $factura->id,
            'suma' => -99.99,
        ]);
    }
}
