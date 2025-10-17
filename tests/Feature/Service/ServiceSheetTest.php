<?php

namespace Tests\Feature\Service;

use App\Models\Service\Masina;
use App\Models\Service\ServiceSheet;
use App\Models\Service\ServiceSheetItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceSheetTest extends TestCase
{
    use RefreshDatabase;

    public function test_service_sheet_store_persists_and_downloads_pdf(): void
    {
        $user = User::factory()->create();
        $masina = Masina::factory()->create([
            'denumire' => 'Autoutilitara Test',
            'numar_inmatriculare' => 'B12XYZ',
        ]);

        $response = $this->actingAs($user)->post(route('service-masini.sheet.store', $masina), [
            'km_bord' => 125000,
            'data_service' => '2024-05-01',
            'items' => [
                [
                    'description' => 'Schimb ulei motor',
                    'quantity' => '1',
                    'notes' => 'Manoperă 1h',
                ],
                [
                    'description' => 'Filtru aer',
                    'quantity' => '1',
                    'notes' => '',
                ],
            ],
        ]);

        $response->assertOk();
        $this->assertSame('application/pdf', $response->headers->get('content-type'));
        $this->assertStringContainsString('foaie-service-b12xyz', strtolower($response->headers->get('content-disposition')));

        $this->assertDatabaseHas('service_sheets', [
            'masina_id' => $masina->id,
            'km_bord' => 125000,
            'data_service' => '2024-05-01',
        ]);

        $sheet = ServiceSheet::query()->where('masina_id', $masina->id)->firstOrFail();

        $this->assertDatabaseHas('service_sheet_items', [
            'service_sheet_id' => $sheet->id,
            'position' => 1,
            'description' => 'Schimb ulei motor',
            'quantity' => '1',
            'notes' => 'Manoperă 1h',
        ]);

        $this->assertDatabaseHas('service_sheet_items', [
            'service_sheet_id' => $sheet->id,
            'position' => 2,
            'description' => 'Filtru aer',
            'quantity' => '1',
            'notes' => null,
        ]);
    }

    public function test_service_sheet_index_view_lists_records(): void
    {
        $user = User::factory()->create();
        $masina = Masina::factory()->create();
        $sheet = ServiceSheet::factory()
            ->for($masina)
            ->create([
                'km_bord' => 23456,
                'data_service' => '2024-04-15',
            ]);

        ServiceSheetItem::factory()->count(3)->for($sheet)->sequence(
            ['position' => 1],
            ['position' => 2],
            ['position' => 3],
        )->create();

        $response = $this->actingAs($user)->get(route('service-masini.index', [
            'masina_id' => $masina->id,
            'view' => 'service-sheets',
        ]));

        $response->assertOk();
        $response->assertSee('Foi service');
        $response->assertSee('15.04.2024');
        $response->assertSee('23,456');
        $response->assertSee('3');
    }

    public function test_service_sheet_can_be_updated_and_deleted(): void
    {
        $user = User::factory()->create();
        $masina = Masina::factory()->create();
        $sheet = ServiceSheet::factory()
            ->for($masina)
            ->create([
                'km_bord' => 1000,
                'data_service' => '2024-01-10',
            ]);

        ServiceSheetItem::factory()->count(2)->for($sheet)->sequence(
            ['position' => 1, 'description' => 'Inițial 1'],
            ['position' => 2, 'description' => 'Inițial 2'],
        )->create();

        $updateResponse = $this->actingAs($user)->put(route('service-masini.sheet.update', [$masina, $sheet]), [
            'km_bord' => 2500,
            'data_service' => '2024-02-20',
            'items' => [
                [
                    'description' => 'Revizie completă',
                    'quantity' => '2',
                    'notes' => 'Include filtre',
                ],
            ],
        ]);

        $updateResponse->assertRedirect(route('service-masini.index', [
            'masina_id' => $masina->id,
            'view' => 'service-sheets',
        ]));

        $this->assertDatabaseHas('service_sheets', [
            'id' => $sheet->id,
            'km_bord' => 2500,
            'data_service' => '2024-02-20',
        ]);

        $this->assertDatabaseMissing('service_sheet_items', [
            'service_sheet_id' => $sheet->id,
            'description' => 'Inițial 1',
        ]);

        $this->assertDatabaseHas('service_sheet_items', [
            'service_sheet_id' => $sheet->id,
            'position' => 1,
            'description' => 'Revizie completă',
            'quantity' => '2',
            'notes' => 'Include filtre',
        ]);

        $deleteResponse = $this->actingAs($user)->delete(route('service-masini.sheet.destroy', [$masina, $sheet]));
        $deleteResponse->assertRedirect(route('service-masini.index', [
            'masina_id' => $masina->id,
            'view' => 'service-sheets',
        ]));

        $this->assertDatabaseMissing('service_sheets', [
            'id' => $sheet->id,
        ]);
        $this->assertDatabaseMissing('service_sheet_items', [
            'service_sheet_id' => $sheet->id,
        ]);
    }
}
