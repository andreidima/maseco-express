<?php

namespace Tests\Feature\Valabilitati;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Models\Valabilitate;
use App\Models\ValabilitateCursa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ValabilitateDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_html_request_cannot_delete_valabilitate_with_curse(): void
    {
        $user = $this->createValabilitatiUser();
        $valabilitate = Valabilitate::factory()
            ->has(ValabilitateCursa::factory(), 'curse')
            ->create();

        $response = $this->actingAs($user)->delete(route('valabilitati.destroy', $valabilitate));

        $response->assertRedirect(route('valabilitati.index'));
        $response->assertSessionHas('error', 'Valabilitatea nu poate fi ștearsă deoarece are curse asociate.');
        $this->assertDatabaseHas('valabilitati', ['id' => $valabilitate->id]);
    }

    public function test_json_request_cannot_delete_valabilitate_with_curse(): void
    {
        $user = $this->createValabilitatiUser();
        $valabilitate = Valabilitate::factory()
            ->has(ValabilitateCursa::factory(), 'curse')
            ->create();

        $response = $this->actingAs($user)->deleteJson(route('valabilitati.destroy', $valabilitate));

        $response->assertStatus(422);
        $response->assertJson([
            'message' => 'Valabilitatea nu poate fi ștearsă deoarece are curse asociate.',
            'errors' => [
                'curse' => ['Valabilitatea nu poate fi ștearsă deoarece are curse asociate.'],
            ],
        ]);
        $this->assertDatabaseHas('valabilitati', ['id' => $valabilitate->id]);
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
