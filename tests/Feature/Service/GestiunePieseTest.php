<?php

namespace Tests\Feature\Service;

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
}
