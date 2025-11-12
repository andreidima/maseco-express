<?php

namespace Tests\Feature\Valabilitati;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Models\Tara;
use App\Models\Valabilitate;
use App\Models\ValabilitateCursa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ValabilitateCursaTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_store_cursa_with_split_fields(): void
    {
        $user = $this->createValabilitatiUser();
        $valabilitate = Valabilitate::factory()->create();
        $incarcareTara = Tara::factory()->create(['nume' => 'România']);
        $descarcareTara = Tara::factory()->create(['nume' => 'Ungaria']);

        $response = $this->actingAs($user)->post(route('valabilitati.curse.store', $valabilitate), [
            'incarcare_localitate' => 'Cluj-Napoca',
            'incarcare_cod_postal' => '400000',
            'incarcare_tara_id' => $incarcareTara->id,
            'incarcare_tara_text' => $incarcareTara->nume,
            'descarcare_localitate' => 'Oradea',
            'descarcare_cod_postal' => '410001',
            'descarcare_tara_id' => $descarcareTara->id,
            'descarcare_tara_text' => $descarcareTara->nume,
            'data_cursa' => '2025-05-01T08:30',
            'observatii' => 'Livrare completă',
            'km_bord' => 12345,
        ]);

        $response->assertRedirect(route('valabilitati.curse.index', $valabilitate));
        $response->assertSessionHas('status', 'Cursa a fost adăugată cu succes.');

        $this->assertDatabaseHas('valabilitati_curse', [
            'valabilitate_id' => $valabilitate->id,
            'incarcare_localitate' => 'Cluj-Napoca',
            'incarcare_cod_postal' => '400000',
            'incarcare_tara_id' => $incarcareTara->id,
            'descarcare_localitate' => 'Oradea',
            'descarcare_cod_postal' => '410001',
            'descarcare_tara_id' => $descarcareTara->id,
            'data_cursa' => '2025-05-01 08:30:00',
            'observatii' => 'Livrare completă',
            'km_bord' => 12345,
        ]);

        $cursa = ValabilitateCursa::firstOrFail();
        $this->assertInstanceOf(Carbon::class, $cursa->data_cursa);
        $this->assertSame('2025-05-01 08:30:00', $cursa->data_cursa->format('Y-m-d H:i:s'));
    }

    public function test_user_can_update_cursa_fields(): void
    {
        $user = $this->createValabilitatiUser();
        $initialIncarcareTara = Tara::factory()->create(['nume' => 'Franța']);
        $initialDescarcareTara = Tara::factory()->create(['nume' => 'Italia']);
        $cursa = ValabilitateCursa::factory()->create([
            'incarcare_localitate' => 'Brașov',
            'incarcare_cod_postal' => '500100',
            'incarcare_tara_id' => $initialIncarcareTara->id,
            'descarcare_localitate' => 'Sibiu',
            'descarcare_cod_postal' => '550200',
            'descarcare_tara_id' => $initialDescarcareTara->id,
            'data_cursa' => '2025-05-03 10:00:00',
            'km_bord' => 78000,
        ]);

        $newIncarcareTara = Tara::factory()->create(['nume' => 'Germania']);
        $newDescarcareTara = Tara::factory()->create(['nume' => 'Polonia']);

        $response = $this->actingAs($user)->put(route('valabilitati.curse.update', [$cursa->valabilitate, $cursa]), [
            'incarcare_localitate' => 'Iași',
            'incarcare_cod_postal' => '700505',
            'incarcare_tara_id' => $newIncarcareTara->id,
            'incarcare_tara_text' => $newIncarcareTara->nume,
            'descarcare_localitate' => 'Galați',
            'descarcare_cod_postal' => '800010',
            'descarcare_tara_id' => $newDescarcareTara->id,
            'descarcare_tara_text' => $newDescarcareTara->nume,
            'data_cursa' => '2025-05-05T14:45',
            'observatii' => 'Actualizare detalii',
            'km_bord' => 98765,
        ]);

        $response->assertRedirect(route('valabilitati.curse.index', $cursa->valabilitate));
        $response->assertSessionHas('status', 'Cursa a fost actualizată.');

        $this->assertDatabaseHas('valabilitati_curse', [
            'id' => $cursa->id,
            'incarcare_localitate' => 'Iași',
            'incarcare_cod_postal' => '700505',
            'incarcare_tara_id' => $newIncarcareTara->id,
            'descarcare_localitate' => 'Galați',
            'descarcare_cod_postal' => '800010',
            'descarcare_tara_id' => $newDescarcareTara->id,
            'data_cursa' => '2025-05-05 14:45:00',
            'observatii' => 'Actualizare detalii',
            'km_bord' => 98765,
        ]);
    }

    public function test_user_can_delete_cursa(): void
    {
        $user = $this->createValabilitatiUser();
        $cursa = ValabilitateCursa::factory()->create();

        $response = $this->actingAs($user)->delete(route('valabilitati.curse.destroy', [$cursa->valabilitate, $cursa]));

        $response->assertRedirect(route('valabilitati.curse.index', $cursa->valabilitate));
        $response->assertSessionHas('status', 'Cursa a fost ștearsă.');
        $this->assertDatabaseMissing('valabilitati_curse', ['id' => $cursa->id]);
    }

    public function test_index_view_displays_fields_and_datetime(): void
    {
        $user = $this->createValabilitatiUser();
        $valabilitate = Valabilitate::factory()->create();
        $incarcareTara = Tara::factory()->create(['nume' => 'Austria']);
        $descarcareTara = Tara::factory()->create(['nume' => 'Cehia']);
        $cursa = ValabilitateCursa::factory()->for($valabilitate)->create([
            'incarcare_localitate' => 'Timișoara',
            'incarcare_cod_postal' => '300001',
            'incarcare_tara_id' => $incarcareTara->id,
            'descarcare_localitate' => 'Arad',
            'descarcare_cod_postal' => '310002',
            'data_cursa' => '2025-06-10 16:20:00',
            'descarcare_tara_id' => $descarcareTara->id,
            'km_bord' => 15400,
        ]);

        $response = $this->actingAs($user)->get(route('valabilitati.curse.index', $valabilitate));

        $response->assertOk();
        $response->assertSeeText('Timișoara');
        $response->assertSeeText('300001');
        $response->assertSeeText($incarcareTara->nume);
        $response->assertSeeText('Arad');
        $response->assertSeeText('310002');
        $response->assertSeeText($descarcareTara->nume);
        $response->assertSeeText('10.06.2025 16:20');
        $response->assertSeeText('15400');
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
