<?php

namespace Tests\Feature\FacturiFurnizori;

use App\Models\FacturiFurnizori\FacturaFurnizor;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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

        $this->assertDatabaseHas('service_ff_facturi', [
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

        $this->assertDatabaseHas('service_ff_facturi', [
            'id' => $factura->id,
            'suma' => -99.99,
        ]);
    }

    public function test_it_stores_products_together_with_the_invoice(): void
    {
        $user = User::factory()->create();

        $payload = [
            'denumire_furnizor' => 'Furnizor Produse SRL',
            'numar_factura' => 'PRD-001',
            'data_factura' => '2025-02-10',
            'data_scadenta' => '2025-02-20',
            'suma' => 999.99,
            'moneda' => 'RON',
            'cont_iban' => 'RO49AAAA1B31007593840000',
            'departament_vehicul' => 'BT-01',
            'observatii' => 'Observatie test',
            'produse' => [
                [
                    'denumire' => 'Filtru ulei',
                    'cod' => 'FU-01',
                    'nr_bucati' => '2',
                    'pret' => '35.50',
                    'tva_cota' => '11',
                ],
                [
                    'denumire' => 'Ulei motor',
                    'cod' => null,
                    'nr_bucati' => '1',
                    'pret' => '250',
                    'tva_cota' => '21',
                ],
            ],
        ];

        $response = $this->actingAs($user)
            ->post(route('facturi-furnizori.facturi.store'), $payload);

        $response->assertRedirect(route('facturi-furnizori.facturi.index'));

        $factura = FacturaFurnizor::query()
            ->where('numar_factura', 'PRD-001')
            ->with('piese')
            ->firstOrFail();

        $this->assertCount(2, $factura->piese);
        $this->assertEquals('Filtru ulei', $factura->piese[0]->denumire);
        $this->assertSame('35.50', $factura->piese[0]->pret);
        $this->assertSame('11.00', $factura->piese[0]->tva_cota);
        $this->assertSame('39.41', $factura->piese[0]->pret_brut);
        $this->assertEquals('Ulei motor', $factura->piese[1]->denumire);
        $this->assertSame('250.00', $factura->piese[1]->pret);
        $this->assertSame('21.00', $factura->piese[1]->tva_cota);
        $this->assertSame('302.50', $factura->piese[1]->pret_brut);
    }

    public function test_it_replaces_products_when_updating_an_invoice(): void
    {
        $user = User::factory()->create();
        $factura = FacturaFurnizor::factory()->create([
            'suma' => 150,
            'moneda' => 'EUR',
        ]);

        $factura->piese()->create([
            'denumire' => 'Produs vechi',
            'cod' => 'OLD-1',
            'nr_bucati' => 5,
            'pret' => 75,
        ]);

        $payload = [
            'denumire_furnizor' => $factura->denumire_furnizor,
            'numar_factura' => $factura->numar_factura,
            'data_factura' => $factura->data_factura->format('Y-m-d'),
            'data_scadenta' => $factura->data_scadenta->format('Y-m-d'),
            'suma' => $factura->suma,
            'moneda' => $factura->moneda,
            'cont_iban' => $factura->cont_iban ?? '',
            'departament_vehicul' => $factura->departament_vehicul ?? '',
            'observatii' => $factura->observatii ?? '',
            'produse' => [
                [
                    'denumire' => 'Plăcuțe frână',
                    'cod' => 'PF-02',
                    'nr_bucati' => 4,
                    'pret' => 120,
                    'tva_cota' => 21,
                ],
            ],
        ];

        $response = $this->actingAs($user)
            ->put(route('facturi-furnizori.facturi.update', $factura), $payload);

        $response->assertRedirect(route('facturi-furnizori.facturi.index'));

        $factura->refresh()->load('piese');

        $this->assertCount(1, $factura->piese);
        $this->assertEquals('Plăcuțe frână', $factura->piese[0]->denumire);
        $this->assertSame('120.00', $factura->piese[0]->pret);
        $this->assertSame('21.00', $factura->piese[0]->tva_cota);
        $this->assertSame('145.20', $factura->piese[0]->pret_brut);
        $this->assertDatabaseMissing('service_gestiune_piese', [
            'factura_id' => $factura->id,
            'denumire' => 'Produs vechi',
        ]);
    }

    public function test_it_allows_uploading_pdfs_for_an_invoice(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();

        $pdfOne = UploadedFile::fake()->create('factura_initiala.pdf', 120, 'application/pdf');
        $pdfTwo = UploadedFile::fake()->create('anexa.pdf', 80, 'application/pdf');

        $payload = [
            'denumire_furnizor' => 'Furnizor PDF SRL',
            'numar_factura' => 'PDF-001',
            'data_factura' => '2025-03-01',
            'data_scadenta' => '2025-03-15',
            'suma' => 250.75,
            'moneda' => 'RON',
            'cont_iban' => '',
            'departament_vehicul' => '',
            'observatii' => 'Factura cu atașamente',
            'fisiere_pdf' => [$pdfOne, $pdfTwo],
        ];

        $response = $this->actingAs($user)
            ->post(route('facturi-furnizori.facturi.store'), $payload);

        $response->assertRedirect(route('facturi-furnizori.facturi.index'));

        $factura = FacturaFurnizor::query()
            ->where('numar_factura', 'PDF-001')
            ->with('fisiere')
            ->firstOrFail();

        $this->assertCount(2, $factura->fisiere);

        foreach ($factura->fisiere as $fisier) {
            Storage::disk('local')->assertExists($fisier->cale);
        }
    }

    public function test_it_removes_attachments_when_deleting_an_invoice(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();

        $payload = [
            'denumire_furnizor' => 'Furnizor Delete SRL',
            'numar_factura' => 'DEL-001',
            'data_factura' => '2025-02-01',
            'data_scadenta' => '2025-02-10',
            'suma' => 100.00,
            'moneda' => 'RON',
            'cont_iban' => '',
            'departament_vehicul' => '',
            'observatii' => '',
            'fisiere_pdf' => [UploadedFile::fake()->create('sterge.pdf', 50, 'application/pdf')],
        ];

        $this->actingAs($user)
            ->post(route('facturi-furnizori.facturi.store'), $payload);

        $factura = FacturaFurnizor::query()
            ->where('numar_factura', 'DEL-001')
            ->with('fisiere')
            ->firstOrFail();

        $this->assertCount(1, $factura->fisiere);
        $caleFisier = $factura->fisiere->first()->cale;

        $this->actingAs($user)
            ->delete(route('facturi-furnizori.facturi.destroy', $factura));

        Storage::disk('local')->assertMissing($caleFisier);
        $this->assertDatabaseMissing('service_ff_facturi_fisiere', [
            'factura_id' => $factura->id,
        ]);
    }
}
