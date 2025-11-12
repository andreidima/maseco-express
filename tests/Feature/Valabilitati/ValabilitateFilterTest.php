<?php

namespace Tests\Feature\Valabilitati;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Models\Valabilitate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ValabilitateFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_filters_by_inceput_and_sfarsit_ranges(): void
    {
        $user = $this->createValabilitatiUser();

        $matching = Valabilitate::factory()->create([
            'denumire' => 'Valabilitate potrivită',
            'data_inceput' => '2025-01-15',
            'data_sfarsit' => '2025-02-12',
        ]);

        Valabilitate::factory()->create([
            'denumire' => 'Înainte de interval',
            'data_inceput' => '2025-01-05',
            'data_sfarsit' => '2025-02-12',
        ]);

        Valabilitate::factory()->create([
            'denumire' => 'După interval',
            'data_inceput' => '2025-01-18',
            'data_sfarsit' => '2025-02-25',
        ]);

        $response = $this->actingAs($user)->get(route('valabilitati.index', [
            'inceput_start' => '2025-01-10',
            'inceput_end' => '2025-01-20',
            'sfarsit_start' => '2025-02-05',
            'sfarsit_end' => '2025-02-20',
        ]));

        $response->assertOk();
        $response->assertSeeText($matching->denumire);
        $response->assertDontSeeText('Înainte de interval');
        $response->assertDontSeeText('După interval');
    }

    public function test_legacy_interval_parameters_are_supported(): void
    {
        $user = $this->createValabilitatiUser();

        $inside = Valabilitate::factory()->create([
            'denumire' => 'Compatibil Legacy',
            'data_inceput' => '2025-03-10',
            'data_sfarsit' => '2025-03-20',
        ]);

        Valabilitate::factory()->create([
            'denumire' => 'În afara Legacy',
            'data_inceput' => '2025-03-25',
            'data_sfarsit' => '2025-03-28',
        ]);

        $response = $this->actingAs($user)->get(route('valabilitati.index', [
            'interval_start' => '2025-03-05',
            'interval_end' => '2025-03-15',
        ]));

        $response->assertOk();
        $response->assertSeeText($inside->denumire);
        $response->assertDontSeeText('În afara Legacy');
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
