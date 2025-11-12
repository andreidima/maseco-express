<?php

namespace Tests\Feature\Sofer;

use App\Models\Role;
use App\Models\Tara;
use App\Models\User;
use App\Models\Valabilitate;
use App\Models\ValabilitateCursa;
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

    public function test_first_cursa_requires_time(): void
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
                'data_cursa_date' => Carbon::today()->format('Y-m-d'),
            ]);

        $response->assertRedirect(route('sofer.valabilitati.show', $valabilitate));
        $response->assertSessionHasErrors(['data_cursa_time']);
        $this->assertDatabaseCount('valabilitati_curse', 0);
    }

    public function test_first_cursa_persists_when_time_is_provided(): void
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
                'data_cursa_date' => '2025-05-20',
                'data_cursa_time' => '08:15',
            ]);

        $response->assertRedirect(route('sofer.valabilitati.show', $valabilitate));
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('valabilitati_curse', [
            'valabilitate_id' => $valabilitate->id,
            'data_cursa' => '2025-05-20 08:15:00',
        ]);
    }

    public function test_final_return_requires_time(): void
    {
        $driver = $this->createSoferUser();
        $valabilitate = Valabilitate::factory()
            ->for($driver, 'sofer')
            ->create([
                'data_inceput' => Carbon::today()->subWeek(),
                'data_sfarsit' => Carbon::today()->addWeek(),
            ]);

        ValabilitateCursa::factory()->for($valabilitate)->create();

        $romania = Tara::factory()->create(['nume' => 'Romania']);

        $response = $this
            ->actingAs($driver)
            ->from(route('sofer.valabilitati.show', $valabilitate))
            ->post(route('sofer.valabilitati.curse.store', $valabilitate), [
                'form_type' => 'create',
                'descarcare_tara_id' => $romania->id,
                'data_cursa_date' => '2025-06-10',
                'final_return' => 1,
            ]);

        $response->assertRedirect(route('sofer.valabilitati.show', $valabilitate));
        $response->assertSessionHasErrors(['data_cursa_time']);
        $this->assertDatabaseCount('valabilitati_curse', 1);
    }

    public function test_update_requires_time_when_marked_as_final_return(): void
    {
        $driver = $this->createSoferUser();
        $valabilitate = Valabilitate::factory()
            ->for($driver, 'sofer')
            ->create([
                'data_inceput' => Carbon::today()->subWeek(),
                'data_sfarsit' => Carbon::today()->addWeek(),
            ]);

        $cursa = ValabilitateCursa::factory()->for($valabilitate)->create([
            'data_cursa' => '2025-05-01 07:00:00',
        ]);

        $romania = Tara::factory()->create(['nume' => 'România']);

        $response = $this
            ->actingAs($driver)
            ->from(route('sofer.valabilitati.show', $valabilitate))
            ->put(route('sofer.valabilitati.curse.update', [$valabilitate, $cursa]), [
                'form_type' => 'edit',
                'form_id' => $cursa->id,
                'descarcare_tara_id' => $romania->id,
                'data_cursa_date' => '2025-05-02',
                'final_return' => 1,
            ]);

        $response->assertRedirect(route('sofer.valabilitati.show', $valabilitate));
        $response->assertSessionHasErrors(['data_cursa_time']);

        $this->assertDatabaseHas('valabilitati_curse', [
            'id' => $cursa->id,
            'data_cursa' => '2025-05-01 07:00:00',
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
