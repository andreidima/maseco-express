<?php

namespace Tests\Feature\Sofer;

use App\Models\Role;
use App\Models\Tara;
use App\Models\User;
use App\Models\Valabilitate;
use App\Models\ValabilitateCursa;
use App\Models\ValabilitateCursaStop;
use App\Models\ValabilitatiDivizie;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class SoferValabilitateCursaTest extends TestCase
{
    use RefreshDatabase;

    public function test_driver_cannot_view_valabilitate_of_another_driver(): void
    {
        $driver = $this->createSoferUser();
        $otherDriver = $this->createSoferUser();

        $valabilitate = Valabilitate::factory()
            ->for($driver, 'sofer')
            ->create([
                'data_inceput' => Carbon::today()->subWeek(),
                'data_sfarsit' => Carbon::today()->addWeek(),
            ]);

        $response = $this->actingAs($otherDriver)->get(route('sofer.valabilitati.show', $valabilitate));

        $response->assertForbidden();
    }

    public function test_first_cursa_requires_datetime(): void
    {
        $driver = $this->createSoferUser();
        $valabilitate = Valabilitate::factory()
            ->for($driver, 'sofer')
            ->create([
                'data_inceput' => Carbon::today()->subWeek(),
                'data_sfarsit' => Carbon::today()->addWeek(),
            ]);

        $descarcareTara = Tara::factory()->create(['nume' => 'Germania']);

        $response = $this
            ->actingAs($driver)
            ->from(route('sofer.valabilitati.show', $valabilitate))
            ->post(route('sofer.valabilitati.curse.store', $valabilitate), [
                'form_type' => 'create',
                'incarcare_localitate' => 'Cluj',
                'descarcare_localitate' => 'Berlin',
                'descarcare_tara_id' => $descarcareTara->id,
                'data_cursa' => '',
            ]);

        $response->assertRedirect(route('sofer.valabilitati.show', $valabilitate));
        $response->assertSessionHasErrors(['data_cursa']);
        $this->assertDatabaseCount('valabilitati_curse', 0);
    }

    public function test_first_cursa_persists_when_datetime_is_provided(): void
    {
        $driver = $this->createSoferUser();
        $valabilitate = Valabilitate::factory()
            ->for($driver, 'sofer')
            ->create([
                'data_inceput' => Carbon::today()->subWeek(),
                'data_sfarsit' => Carbon::today()->addWeek(),
            ]);

        $tara = Tara::factory()->create(['nume' => 'Ungaria']);

        $response = $this
            ->actingAs($driver)
            ->post(route('sofer.valabilitati.curse.store', $valabilitate), [
                'form_type' => 'create',
                'incarcare_localitate' => 'Cluj',
                'descarcare_localitate' => 'Budapesta',
                'descarcare_tara_id' => $tara->id,
                'data_cursa' => '2025-05-20T08:15',
            ]);

        $response->assertRedirect(route('sofer.valabilitati.show', $valabilitate));
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('valabilitati_curse', [
            'valabilitate_id' => $valabilitate->id,
            'data_cursa' => '2025-05-20 08:15:00',
        ]);
    }

    public function test_flash_valabilitate_hides_kilometer_fields_in_form(): void
    {
        $driver = $this->createSoferUser();
        $flashDivizie = ValabilitatiDivizie::factory()->create([
            'id' => 1,
            'nume' => 'FLASH',
        ]);

        $valabilitate = Valabilitate::factory()
            ->for($driver, 'sofer')
            ->for($flashDivizie, 'divizie')
            ->create([
                'data_inceput' => Carbon::today()->subWeek(),
                'data_sfarsit' => Carbon::today()->addWeek(),
            ]);

        $response = $this->actingAs($driver)->get(route('sofer.valabilitati.curse.create', $valabilitate));

        $response->assertOk();
        $response->assertDontSee('Km bord încărcare', false);
        $response->assertDontSee('Km bord descărcare', false);
    }

    public function test_non_flash_valabilitate_displays_kilometer_fields_in_form(): void
    {
        $driver = $this->createSoferUser();
        $divizie = ValabilitatiDivizie::factory()->create([
            'id' => 2,
            'nume' => 'ALTĂ DIVIZIE',
        ]);

        $valabilitate = Valabilitate::factory()
            ->for($driver, 'sofer')
            ->for($divizie, 'divizie')
            ->create([
                'data_inceput' => Carbon::today()->subWeek(),
                'data_sfarsit' => Carbon::today()->addWeek(),
            ]);

        $response = $this->actingAs($driver)->get(route('sofer.valabilitati.curse.create', $valabilitate));

        $response->assertOk();
        $response->assertSee('Km bord încărcare', false);
        $response->assertSee('Km bord descărcare', false);
    }

    public function test_flash_valabilitate_hides_kilometer_rows_on_show_page(): void
    {
        $driver = $this->createSoferUser();
        $flashDivizie = ValabilitatiDivizie::factory()->create([
            'id' => 1,
            'nume' => 'FLASH',
        ]);

        $valabilitate = Valabilitate::factory()
            ->for($driver, 'sofer')
            ->for($flashDivizie, 'divizie')
            ->create([
                'data_inceput' => Carbon::today()->subWeek(),
                'data_sfarsit' => Carbon::today()->addWeek(),
            ]);

        ValabilitateCursa::factory()
            ->for($valabilitate)
            ->create([
                'nr_ordine' => 1,
                'km_bord_incarcare' => 123,
                'km_bord_descarcare' => 456,
                'data_cursa' => '2025-05-01 07:00:00',
            ]);

        $response = $this->actingAs($driver)->get(route('sofer.valabilitati.show', $valabilitate));

        $response->assertOk();
        $response->assertSee('01.05.2025 07:00', false);
        $response->assertDontSee('Km bord încărcare', false);
        $response->assertDontSee('Km bord descărcare', false);
    }

    public function test_driver_can_reorder_curse(): void
    {
        $driver = $this->createSoferUser();
        $valabilitate = Valabilitate::factory()
            ->for($driver, 'sofer')
            ->create([
                'data_inceput' => Carbon::today()->subWeek(),
                'data_sfarsit' => Carbon::today()->addWeek(),
            ]);

        $first = ValabilitateCursa::factory()->for($valabilitate)->create(['nr_ordine' => 1]);
        $second = ValabilitateCursa::factory()->for($valabilitate)->create(['nr_ordine' => 2]);

        $response = $this
            ->actingAs($driver)
            ->patch(route('sofer.valabilitati.curse.reorder', [$valabilitate, $first]), [
                'direction' => 'down',
            ]);

        $response->assertRedirect(route('sofer.valabilitati.show', $valabilitate));
        $response->assertSessionHas('status', 'Ordinea cursei a fost actualizată.');

        $this->assertDatabaseHas('valabilitati_curse', [
            'id' => $first->id,
            'nr_ordine' => 2,
        ]);

        $this->assertDatabaseHas('valabilitati_curse', [
            'id' => $second->id,
            'nr_ordine' => 1,
        ]);
    }

    public function test_driver_gets_feedback_when_reorder_is_not_possible(): void
    {
        $driver = $this->createSoferUser();
        $valabilitate = Valabilitate::factory()
            ->for($driver, 'sofer')
            ->create([
                'data_inceput' => Carbon::today()->subWeek(),
                'data_sfarsit' => Carbon::today()->addWeek(),
            ]);

        $onlyCursa = ValabilitateCursa::factory()->for($valabilitate)->create(['nr_ordine' => 1]);

        $response = $this
            ->actingAs($driver)
            ->patch(route('sofer.valabilitati.curse.reorder', [$valabilitate, $onlyCursa]), [
                'direction' => 'up',
            ]);

        $response->assertRedirect(route('sofer.valabilitati.show', $valabilitate));
        $response->assertSessionHas('status', 'Această cursă este deja prima în listă.');

        $this->assertDatabaseHas('valabilitati_curse', [
            'id' => $onlyCursa->id,
            'nr_ordine' => 1,
        ]);
    }

    public function test_driver_can_create_flash_cursa_with_ordered_stops(): void
    {
        $driver = $this->createSoferUser();
        $flashDivizie = ValabilitatiDivizie::factory()->create([
            'id' => 1,
            'nume' => 'FLASH',
        ]);

        $valabilitate = Valabilitate::factory()
            ->for($driver, 'sofer')
            ->for($flashDivizie, 'divizie')
            ->create([
                'data_inceput' => Carbon::today()->subWeek(),
                'data_sfarsit' => Carbon::today()->addWeek(),
            ]);

        $response = $this
            ->actingAs($driver)
            ->post(route('sofer.valabilitati.curse.store', $valabilitate), [
                'form_type' => 'create',
                'incarcare_localitate' => 'Cluj',
                'descarcare_localitate' => 'Berlin',
                'data_cursa' => '2025-05-20T08:15',
                'stops' => [
                    ['type' => 'incarcare', 'localitate' => 'Punct B', 'cod_postal' => '400222', 'position' => 2],
                    ['type' => 'incarcare', 'localitate' => 'Punct A', 'cod_postal' => '400111', 'position' => 1],
                    ['type' => 'descarcare', 'localitate' => 'Punct D', 'cod_postal' => '010222', 'position' => 2],
                    ['type' => 'descarcare', 'localitate' => 'Punct C', 'cod_postal' => '010111', 'position' => 1],
                ],
            ]);

        $response->assertRedirect(route('sofer.valabilitati.show', $valabilitate));
        $response->assertSessionHasNoErrors();

        $cursa = ValabilitateCursa::first();

        $this->assertDatabaseHas('valabilitate_cursa_stops', [
            'valabilitate_cursa_id' => $cursa?->id,
            'type' => 'incarcare',
            'localitate' => 'Punct A',
            'position' => 1,
        ]);

        $this->assertDatabaseHas('valabilitate_cursa_stops', [
            'valabilitate_cursa_id' => $cursa?->id,
            'type' => 'descarcare',
            'localitate' => 'Punct C',
            'position' => 1,
        ]);
    }

    public function test_flash_cursa_stops_are_shown_in_order(): void
    {
        $driver = $this->createSoferUser();
        $flashDivizie = ValabilitatiDivizie::factory()->create([
            'id' => 1,
            'nume' => 'FLASH',
        ]);

        $valabilitate = Valabilitate::factory()
            ->for($driver, 'sofer')
            ->for($flashDivizie, 'divizie')
            ->create([
                'data_inceput' => Carbon::today()->subWeek(),
                'data_sfarsit' => Carbon::today()->addWeek(),
            ]);

        $cursa = ValabilitateCursa::factory()->for($valabilitate)->create();

        ValabilitateCursaStop::factory()->create([
            'valabilitate_cursa_id' => $cursa->id,
            'type' => 'incarcare',
            'localitate' => 'Punct B',
            'cod_postal' => '400222',
            'position' => 2,
        ]);

        ValabilitateCursaStop::factory()->create([
            'valabilitate_cursa_id' => $cursa->id,
            'type' => 'incarcare',
            'localitate' => 'Punct A',
            'cod_postal' => '400111',
            'position' => 1,
        ]);

        ValabilitateCursaStop::factory()->create([
            'valabilitate_cursa_id' => $cursa->id,
            'type' => 'descarcare',
            'localitate' => 'Punct D',
            'cod_postal' => '010222',
            'position' => 2,
        ]);

        ValabilitateCursaStop::factory()->create([
            'valabilitate_cursa_id' => $cursa->id,
            'type' => 'descarcare',
            'localitate' => 'Punct C',
            'cod_postal' => '010111',
            'position' => 1,
        ]);

        $response = $this->actingAs($driver)->get(route('sofer.valabilitati.show', $valabilitate));

        $response->assertOk();
        $response->assertSeeInOrder(['Punct A', 'Punct B']);
        $response->assertSeeInOrder(['Punct C', 'Punct D']);
    }

    public function test_driver_can_update_flash_cursa_stops(): void
    {
        $driver = $this->createSoferUser();
        $flashDivizie = ValabilitatiDivizie::factory()->create([
            'id' => 1,
            'nume' => 'FLASH',
        ]);

        $valabilitate = Valabilitate::factory()
            ->for($driver, 'sofer')
            ->for($flashDivizie, 'divizie')
            ->create([
                'data_inceput' => Carbon::today()->subWeek(),
                'data_sfarsit' => Carbon::today()->addWeek(),
            ]);

        $cursa = ValabilitateCursa::factory()->for($valabilitate)->create([
            'nr_ordine' => 1,
        ]);

        ValabilitateCursaStop::factory()->create([
            'valabilitate_cursa_id' => $cursa->id,
            'type' => 'incarcare',
            'localitate' => 'Veche',
            'position' => 1,
        ]);

        $response = $this
            ->actingAs($driver)
            ->put(route('sofer.valabilitati.curse.update', [$valabilitate, $cursa]), [
                'form_type' => 'edit',
                'incarcare_localitate' => 'Cluj',
                'descarcare_localitate' => 'Berlin',
                'data_cursa' => '2025-05-21T10:00',
                'stops' => [
                    ['type' => 'incarcare', 'localitate' => 'Nouă', 'cod_postal' => null, 'position' => 1],
                    ['type' => 'descarcare', 'localitate' => 'Berlin', 'cod_postal' => '10115', 'position' => 1],
                ],
            ]);

        $response->assertRedirect(route('sofer.valabilitati.show', $valabilitate));

        $this->assertDatabaseMissing('valabilitate_cursa_stops', [
            'valabilitate_cursa_id' => $cursa->id,
            'localitate' => 'Veche',
        ]);

        $this->assertDatabaseHas('valabilitate_cursa_stops', [
            'valabilitate_cursa_id' => $cursa->id,
            'type' => 'incarcare',
            'localitate' => 'Nouă',
            'position' => 1,
        ]);
    }

    public function test_non_flash_cursa_ignores_stop_payload(): void
    {
        $driver = $this->createSoferUser();
        $divizie = ValabilitatiDivizie::factory()->create([
            'id' => 2,
            'nume' => 'ALTĂ DIVIZIE',
        ]);

        $valabilitate = Valabilitate::factory()
            ->for($driver, 'sofer')
            ->for($divizie, 'divizie')
            ->create([
                'data_inceput' => Carbon::today()->subWeek(),
                'data_sfarsit' => Carbon::today()->addWeek(),
            ]);

        $response = $this
            ->actingAs($driver)
            ->post(route('sofer.valabilitati.curse.store', $valabilitate), [
                'form_type' => 'create',
                'incarcare_localitate' => 'Cluj',
                'descarcare_localitate' => 'Budapesta',
                'data_cursa' => '2025-05-21T10:00',
                'stops' => [
                    ['type' => 'incarcare', 'localitate' => 'Punct A', 'cod_postal' => '400111', 'position' => 1],
                ],
            ]);

        $response->assertRedirect(route('sofer.valabilitati.show', $valabilitate));

        $this->assertDatabaseCount('valabilitati_curse', 1);
        $this->assertDatabaseCount('valabilitate_cursa_stops', 0);
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
