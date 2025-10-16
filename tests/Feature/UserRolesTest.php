<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserRolesTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_management_access_is_limited_to_admin_roles(): void
    {
        [$superAdminRole, $adminRole, $mechanicRole] = $this->createCoreRoles();

        $admin = User::factory()->create(['role' => $adminRole->id]);
        $admin->assignRole($adminRole);

        $superAdmin = User::factory()->create(['role' => $superAdminRole->id]);
        $superAdmin->assignRole($superAdminRole);

        $mechanic = User::factory()->create(['role' => $mechanicRole->id]);
        $mechanic->assignRole($mechanicRole);

        $this->actingAs($admin)->get('/utilizatori')->assertOk();
        $this->actingAs($superAdmin)->get('/utilizatori')->assertOk();
        $this->actingAs($mechanic)->get('/utilizatori')->assertForbidden();
    }

    public function test_super_admin_role_cannot_be_assigned_through_user_forms(): void
    {
        [$superAdminRole, $adminRole] = $this->createCoreRoles();

        $admin = User::factory()->create(['role' => $adminRole->id]);
        $admin->assignRole($adminRole);

        $response = $this->actingAs($admin)->post('/utilizatori', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'telefon' => '0123456789',
            'role' => $superAdminRole->id,
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'activ' => 1,
        ]);

        $response->assertSessionHasErrors('role');
        $this->assertDatabaseMissing('users', ['email' => 'test@example.com']);

        $user = User::factory()->create([
            'name' => 'Existing User',
            'email' => 'existing@example.com',
            'role' => $adminRole->id,
        ]);
        $user->assignRole($adminRole);

        $updateResponse = $this->actingAs($admin)->put("/utilizatori/{$user->id}", [
            'id' => $user->id,
            'name' => 'Existing User',
            'email' => 'existing@example.com',
            'telefon' => '0123456789',
            'role' => $superAdminRole->id,
            'activ' => 1,
        ]);

        $updateResponse->assertSessionHasErrors('role');
        $this->assertFalse($user->fresh()->hasRole('super-admin'));
    }

    public function test_legacy_super_admin_without_pivot_role_can_access_user_management(): void
    {
        [$superAdminRole, $adminRole] = $this->createCoreRoles();

        $legacySuperAdmin = User::factory()->create([
            'role' => $superAdminRole->id,
        ]);

        $this->actingAs($legacySuperAdmin)->get('/utilizatori')->assertOk();
    }

    public function test_mechanics_are_restricted_to_service_sections(): void
    {
        [, , $mechanicRole] = $this->createCoreRoles();

        $mechanic = User::factory()->create(['role' => $mechanicRole->id]);
        $mechanic->assignRole($mechanicRole);

        $this->actingAs($mechanic)->get('/gestiune-piese')->assertOk();
        $this->actingAs($mechanic)->get('/service-masini')->assertOk();
        $this->actingAs($mechanic)->get('/acasa')->assertForbidden();
    }

    /**
     * @return array{0: Role, 1: Role, 2: Role}
     */
    private function createCoreRoles(): array
    {
        $superAdmin = Role::firstOrCreate(
            ['slug' => 'super-admin'],
            [
                'name' => 'Super Admin',
                'description' => 'Full access.',
            ]
        );

        $admin = Role::firstOrCreate(
            ['slug' => 'admin'],
            [
                'name' => 'Administrator',
                'description' => 'Administrator access.',
            ]
        );

        $mechanic = Role::firstOrCreate(
            ['slug' => 'mecanic'],
            [
                'name' => 'Mecanic',
                'description' => 'Acces limitat la gestiune piese și service mașini.',
            ]
        );

        return [$superAdmin, $admin, $mechanic];
    }
}
