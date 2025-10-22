<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_mechanic_navigation_updates_with_new_permissions(): void
    {
        $mechanicRole = $this->ensureRoleWithDefaults(
            'mecanic',
            'Mecanic',
            'Acces limitat la gestiunea pieselor și service-ul mașinilor.'
        );

        $mechanic = $this->createUserWithRole($mechanicRole);

        $response = $this->actingAs($mechanic)->get(route('gestiune-piese.index'));

        $response->assertOk();
        $response->assertSee('href="' . route('gestiune-piese.index') . '"', false);
        $response->assertSee('href="' . route('service-masini.index') . '"', false);
        $response->assertDontSee('href="/comenzi"', false);
        $response->assertDontSee('href="/file-manager-personalizat"', false);

        $comenziPermission = Permission::where('module', 'comenzi')->firstOrFail();

        $mechanic->syncPermissions([$comenziPermission->id]);

        $updatedResponse = $this->actingAs($mechanic->fresh())->get(route('gestiune-piese.index'));

        $updatedResponse->assertOk();
        $updatedResponse->assertSee('href="/comenzi"', false);
        $updatedResponse->assertDontSee('href="/file-manager-personalizat"', false);
    }

    private function ensureRoleWithDefaults(string $slug, string $name, string $description): Role
    {
        $role = Role::firstOrCreate(
            ['slug' => $slug],
            [
                'name' => $name,
                'description' => $description,
            ]
        );

        $this->syncRoleDefaults($role);

        return $role;
    }

    private function createUserWithRole(Role $role, array $attributes = []): User
    {
        $user = User::factory()->create($attributes);
        $user->assignRole($role);

        return $user->fresh();
    }

    private function syncRoleDefaults(Role $role): void
    {
        $defaults = config('permissions.role_defaults', []);
        $modules = $defaults[$role->slug] ?? [];

        if (in_array('*', $modules, true)) {
            $role->syncPermissions(Permission::all());

            return;
        }

        if (empty($modules)) {
            $role->syncPermissions([]);

            return;
        }

        $role->syncPermissions(Permission::whereIn('module', $modules)->get());
    }
}
