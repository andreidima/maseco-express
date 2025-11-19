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
            'data_cursa_date' => '2025-05-01',
            'data_cursa_time' => '08:30',
            'observatii' => 'Livrare completă',
            'km_bord_incarcare' => 12345,
            'km_bord_descarcare' => 12500,
            'km_maps' => '150',
            'km_maps_gol' => 50,
            'km_maps_plin' => 100,
            'km_cu_taxa' => 25,
            'km_flash_gol' => 48,
            'km_flash_plin' => 98,
        ]);

        $response->assertRedirect(route('valabilitati.curse.index', $valabilitate));
        $response->assertSessionHas('status', 'Cursa a fost adăugată cu succes.');

        $this->assertDatabaseHas('valabilitati_curse', [
            'valabilitate_id' => $valabilitate->id,
            'nr_ordine' => 1,
            'incarcare_localitate' => 'Cluj-Napoca',
            'incarcare_cod_postal' => '400000',
            'incarcare_tara_id' => $incarcareTara->id,
            'descarcare_localitate' => 'Oradea',
            'descarcare_cod_postal' => '410001',
            'descarcare_tara_id' => $descarcareTara->id,
            'data_cursa' => '2025-05-01 08:30:00',
            'observatii' => 'Livrare completă',
            'km_bord_incarcare' => 12345,
            'km_bord_descarcare' => 12500,
            'km_maps' => '150',
            'km_maps_gol' => 50,
            'km_maps_plin' => 100,
            'km_cu_taxa' => 25,
            'km_flash_gol' => 48,
            'km_flash_plin' => 98,
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
            'nr_ordine' => 1,
            'incarcare_localitate' => 'Brașov',
            'incarcare_cod_postal' => '500100',
            'incarcare_tara_id' => $initialIncarcareTara->id,
            'descarcare_localitate' => 'Sibiu',
            'descarcare_cod_postal' => '550200',
            'descarcare_tara_id' => $initialDescarcareTara->id,
            'data_cursa' => '2025-05-03 10:00:00',
            'km_bord_incarcare' => 78000,
            'km_bord_descarcare' => 78500,
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
            'data_cursa_date' => '2025-05-05',
            'data_cursa_time' => '14:45',
            'observatii' => 'Actualizare detalii',
            'km_bord_incarcare' => 98765,
            'km_bord_descarcare' => 99000,
            'km_maps' => '300',
            'km_maps_gol' => 120,
            'km_maps_plin' => 180,
            'km_cu_taxa' => 60,
            'km_flash_gol' => 110,
            'km_flash_plin' => 175,
        ]);

        $response->assertRedirect(route('valabilitati.curse.index', $cursa->valabilitate));
        $response->assertSessionHas('status', 'Cursa a fost actualizată.');

        $this->assertDatabaseHas('valabilitati_curse', [
            'id' => $cursa->id,
            'nr_ordine' => 1,
            'incarcare_localitate' => 'Iași',
            'incarcare_cod_postal' => '700505',
            'incarcare_tara_id' => $newIncarcareTara->id,
            'descarcare_localitate' => 'Galați',
            'descarcare_cod_postal' => '800010',
            'descarcare_tara_id' => $newDescarcareTara->id,
            'data_cursa' => '2025-05-05 14:45:00',
            'observatii' => 'Actualizare detalii',
            'km_bord_incarcare' => 98765,
            'km_bord_descarcare' => 99000,
            'km_maps' => '300',
            'km_maps_gol' => 120,
            'km_maps_plin' => 180,
            'km_cu_taxa' => 60,
            'km_flash_gol' => 110,
            'km_flash_plin' => 175,
        ]);
    }

    public function test_ajax_update_returns_json_payload(): void
    {
        $user = $this->createValabilitatiUser();
        $cursa = ValabilitateCursa::factory()->create([
            'nr_ordine' => 3,
            'data_cursa' => '2025-05-02 09:00:00',
        ]);
        $newIncarcareTara = Tara::factory()->create(['nume' => 'Austria']);
        $newDescarcareTara = Tara::factory()->create(['nume' => 'Cehia']);

        $response = $this
            ->actingAs($user)
            ->withHeaders([
                'X-Requested-With' => 'XMLHttpRequest',
                'Accept' => 'application/json',
            ])
            ->put(route('valabilitati.curse.update', [$cursa->valabilitate, $cursa]), [
                'incarcare_localitate' => 'București',
                'incarcare_cod_postal' => '010101',
                'incarcare_tara_id' => $newIncarcareTara->id,
                'descarcare_localitate' => 'Praga',
                'descarcare_cod_postal' => '11000',
                'descarcare_tara_id' => $newDescarcareTara->id,
                'data_cursa_date' => '2025-05-10',
                'data_cursa_time' => '11:15',
            ]);

        $response->assertOk();
        $response->assertJsonStructure([
            'message',
            'table_html',
            'modals_html',
            'next_url',
        ]);
        $response->assertJson([
            'message' => 'Cursa a fost actualizată.',
        ]);

        $this->assertDatabaseHas('valabilitati_curse', [
            'id' => $cursa->id,
            'nr_ordine' => 3,
            'incarcare_localitate' => 'București',
            'descarcare_localitate' => 'Praga',
            'data_cursa' => '2025-05-10 11:15:00',
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
            'nr_ordine' => 7,
            'incarcare_localitate' => 'Timișoara',
            'incarcare_cod_postal' => '300001',
            'incarcare_tara_id' => $incarcareTara->id,
            'descarcare_localitate' => 'Arad',
            'descarcare_cod_postal' => '310002',
            'data_cursa' => '2025-06-10 16:20:00',
            'descarcare_tara_id' => $descarcareTara->id,
            'km_bord_incarcare' => 15400,
            'km_bord_descarcare' => 15900,
        ]);

        $response = $this->actingAs($user)->get(route('valabilitati.curse.index', $valabilitate));

        $response->assertOk();
        $response->assertSeeText('Timișoara');
        $response->assertSeeText('300001');
        $response->assertSeeText($incarcareTara->nume);
        $response->assertSeeText('Arad');
        $response->assertSeeText('310002');
        $response->assertSeeText((string) $cursa->nr_ordine);
        $response->assertSeeText($descarcareTara->nume);
        $response->assertSeeText('10.06.2025 16:20');
        $response->assertSeeText('15400');
        $response->assertSeeText('15900');
    }

    public function test_index_lists_curse_in_chronological_order(): void
    {
        $user = $this->createValabilitatiUser();
        $valabilitate = Valabilitate::factory()->create();

        ValabilitateCursa::factory()->for($valabilitate)->create([
            'data_cursa' => '2025-05-10 09:30:00',
        ]);

        ValabilitateCursa::factory()->for($valabilitate)->create([
            'data_cursa' => '2025-05-01 08:00:00',
        ]);

        ValabilitateCursa::factory()->for($valabilitate)->create([
            'data_cursa' => '2025-05-05 12:00:00',
        ]);

        $response = $this->actingAs($user)->get(route('valabilitati.curse.index', $valabilitate));

        $response->assertOk();
        $response->assertSeeInOrder([
            '01.05.2025 08:00',
            '05.05.2025 12:00',
            '10.05.2025 09:30',
        ]);
    }

    public function test_user_can_move_cursa_up(): void
    {
        $user = $this->createValabilitatiUser();
        $valabilitate = Valabilitate::factory()->create();

        $first = ValabilitateCursa::factory()->for($valabilitate)->create(['nr_ordine' => 1]);
        $second = ValabilitateCursa::factory()->for($valabilitate)->create(['nr_ordine' => 2]);
        $third = ValabilitateCursa::factory()->for($valabilitate)->create(['nr_ordine' => 3]);

        $response = $this
            ->actingAs($user)
            ->patch(route('valabilitati.curse.reorder', [$valabilitate, $third]), [
                'direction' => 'up',
            ]);

        $response->assertRedirect(route('valabilitati.curse.index', $valabilitate));
        $response->assertSessionHas('status', 'Ordinea cursei a fost actualizată.');

        $this->assertDatabaseHas('valabilitati_curse', [
            'id' => $third->id,
            'nr_ordine' => 2,
        ]);

        $this->assertDatabaseHas('valabilitati_curse', [
            'id' => $second->id,
            'nr_ordine' => 3,
        ]);

        $this->assertDatabaseHas('valabilitati_curse', [
            'id' => $first->id,
            'nr_ordine' => 1,
        ]);
    }

    public function test_user_can_move_cursa_down(): void
    {
        $user = $this->createValabilitatiUser();
        $valabilitate = Valabilitate::factory()->create();

        $first = ValabilitateCursa::factory()->for($valabilitate)->create(['nr_ordine' => 1]);
        $second = ValabilitateCursa::factory()->for($valabilitate)->create(['nr_ordine' => 2]);
        $third = ValabilitateCursa::factory()->for($valabilitate)->create(['nr_ordine' => 3]);

        $response = $this
            ->actingAs($user)
            ->patch(route('valabilitati.curse.reorder', [$valabilitate, $first]), [
                'direction' => 'down',
            ]);

        $response->assertRedirect(route('valabilitati.curse.index', $valabilitate));
        $response->assertSessionHas('status', 'Ordinea cursei a fost actualizată.');

        $this->assertDatabaseHas('valabilitati_curse', [
            'id' => $first->id,
            'nr_ordine' => 2,
        ]);

        $this->assertDatabaseHas('valabilitati_curse', [
            'id' => $second->id,
            'nr_ordine' => 1,
        ]);

        $this->assertDatabaseHas('valabilitati_curse', [
            'id' => $third->id,
            'nr_ordine' => 3,
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
