<?php

namespace Tests\Feature\Service;

use App\Models\Role;
use App\Models\Service\GestiunePiesa;
use App\Models\Service\Masina;
use App\Models\Service\MasinaServiceEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\DomCrawler\Crawler;
use Tests\TestCase;

class GestiunePieseTest extends TestCase
{
    use RefreshDatabase;

    public function test_page_is_accessible_even_without_legacy_table(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('gestiune-piese.index'));

        $response->assertOk();
        $response->assertSee('Gestiune piese');
        $response->assertSee('service_gestiune_piese nu sunt disponibile');
    }

    public function test_stock_details_include_machine_allocations(): void
    {
        $user = User::factory()->create();

        $piece = GestiunePiesa::factory()->create([
            'cantitate_initiala' => 10,
            'nr_bucati' => 4,
        ]);

        $machine = Masina::factory()->create([
            'numar_inmatriculare' => 'CJ01AAA',
            'denumire' => 'Masina A',
        ]);

        MasinaServiceEntry::factory()->create([
            'gestiune_piesa_id' => $piece->id,
            'masina_id' => $machine->id,
            'tip' => 'piesa',
            'cantitate' => 3.5,
            'data_montaj' => '2024-01-05',
        ]);

        MasinaServiceEntry::factory()->create([
            'gestiune_piesa_id' => $piece->id,
            'masina_id' => $machine->id,
            'tip' => 'piesa',
            'cantitate' => 2.0,
            'data_montaj' => '2024-02-15',
        ]);

        $response = $this->actingAs($user)->get(route('gestiune-piese.index'));

        $response->assertOk();
        $response->assertSee('Detalii');

        $crawler = new Crawler($response->getContent());
        $button = $crawler->filter('button[data-piece-machines]')->first();

        $machinesJson = $button->attr('data-piece-machines');
        $machines = json_decode($machinesJson, true, 512, JSON_THROW_ON_ERROR);

        $this->assertIsArray($machines);
        $this->assertCount(2, $machines);
        $this->assertSame([$machine->id, $machine->id], array_column($machines, 'masina_id'));
        $this->assertSame(['CJ01AAA', 'CJ01AAA'], array_column($machines, 'numar_inmatriculare'));
        $this->assertSame(['Masina A', 'Masina A'], array_column($machines, 'denumire'));
        $this->assertEquals([2.0, 3.5], array_column($machines, 'cantitate'));
        $this->assertSame(['15.02.2024', '05.01.2024'], array_column($machines, 'data'));
    }

    public function test_non_mechanic_can_create_piece(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('gestiune-piese.store'), [
            'denumire' => 'Filtru ulei',
            'cod' => 'FU-001',
            'cantitate_initiala' => 6,
            'nr_bucati' => 2,
            'pret' => 25.5,
            'tva_cota' => 19,
            'pret_brut' => 30.35,
        ]);

        $response->assertRedirect(route('gestiune-piese.index'));

        $this->assertDatabaseHas('service_gestiune_piese', [
            'denumire' => 'Filtru ulei',
            'cod' => 'FU-001',
            'cantitate_initiala' => 6,
            'nr_bucati' => 6,
        ]);
    }

    public function test_non_mechanic_can_update_piece(): void
    {
        $user = User::factory()->create();
        $piece = GestiunePiesa::factory()->create([
            'denumire' => 'Plăcuțe frână',
            'nr_bucati' => 8,
            'cantitate_initiala' => 8,
        ]);

        $response = $this->actingAs($user)->put(route('gestiune-piese.update', $piece), [
            'denumire' => 'Plăcuțe frână față',
            'cod' => 'PF-100',
            'cantitate_initiala' => 10,
            'nr_bucati' => 4,
            'pret' => 120,
            'tva_cota' => 19,
            'pret_brut' => 142.8,
        ]);

        $response->assertRedirect(route('gestiune-piese.index'));

        $this->assertDatabaseHas('service_gestiune_piese', [
            'id' => $piece->id,
            'denumire' => 'Plăcuțe frână față',
            'cod' => 'PF-100',
            'cantitate_initiala' => 10,
            'nr_bucati' => 10,
        ]);
    }

    public function test_nr_bucati_is_calculated_based_on_usage_when_updating_piece(): void
    {
        $user = User::factory()->create();
        $piece = GestiunePiesa::factory()->create([
            'denumire' => 'Bucșe',
            'cantitate_initiala' => 10,
            'nr_bucati' => 7,
        ]);

        $machine = Masina::factory()->create();

        MasinaServiceEntry::factory()->create([
            'gestiune_piesa_id' => $piece->id,
            'masina_id' => $machine->id,
            'tip' => 'piesa',
            'cantitate' => 3,
        ]);

        $response = $this->actingAs($user)->put(route('gestiune-piese.update', $piece), [
            'denumire' => 'Bucșe față',
            'cantitate_initiala' => 12,
            'nr_bucati' => 1,
        ]);

        $response->assertRedirect(route('gestiune-piese.index'));

        $this->assertDatabaseHas('service_gestiune_piese', [
            'id' => $piece->id,
            'denumire' => 'Bucșe față',
            'cantitate_initiala' => 12,
            'nr_bucati' => 9,
        ]);
    }

    public function test_cannot_set_initial_quantity_below_used_amount(): void
    {
        $user = User::factory()->create();
        $piece = GestiunePiesa::factory()->create([
            'denumire' => 'Amortizor',
            'cantitate_initiala' => 8,
            'nr_bucati' => 4,
        ]);

        MasinaServiceEntry::factory()->count(2)->create([
            'gestiune_piesa_id' => $piece->id,
            'tip' => 'piesa',
            'cantitate' => 2,
        ]);

        $response = $this->actingAs($user)->from(route('gestiune-piese.edit', $piece))
            ->put(route('gestiune-piese.update', $piece), [
                'denumire' => 'Amortizor spate',
                'cantitate_initiala' => 3,
            ]);

        $response->assertRedirect(route('gestiune-piese.edit', $piece));
        $response->assertSessionHasErrors('cantitate_initiala');

        $this->assertDatabaseHas('service_gestiune_piese', [
            'id' => $piece->id,
            'denumire' => 'Amortizor',
            'cantitate_initiala' => 8,
            'nr_bucati' => 4,
        ]);
    }

    public function test_non_mechanic_can_delete_piece_without_entries(): void
    {
        $user = User::factory()->create();
        $piece = GestiunePiesa::factory()->create();

        $response = $this->actingAs($user)->delete(route('gestiune-piese.destroy', $piece));

        $response->assertRedirect(route('gestiune-piese.index'));
        $this->assertDatabaseMissing('service_gestiune_piese', ['id' => $piece->id]);
    }

    public function test_cannot_delete_piece_with_entries(): void
    {
        $user = User::factory()->create();
        $piece = GestiunePiesa::factory()->create(['nr_bucati' => 5]);

        MasinaServiceEntry::factory()->create([
            'gestiune_piesa_id' => $piece->id,
            'tip' => 'piesa',
            'cantitate' => 1,
        ]);

        $response = $this->actingAs($user)->delete(route('gestiune-piese.destroy', $piece));

        $response->assertRedirect(route('gestiune-piese.edit', $piece));
        $response->assertSessionHasErrors('general');
        $this->assertDatabaseHas('service_gestiune_piese', ['id' => $piece->id]);
    }

    public function test_mechanic_cannot_access_mutating_routes(): void
    {
        $mechanicRole = Role::firstOrCreate(
            ['slug' => 'mecanic'],
            [
                'name' => 'Mecanic',
                'description' => 'Acces limitat la gestiune piese și service mașini.',
            ]
        );

        $mechanic = User::factory()->create(['role' => $mechanicRole->id]);
        $mechanic->assignRole($mechanicRole);

        $piece = GestiunePiesa::factory()->create();

        $this->actingAs($mechanic)->get(route('gestiune-piese.create'))->assertForbidden();
        $this->actingAs($mechanic)->post(route('gestiune-piese.store'), [
            'denumire' => 'Bec stop',
        ])->assertForbidden();
        $this->actingAs($mechanic)->get(route('gestiune-piese.edit', $piece))->assertForbidden();
        $this->actingAs($mechanic)->put(route('gestiune-piese.update', $piece), [
            'denumire' => 'Bec stop',
        ])->assertForbidden();
        $this->actingAs($mechanic)->delete(route('gestiune-piese.destroy', $piece))->assertForbidden();
    }
}
