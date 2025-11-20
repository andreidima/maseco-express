<?php

namespace Tests\Feature\Valabilitati;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Models\Valabilitate;
use App\Models\ValabilitatiDivizie;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ValabilitateFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_shows_only_active_valabilitati_by_default(): void
    {
        $user = $this->createValabilitatiUser();

        $activeDivizie = ValabilitatiDivizie::factory()->create(['nume' => 'În lucru']);
        $finishedDivizie = ValabilitatiDivizie::factory()->create(['nume' => 'Finalizată']);

        $active = Valabilitate::factory()->create([
            'divizie_id' => $activeDivizie->id,
            'data_sfarsit' => null,
        ]);

        Valabilitate::factory()->create([
            'divizie_id' => $finishedDivizie->id,
            'data_sfarsit' => '2025-02-12',
        ]);

        $response = $this->actingAs($user)->get(route('valabilitati.index'));

        $response->assertOk();
        $response->assertSeeText($activeDivizie->nume);
        $response->assertDontSeeText($finishedDivizie->nume);
        $this->assertTrue($active->fresh()->data_sfarsit === null);
    }

    public function test_can_filter_finished_valabilitati(): void
    {
        $user = $this->createValabilitatiUser();

        $finishedDivizie = ValabilitatiDivizie::factory()->create(['nume' => 'Terminate']);
        $activeDivizie = ValabilitatiDivizie::factory()->create(['nume' => 'Active']);

        $finished = Valabilitate::factory()->create([
            'divizie_id' => $finishedDivizie->id,
            'data_sfarsit' => '2025-03-20',
        ]);

        Valabilitate::factory()->create([
            'divizie_id' => $activeDivizie->id,
            'data_sfarsit' => null,
        ]);

        $response = $this->actingAs($user)->get(route('valabilitati.index', [
            'status' => 'finished',
        ]));

        $response->assertOk();
        $response->assertSeeText($finishedDivizie->nume);
        $response->assertDontSeeText($activeDivizie->nume);
        $this->assertNotNull($finished->fresh()->data_sfarsit);
    }

    public function test_can_list_all_valabilitati_when_requested(): void
    {
        $user = $this->createValabilitatiUser();

        $finishedDivizie = ValabilitatiDivizie::factory()->create(['nume' => 'Listată']);
        $activeDivizie = ValabilitatiDivizie::factory()->create(['nume' => 'Tot listată']);

        Valabilitate::factory()->create([
            'divizie_id' => $finishedDivizie->id,
            'data_sfarsit' => '2025-04-01',
        ]);

        Valabilitate::factory()->create([
            'divizie_id' => $activeDivizie->id,
            'data_sfarsit' => null,
        ]);

        $response = $this->actingAs($user)->get(route('valabilitati.index', [
            'status' => 'all',
        ]));

        $response->assertOk();
        $response->assertSeeText($finishedDivizie->nume);
        $response->assertSeeText($activeDivizie->nume);
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
