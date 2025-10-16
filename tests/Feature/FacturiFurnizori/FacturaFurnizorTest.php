<?php

namespace Tests\Feature\FacturiFurnizori;

use App\Models\FacturiFurnizori\FacturaFurnizor;
use App\Models\Service\GestiunePiesa;
use App\Models\Service\Masina;
use App\Models\Service\MasinaServiceEntry;
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
        $this->assertSame('2.00', $factura->piese[0]->cantitate_initiala);
        $this->assertSame('2.00', $factura->piese[0]->nr_bucati);
        $this->assertSame('35.50', $factura->piese[0]->pret);
        $this->assertSame('11.00', $factura->piese[0]->tva_cota);
        $this->assertSame('39.41', $factura->piese[0]->pret_brut);
        $this->assertEquals('Ulei motor', $factura->piese[1]->denumire);
        $this->assertSame('1.00', $factura->piese[1]->cantitate_initiala);
        $this->assertSame('1.00', $factura->piese[1]->nr_bucati);
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
            'cantitate_initiala' => 5,
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
        $this->assertSame('4.00', $factura->piese[0]->cantitate_initiala);
        $this->assertSame('4.00', $factura->piese[0]->nr_bucati);
        $this->assertSame('120.00', $factura->piese[0]->pret);
        $this->assertSame('21.00', $factura->piese[0]->tva_cota);
        $this->assertSame('145.20', $factura->piese[0]->pret_brut);
        $this->assertDatabaseMissing('service_gestiune_piese', [
            'factura_id' => $factura->id,
            'denumire' => 'Produs vechi',
        ]);
    }

    public function test_it_updates_initial_quantity_without_affecting_allocations(): void
    {
        $user = User::factory()->create();
        $factura = FacturaFurnizor::factory()->create([
            'suma' => 250,
            'moneda' => 'RON',
        ]);

        $piece = $factura->piese()->create([
            'denumire' => 'Filtru aer',
            'cod' => 'FA-1',
            'cantitate_initiala' => 5,
            'nr_bucati' => 5,
            'pret' => 50,
            'tva_cota' => 21,
            'pret_brut' => 60.5,
        ]);

        $masina = Masina::factory()->create();

        $entry = MasinaServiceEntry::create([
            'masina_id' => $masina->id,
            'gestiune_piesa_id' => $piece->id,
            'tip' => 'piesa',
            'denumire_piesa' => $piece->denumire,
            'cod_piesa' => $piece->cod,
            'cantitate' => 2,
            'data_montaj' => '2025-01-05',
            'nume_mecanic' => 'Ion Mecanic',
            'nume_utilizator' => 'Maria Utilizator',
        ]);

        $piece->decrement('nr_bucati', 2);
        $piece->refresh();

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
                    'id' => $piece->id,
                    'denumire' => $piece->denumire,
                    'cod' => $piece->cod,
                    'cantitate_initiala' => '8',
                    'nr_bucati' => '6',
                    'pret' => '50',
                    'tva_cota' => '21',
                    'pret_brut' => '60.5',
                ],
            ],
        ];

        $response = $this->actingAs($user)
            ->put(route('facturi-furnizori.facturi.update', $factura), $payload);

        $response->assertRedirect(route('facturi-furnizori.facturi.index'));

        $piece->refresh();

        $this->assertSame('8.00', $piece->cantitate_initiala);
        $this->assertSame('6.00', $piece->nr_bucati);
        $this->assertDatabaseHas('service_masina_service_entries', [
            'id' => $entry->id,
            'gestiune_piesa_id' => $piece->id,
        ]);
    }

    public function test_it_validates_initial_quantity_against_allocations(): void
    {
        $user = User::factory()->create();
        $factura = FacturaFurnizor::factory()->create([
            'suma' => 300,
            'moneda' => 'RON',
        ]);

        $piece = $factura->piese()->create([
            'denumire' => 'Set plăcuțe',
            'cod' => 'SP-9',
            'cantitate_initiala' => 4,
            'nr_bucati' => 4,
            'pret' => 40,
            'tva_cota' => 21,
            'pret_brut' => 48.4,
        ]);

        $masina = Masina::factory()->create();

        MasinaServiceEntry::create([
            'masina_id' => $masina->id,
            'gestiune_piesa_id' => $piece->id,
            'tip' => 'piesa',
            'denumire_piesa' => $piece->denumire,
            'cod_piesa' => $piece->cod,
            'cantitate' => 3,
            'data_montaj' => '2025-02-10',
            'nume_mecanic' => 'Vasile Mecanic',
            'nume_utilizator' => 'Andrei Utilizator',
        ]);

        $piece->decrement('nr_bucati', 3);
        $piece->refresh();

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
                    'id' => $piece->id,
                    'denumire' => $piece->denumire,
                    'cod' => $piece->cod,
                    'cantitate_initiala' => '2',
                    'nr_bucati' => '0',
                    'pret' => '40',
                    'tva_cota' => '21',
                    'pret_brut' => '48.4',
                ],
            ],
        ];

        $response = $this->actingAs($user)
            ->from(route('facturi-furnizori.facturi.edit', $factura))
            ->put(route('facturi-furnizori.facturi.update', $factura), $payload);

        $response->assertRedirect(route('facturi-furnizori.facturi.edit', $factura));
        $response->assertSessionHasErrors(['produse.0.cantitate_initiala']);

        $piece->refresh();
        $this->assertSame('4.00', $piece->cantitate_initiala);
        $this->assertSame('1.00', $piece->nr_bucati);
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

    public function test_show_displays_products_with_stock_details_modal_trigger(): void
    {
        $user = User::factory()->create();
        $factura = FacturaFurnizor::factory()->create();

        $piece = GestiunePiesa::factory()
            ->for($factura, 'factura')
            ->create([
                'denumire' => 'Filtru cabină',
                'cod' => 'FC-01',
                'cantitate_initiala' => 5,
                'nr_bucati' => 3,
                'pret' => 75.5,
                'tva_cota' => 21,
                'pret_brut' => 91.36,
            ]);

        $masina = Masina::factory()->create([
            'denumire' => 'Camion Test',
            'numar_inmatriculare' => 'B-99-XYZ',
        ]);

        MasinaServiceEntry::factory()->create([
            'masina_id' => $masina->id,
            'gestiune_piesa_id' => $piece->id,
            'tip' => 'piesa',
            'denumire_piesa' => $piece->denumire,
            'cod_piesa' => $piece->cod,
            'cantitate' => 2,
            'data_montaj' => '2024-05-10',
        ]);

        $response = $this->actingAs($user)->get(route('facturi-furnizori.facturi.show', $factura));

        $response->assertOk();
        $response->assertSee('Cantitate inițială');
        $response->assertSee('Preț NET/buc');
        $response->assertSee('Cotă TVA');
        $response->assertSee('Preț BRUT/buc');
        $response->assertSee('Filtru cabină', false);
        $response->assertSee('75.50');
        $response->assertSee('91.36');
        $response->assertSee('data-piece-initial="5.00"', false);
        $response->assertSee('data-piece-remaining="3.00"', false);
        $response->assertSee('data-piece-used="2.00"', false);
        $response->assertSee("data-piece-machines='[", false);
        $response->assertSee('"masina_id":' . $masina->id, false);
    }
}
