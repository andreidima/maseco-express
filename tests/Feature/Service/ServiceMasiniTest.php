<?php

namespace Tests\Feature\Service;

use App\Models\Service\GestiunePiesa;
use App\Models\Service\Masina;
use App\Models\Service\MasinaServiceEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceMasiniTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_displays_masini(): void
    {
        $user = User::factory()->create();
        $masina = Masina::factory()->create(['denumire' => 'Camion Test', 'numar_inmatriculare' => 'B01ABC']);

        $response = $this->actingAs($user)->get(route('service-masini.index', ['masina_id' => $masina->id]));

        $response->assertOk();
        $response->assertSee('Service mașini');
        $response->assertSee('Camion Test');
    }

    public function test_it_creates_a_new_masina(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('service-masini.store-masina'), [
            'denumire' => 'Duba Test',
            'numar_inmatriculare' => 'CT09XYZ',
            'serie_sasiu' => 'SERIE1234567',
            'observatii' => 'Test observatii',
        ]);

        $masina = Masina::query()->first();

        $response->assertRedirect(route('service-masini.index', ['masina_id' => $masina->id]));
        $this->assertDatabaseHas('service_masini', [
            'denumire' => 'Duba Test',
            'numar_inmatriculare' => 'CT09XYZ',
        ]);
    }

    public function test_it_allocates_piece_and_decrements_stock(): void
    {
        $user = User::factory()->create(['name' => 'Test User']);
        $masina = Masina::factory()->create();
        $piesa = GestiunePiesa::factory()->create(['nr_bucati' => 5]);

        $response = $this->actingAs($user)->post(route('service-masini.entries.store', $masina), [
            'tip' => 'piesa',
            'gestiune_piesa_id' => $piesa->id,
            'cantitate' => 2,
            'data_montaj' => now()->toDateString(),
            'nume_mecanic' => 'Ion Mecanic',
            'observatii' => 'Test piesă',
        ]);

        $response->assertRedirect();

        $entry = MasinaServiceEntry::query()->first();
        $this->assertNotNull($entry);
        $this->assertSame('piesa', $entry->tip);
        $this->assertSame($masina->id, $entry->masina_id);
        $this->assertSame($piesa->id, $entry->gestiune_piesa_id);
        $this->assertSame('Ion Mecanic', $entry->nume_mecanic);
        $this->assertSame('Test User', $entry->nume_utilizator);
        $this->assertEquals(2.0, (float) $entry->cantitate);

        $piesa->refresh();
        $this->assertEquals(3.0, (float) $piesa->nr_bucati);
    }

    public function test_it_stores_manual_intervention(): void
    {
        $user = User::factory()->create();
        $masina = Masina::factory()->create();

        $response = $this->actingAs($user)->post(route('service-masini.entries.store', $masina), [
            'tip' => 'manual',
            'denumire_interventie' => 'Schimb ulei',
            'data_montaj' => now()->toDateString(),
            'nume_mecanic' => 'Mecanic Test',
            'observatii' => 'Ulei 5W40',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('service_masina_service_entries', [
            'masina_id' => $masina->id,
            'tip' => 'manual',
            'denumire_interventie' => 'Schimb ulei',
        ]);
    }

    public function test_it_exports_pdf(): void
    {
        $user = User::factory()->create();
        $masina = Masina::factory()->create();
        MasinaServiceEntry::factory()->create([
            'masina_id' => $masina->id,
            'tip' => 'manual',
            'denumire_interventie' => 'Verificare lumini',
        ]);

        $response = $this->actingAs($user)->get(route('service-masini.export', ['masina_id' => $masina->id]));

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', $response->headers->get('content-type'));
        $response->assertSee('table-layout: fixed', false);
        $response->assertSee('word-break: break-word', false);
    }
}
