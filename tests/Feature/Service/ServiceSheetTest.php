<?php

namespace Tests\Feature\Service;

use App\Models\Service\Masina;
use App\Models\Service\ServiceSheet;
use App\Models\Service\ServiceSheetItem;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class ServiceSheetTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_service_sheet_store_persists_and_redirects_to_index(): void
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
                    'descriere' => 'Schimb ulei motor',
                ],
                [
                    'descriere' => 'Filtru aer',
                ],
            ],
        ]);

        $response->assertRedirect(route('service-masini.index', [
            'masina_id' => $masina->id,
            'view' => 'service-sheets',
        ]));

        $this->assertDatabaseHas('service_sheets', [
            'masina_id' => $masina->id,
            'km_bord' => 125000,
            'data_service' => '2024-05-01',
        ]);

        $sheet = ServiceSheet::query()->where('masina_id', $masina->id)->firstOrFail();

        $this->assertDatabaseHas('service_sheet_items', [
            'service_sheet_id' => $sheet->id,
            'position' => 1,
            'descriere' => 'Schimb ulei motor',
        ]);

        $this->assertDatabaseHas('service_sheet_items', [
            'service_sheet_id' => $sheet->id,
            'position' => 2,
            'descriere' => 'Filtru aer',
        ]);

        $sheet->items()->delete();

        $sheet->items()->createMany([
            ['position' => 2, 'descriere' => 'Element poziția 2'],
            ['position' => 3, 'descriere' => 'Element poziția 3'],
            ['position' => 1, 'descriere' => 'Element poziția 1'],
        ]);

        $expectedDescriptions = $sheet->items()->orderBy('position')->pluck('descriere')->all();

        $pdfMock = new class
        {
            public function stream(string $filename, array $options = [])
            {
                return response('pdf-content', 200, array_merge([
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'inline; filename="' . $filename . '"',
                ], $options));
            }
        };

        Pdf::shouldReceive('loadView')
            ->once()
            ->with('pdf.service-sheet', Mockery::on(function (array $data) use ($expectedDescriptions) {
                $items = collect($data['items']);

                return $items->pluck('descriere')->all() === $expectedDescriptions
                    && $items->pluck('index')->all() === range(1, count($expectedDescriptions));
            }))
            ->andReturn($pdfMock);

        $downloadResponse = $this->actingAs($user)->get(route('service-masini.sheet.download', [$masina, $sheet]));

        $downloadResponse->assertOk();
        $this->assertSame('application/pdf', $downloadResponse->headers->get('content-type'));
        $this->assertStringContainsString('foaie-service-b12xyz', strtolower($downloadResponse->headers->get('content-disposition')));
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
            ['position' => 1, 'descriere' => 'Inițial 1'],
            ['position' => 2, 'descriere' => 'Inițial 2'],
        )->create();

        $updateResponse = $this->actingAs($user)->put(route('service-masini.sheet.update', [$masina, $sheet]), [
            'km_bord' => 2500,
            'data_service' => '2024-02-20',
            'items' => [
                [
                    'descriere' => 'Revizie completă',
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
            'descriere' => 'Inițial 1',
        ]);

        $this->assertDatabaseHas('service_sheet_items', [
            'service_sheet_id' => $sheet->id,
            'position' => 1,
            'descriere' => 'Revizie completă',
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
