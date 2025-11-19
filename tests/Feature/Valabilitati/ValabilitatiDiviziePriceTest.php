<?php

namespace Tests\Feature\Valabilitati;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Models\ValabilitatiDivizie;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ValabilitatiDiviziePriceTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_fetch_divizie_prices(): void
    {
        $user = $this->createValabilitatiUser();
        $divizie = ValabilitatiDivizie::factory()->create([
            'nume' => 'Divizie Test',
            'pret_km_gol' => 1.234,
            'pret_km_plin' => 2.345,
            'pret_km_cu_taxa' => 3.456,
        ]);

        $response = $this->actingAs($user)->getJson(route('valabilitati.divizii.show', $divizie));

        $response->assertOk();
        $response->assertJsonPath('divizie.id', $divizie->id);
        $response->assertJsonPath('divizie.nume', 'Divizie Test');
        $response->assertJsonPath('divizie.pret_km_gol', '1.234');
        $response->assertJsonPath('divizie.pret_km_plin', '2.345');
        $response->assertJsonPath('divizie.pret_km_cu_taxa', '3.456');
    }

    public function test_user_can_update_divizie_prices(): void
    {
        $user = $this->createValabilitatiUser();
        $divizie = ValabilitatiDivizie::factory()->create([
            'pret_km_gol' => null,
            'pret_km_plin' => null,
            'pret_km_cu_taxa' => null,
        ]);

        $payload = [
            'pret_km_gol' => '10.123',
            'pret_km_plin' => '20.456',
            'pret_km_cu_taxa' => '30.789',
        ];

        $response = $this->actingAs($user)->putJson(route('valabilitati.divizii.update', $divizie), $payload);

        $response->assertOk();
        $response->assertJsonPath('divizie.pret_km_gol', '10.123');
        $response->assertJsonPath('divizie.pret_km_plin', '20.456');
        $response->assertJsonPath('divizie.pret_km_cu_taxa', '30.789');

        $this->assertDatabaseHas('valabilitati_divizii', [
            'id' => $divizie->id,
            'pret_km_gol' => 10.123,
            'pret_km_plin' => 20.456,
            'pret_km_cu_taxa' => 30.789,
        ]);
    }

    public function test_update_requires_numeric_values(): void
    {
        $user = $this->createValabilitatiUser();
        $divizie = ValabilitatiDivizie::factory()->create();

        $response = $this->actingAs($user)->putJson(route('valabilitati.divizii.update', $divizie), [
            'pret_km_gol' => 'invalid',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['pret_km_gol']);
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
