<?php

namespace Tests\Feature\Sofer;

use App\Models\Role;
use App\Models\User;
use App\Models\Valabilitate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class SoferDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_shows_active_valabilitate_without_legacy_section(): void
    {
        $driver = $this->createSoferUser();

        $valabilitate = Valabilitate::factory()
            ->for($driver, 'sofer')
            ->create([
                'data_inceput' => Carbon::today()->subWeek(),
                'data_sfarsit' => Carbon::today()->addWeek(),
                'numar_auto' => 'CJ01ABC',
                'denumire' => 'ITP',
            ]);

        $response = $this->actingAs($driver)->get(route('sofer.dashboard'));

        $response->assertOk();
        $response->assertSee('Valabilități active', false);
        $response->assertSee($valabilitate->numar_auto, false);
        $response->assertDontSee('Istoric valabilități flota', false);
    }

    public function test_dashboard_handles_missing_active_valabilitate(): void
    {
        $driver = $this->createSoferUser();

        $response = $this->actingAs($driver)->get(route('sofer.dashboard'));

        $response->assertOk();
        $response->assertSee('Nu aveți nicio valabilitate activă', false);
        $response->assertDontSee('Istoric valabilități flota', false);
    }

    private function createSoferUser(): User
    {
        $role = Role::firstOrCreate(
            ['slug' => 'sofer'],
            ['name' => 'Șofer']
        );

        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }
}
