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

class ValabilitateRoadTaxesFrontendTest extends TestCase
{
    use RefreshDatabase;

    public function test_edit_modal_prefills_existing_road_taxes(): void
    {
        $user = $this->createValabilitatiUser();

        $divizie = ValabilitatiDivizie::factory()->create(['nume' => 'Valabilitate modal']);
        $valabilitate = Valabilitate::factory()->create([
            'divizie_id' => $divizie->id,
        ]);

        ValabilitateTaxaDrum::factory()->create([
            'valabilitate_id' => $valabilitate->id,
            'nume' => 'Rovinietă',
            'tara' => 'România',
            'suma' => 123.45,
            'moneda' => 'RON',
            'data' => '2025-03-01',
            'observatii' => 'Vignetă',
        ]);

        ValabilitateTaxaDrum::factory()->create([
            'valabilitate_id' => $valabilitate->id,
            'nume' => 'Autostradă Austria',
            'tara' => 'Austria',
            'suma' => 78.9,
            'moneda' => 'HUF',
            'data' => '2025-03-05',
            'observatii' => 'Autostradă',
        ]);

        $response = $this->actingAs($user)->get(route('valabilitati.index'));

        $response->assertOk();
        $response->assertSee('Adaugă taxă de drum', false);
        $response->assertSee('name="taxe_drum[0][nume]"', false);
        $response->assertSee('value="Rovinietă"', false);
        $response->assertSee('name="taxe_drum[0][tara]"', false);
        $response->assertSee('value="România"', false);
        $response->assertSee('value="123.45"', false);
        $response->assertSee('value="RON"', false);
        $response->assertSee('value="2025-03-01"', false);
        $response->assertSee('Vignetă', false);
        $response->assertSee('name="taxe_drum[1][nume]"', false);
        $response->assertSee('value="Autostradă Austria"', false);
        $response->assertSee('name="taxe_drum[1][tara]"', false);
        $response->assertSee('value="Austria"', false);
        $response->assertSee('value="HUF"', false);
        $response->assertSee('value="2025-03-05"', false);
    }

    public function test_show_page_displays_road_taxes_table(): void
    {
        $user = $this->createValabilitatiUser();

        $divizie = ValabilitatiDivizie::factory()->create(['nume' => 'Valabilitate vizualizare']);
        $valabilitate = Valabilitate::factory()->create([
            'divizie_id' => $divizie->id,
        ]);

        ValabilitateTaxaDrum::factory()->create([
            'valabilitate_id' => $valabilitate->id,
            'nume' => 'Rovinietă',
            'tara' => 'România',
            'suma' => 123.45,
            'moneda' => 'RON',
            'data' => '2025-03-01',
            'observatii' => 'Vignetă',
        ]);

        ValabilitateTaxaDrum::factory()->create([
            'valabilitate_id' => $valabilitate->id,
            'nume' => 'Vignetă Ungaria',
            'tara' => 'Ungaria',
            'suma' => 67.89,
            'moneda' => 'EUR',
            'data' => '2025-03-10',
            'observatii' => null,
        ]);

        $response = $this->actingAs($user)->get(route('valabilitati.show', $valabilitate));

        $response->assertOk();
        $response->assertSeeText('Taxe de drum');
        $response->assertSeeText('Rovinietă');
        $response->assertSeeText('România');
        $response->assertSeeText('Vignetă Ungaria');
        $response->assertSeeText('Ungaria');
        $response->assertSeeText('123,45');
        $response->assertSeeText('67,89');
        $response->assertSeeText('RON');
        $response->assertSeeText('EUR');
        $response->assertDontSeeText('Nu există taxe de drum înregistrate.');
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
