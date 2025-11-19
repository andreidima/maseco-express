<?php

namespace Tests\Feature\Sofer;

use App\Models\Role;
use App\Models\Tara;
use App\Models\User;
use App\Models\Valabilitate;
use App\Models\ValabilitateCursa;
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
