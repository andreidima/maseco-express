<?php

namespace Tests\Feature\Driver;

use App\Models\Role;
use App\Models\Tara;
use App\Models\User;
use App\Models\Valabilitate;
use App\Models\ValabilitateCursa;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DriverValabilitateTest extends TestCase
{
    use RefreshDatabase;

    public function test_driver_routes_require_soferi_role(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->get('/driver')->assertStatus(403);

        $driver = User::factory()->create();
        $this->assignDriverRole($driver);

        $this->actingAs($driver);

        $this->get('/driver')->assertOk();
    }

    public function test_active_valabilitati_are_filtered_for_driver(): void
    {
        Carbon::setTestNow('2025-04-01');

        $driver = User::factory()->create();
        $this->assignDriverRole($driver);

        $active = Valabilitate::factory()->create([
            'sofer_id' => $driver->id,
            'data_inceput' => now()->subDay()->toDateString(),
            'data_sfarsit' => now()->addWeek()->toDateString(),
        ]);

        Valabilitate::factory()->create([
            'sofer_id' => $driver->id,
            'data_inceput' => now()->addDay()->toDateString(),
            'data_sfarsit' => now()->addDays(10)->toDateString(),
        ]);

        Valabilitate::factory()->create(); // another driver

        $this->actingAs($driver);

        $response = $this->getJson(route('driver.api.valabilitati.index'));

        $response
            ->assertOk()
            ->assertJsonCount(1, 'valabilitati')
            ->assertJsonPath('valabilitati.0.id', $active->id);

        $this->getJson(route('driver.api.valabilitati.show', $active))
            ->assertOk()
            ->assertJsonPath('valabilitate.id', $active->id);
    }

    public function test_first_cursa_requires_time(): void
    {
        Carbon::setTestNow('2025-04-01');

        $driver = User::factory()->create();
        $this->assignDriverRole($driver);

        $valabilitate = Valabilitate::factory()->create([
            'sofer_id' => $driver->id,
            'data_inceput' => now()->subDay()->toDateString(),
            'data_sfarsit' => now()->addDays(3)->toDateString(),
        ]);

        $tara = Tara::factory()->create();

        $payload = [
            'incarcare_localitate' => 'Cluj',
            'incarcare_cod_postal' => '12345',
            'incarcare_tara_id' => $tara->id,
            'descarcare_localitate' => 'Arad',
            'descarcare_cod_postal' => '67890',
            'descarcare_tara_id' => $tara->id,
            'data_cursa_date' => now()->toDateString(),
            'data_cursa_time' => '',
        ];

        $this->actingAs($driver);

        $this->postJson(route('driver.api.valabilitati.curse.store', $valabilitate), $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['data_cursa_time']);

        $payload['data_cursa_time'] = '08:30';

        $this->postJson(route('driver.api.valabilitati.curse.store', $valabilitate), $payload)
            ->assertOk()
            ->assertJsonPath('valabilitate.curse.0.data_time', '08:30');
    }

    public function test_romania_descarcare_requires_time(): void
    {
        Carbon::setTestNow('2025-04-01');

        $driver = User::factory()->create();
        $this->assignDriverRole($driver);

        $romania = Tara::factory()->create(['nume' => 'Romania']);
        $germany = Tara::factory()->create(['nume' => 'Germania']);

        $valabilitate = Valabilitate::factory()->create([
            'sofer_id' => $driver->id,
            'data_inceput' => now()->subDay()->toDateString(),
            'data_sfarsit' => now()->addDays(3)->toDateString(),
        ]);

        ValabilitateCursa::factory()->create([
            'valabilitate_id' => $valabilitate->id,
            'incarcare_tara_id' => $germany->id,
            'descarcare_tara_id' => $germany->id,
            'data_cursa' => now()->subDay()->setTime(7, 0),
        ]);

        $payload = [
            'incarcare_localitate' => 'Berlin',
            'incarcare_cod_postal' => '10115',
            'incarcare_tara_id' => $germany->id,
            'descarcare_localitate' => 'Cluj',
            'descarcare_cod_postal' => '400000',
            'descarcare_tara_id' => $romania->id,
            'data_cursa_date' => now()->toDateString(),
            'data_cursa_time' => '',
        ];

        $this->actingAs($driver);

        $this->postJson(route('driver.api.valabilitati.curse.store', $valabilitate), $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['data_cursa_time']);

        $payload['data_cursa_time'] = '10:15';

        $this->postJson(route('driver.api.valabilitati.curse.store', $valabilitate), $payload)
            ->assertOk()
            ->assertJsonPath('valabilitate.curse.1.data_time', '10:15');
    }

    private function assignDriverRole(User $user): void
    {
        $role = Role::query()->firstOrCreate(
            ['slug' => 'soferi'],
            ['name' => 'Șoferi']
        );

        $user->roles()->attach($role);
    }
}
