<?php

namespace Tests\Feature\Valabilitati;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Models\Valabilitate;
use App\Models\ValabilitateTaxaDrum;
use App\Models\ValabilitatiDivizie;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ValabilitateRoadTaxesTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_store_valabilitate_with_road_taxes_via_json(): void
    {
        $user = $this->createValabilitatiUser();
        $driver = User::factory()->create();
        $divizie = ValabilitatiDivizie::factory()->create(['nume' => 'Divizie Taxe']);

        $payload = [
            'numar_auto' => 'B-99-XYZ',
            'sofer_id' => $driver->id,
            'divizie_id' => $divizie->id,
            'data_inceput' => '2025-03-01',
            'data_sfarsit' => '2025-03-31',
            'taxe_drum' => [
                [
                    'nume' => 'Rovinietă România',
                    'tara' => 'România',
                    'suma' => '123.45',
                    'moneda' => 'RON',
                    'data' => '2025-03-05',
                    'observatii' => 'Taxă vignetă',
                ],
                [
                    'nume' => 'Vignetă Ungaria',
                    'tara' => 'Ungaria',
                    'suma' => '67,89',
                    'moneda' => 'eur',
                    'data' => '2025-03-10',
                    'observatii' => '',
                ],
            ],
        ];

        $response = $this->actingAs($user)->postJson(route('valabilitati.store'), $payload);

        $response->assertOk();
        $response->assertJsonPath('valabilitate.taxe_drum.0.nume', 'Rovinietă România');
        $response->assertJsonPath('valabilitate.taxe_drum.0.tara', 'România');
        $response->assertJsonPath('valabilitate.taxe_drum.0.suma', '123.45');
        $response->assertJsonPath('valabilitate.taxe_drum.0.moneda', 'RON');
        $response->assertJsonPath('valabilitate.taxe_drum.1.nume', 'Vignetă Ungaria');
        $response->assertJsonPath('valabilitate.taxe_drum.1.moneda', 'EUR');
        $response->assertJsonPath('valabilitate.taxe_drum.1.suma', '67.89');
        $response->assertJsonPath('valabilitate.taxe_drum.1.observatii', null);

        $valabilitateId = $response->json('valabilitate.id');
        $this->assertNotNull($valabilitateId);

        $this->assertDatabaseHas('valabilitati_taxe_drum', [
            'valabilitate_id' => $valabilitateId,
            'nume' => 'Rovinietă România',
            'tara' => 'România',
            'suma' => '123.45',
            'moneda' => 'RON',
            'observatii' => 'Taxă vignetă',
        ]);

        $this->assertDatabaseHas('valabilitati_taxe_drum', [
            'valabilitate_id' => $valabilitateId,
            'nume' => 'Vignetă Ungaria',
            'tara' => 'Ungaria',
            'suma' => '67.89',
            'moneda' => 'EUR',
            'observatii' => null,
        ]);
    }

    public function test_update_replaces_existing_road_taxes(): void
    {
        $user = $this->createValabilitatiUser();

        $valabilitate = Valabilitate::factory()->create();
        ValabilitateTaxaDrum::factory()->count(2)->create([
            'valabilitate_id' => $valabilitate->id,
        ]);

        $payload = [
            'numar_auto' => $valabilitate->numar_auto,
            'sofer_id' => $valabilitate->sofer_id,
            'divizie_id' => $valabilitate->divizie_id,
            'data_inceput' => $valabilitate->data_inceput?->format('Y-m-d') ?? '2025-03-01',
            'data_sfarsit' => $valabilitate->data_sfarsit?->format('Y-m-d'),
            'taxe_drum' => [
                [
                    'nume' => 'Taxă Bulgaria',
                    'tara' => 'Bulgaria',
                    'suma' => '45.50',
                    'moneda' => 'BGN',
                    'data' => '2025-04-12',
                    'observatii' => 'Noul tarif',
                ],
            ],
        ];

        $response = $this->actingAs($user)->putJson(route('valabilitati.update', $valabilitate), $payload);

        $response->assertOk();
        $response->assertJsonCount(1, 'valabilitate.taxe_drum');
        $response->assertJsonPath('valabilitate.taxe_drum.0.nume', 'Taxă Bulgaria');
        $response->assertJsonPath('valabilitate.taxe_drum.0.tara', 'Bulgaria');
        $response->assertJsonPath('valabilitate.taxe_drum.0.suma', '45.50');

        $this->assertDatabaseHas('valabilitati_taxe_drum', [
            'valabilitate_id' => $valabilitate->id,
            'nume' => 'Taxă Bulgaria',
            'tara' => 'Bulgaria',
            'suma' => '45.50',
            'moneda' => 'BGN',
            'observatii' => 'Noul tarif',
        ]);

        $this->assertEquals(1, ValabilitateTaxaDrum::where('valabilitate_id', $valabilitate->id)->count());
    }

    public function test_update_can_remove_all_road_taxes(): void
    {
        $user = $this->createValabilitatiUser();

        $valabilitate = Valabilitate::factory()->create();
        ValabilitateTaxaDrum::factory()->count(2)->create([
            'valabilitate_id' => $valabilitate->id,
        ]);

        $payload = [
            'numar_auto' => $valabilitate->numar_auto,
            'sofer_id' => $valabilitate->sofer_id,
            'divizie_id' => $valabilitate->divizie_id,
            'data_inceput' => $valabilitate->data_inceput?->format('Y-m-d') ?? '2025-03-01',
            'data_sfarsit' => $valabilitate->data_sfarsit?->format('Y-m-d'),
            'taxe_drum' => [],
        ];

        $response = $this->actingAs($user)->putJson(route('valabilitati.update', $valabilitate), $payload);

        $response->assertOk();
        $response->assertJsonCount(0, 'valabilitate.taxe_drum');

        $this->assertDatabaseMissing('valabilitati_taxe_drum', [
            'valabilitate_id' => $valabilitate->id,
        ]);
    }

    private function createValabilitatiUser(): User
    {
        $user = User::factory()->create();

        $permission = Permission::create([
            'name' => 'Valabilități',
            'slug' => 'access-valabilitati',
            'module' => 'valabilitati',
        ]);

        $role = Role::create([
            'name' => 'Administrator',
            'slug' => 'admin',
        ]);

        $role->permissions()->syncWithoutDetaching([$permission->id]);
        $user->assignRole($role);

        return $user;
    }
}
