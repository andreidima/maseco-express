<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImpersonationTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_view_impersonation_screen(): void
    {
        $superAdmin = $this->createSuperAdmin();

        $response = $this->actingAs($superAdmin)->get(route('tech.impersonation.index'));

        $response->assertOk();
        $response->assertSee('Impersonare utilizatori');
    }

    public function test_super_admin_can_impersonate_a_user(): void
    {
        $superAdmin = $this->createSuperAdmin();
        $targetUser = User::factory()->create();

        $response = $this->actingAs($superAdmin)->post(route('tech.impersonation.start'), [
            'user_id' => $targetUser->id,
        ]);

        $response->assertRedirect('/');
        $this->assertAuthenticatedAs($targetUser);
        $this->assertSame($superAdmin->id, session('impersonated_by'));
        $this->assertSame($superAdmin->name, session('impersonated_by_name'));
    }

    public function test_impersonated_user_can_stop_impersonation(): void
    {
        $superAdmin = $this->createSuperAdmin();
        $mechanicRole = Role::firstOrCreate(
            ['slug' => 'mecanic'],
            [
                'name' => 'Mecanic',
                'description' => 'Acces limitat la gestiune piese și service mașini.',
            ]
        );

        $targetUser = User::factory()->create();
        $targetUser->assignRole($mechanicRole);

        $this->actingAs($superAdmin)->post(route('tech.impersonation.start'), [
            'user_id' => $targetUser->id,
        ]);

        $this->assertAuthenticatedAs($targetUser);

        $response = $this->post(route('impersonation.stop'));

        $response->assertRedirect('/');
        $this->assertAuthenticatedAs($superAdmin);
        $this->assertNull(session('impersonated_by'));
        $this->assertNull(session('impersonated_by_name'));
    }

    public function test_impersonating_mechanic_redirects_to_service_module(): void
    {
        $superAdmin = $this->createSuperAdmin();
        $mechanicRole = Role::firstOrCreate(
            ['slug' => 'mecanic'],
            [
                'name' => 'Mecanic',
                'description' => 'Acces limitat la gestiune piese și service mașini.',
            ]
        );

        $mechanicUser = User::factory()->create();
        $mechanicUser->assignRole($mechanicRole);

        $response = $this->actingAs($superAdmin)->post(route('tech.impersonation.start'), [
            'user_id' => $mechanicUser->id,
        ]);

        $response->assertRedirect(route('service-masini.index'));
        $this->assertAuthenticatedAs($mechanicUser);
        $this->assertSame($superAdmin->id, session('impersonated_by'));
        $this->assertSame($superAdmin->name, session('impersonated_by_name'));
    }

    public function test_impersonation_index_orders_by_primary_role_then_name(): void
    {
        $superAdminRole = Role::firstOrCreate(
            ['slug' => 'super-admin'],
            [
                'name' => 'Super Admin',
                'description' => 'Full access to the technical toolbox.',
            ]
        );

        $adminRole = Role::firstOrCreate(
            ['slug' => 'admin'],
            [
                'name' => 'Administrator',
                'description' => 'Administrator access.',
            ]
        );

        $mechanicRole = Role::firstOrCreate(
            ['slug' => 'mecanic'],
            [
                'name' => 'Mecanic',
                'description' => 'Acces limitat la gestiune piese și service mașini.',
            ]
        );

        $operatorRole = Role::firstOrCreate(
            ['slug' => 'operator'],
            [
                'name' => 'Operator',
                'description' => 'Operator access.',
            ]
        );

        $superAdmin = User::factory()->create(['name' => 'Root User', 'activ' => 1]);
        $superAdmin->assignRole($superAdminRole);

        $adminUser = User::factory()->create(['name' => 'Zed Admin', 'activ' => 1]);
        $adminUser->assignRole($adminRole);

        $mechanicUser = User::factory()->create(['name' => 'Alpha Mechanic', 'activ' => 1]);
        $mechanicUser->assignRole($mechanicRole);

        $operatorUser = User::factory()->create(['name' => 'Beta Operator', 'activ' => 1]);
        $operatorUser->assignRole($operatorRole);

        $response = $this->actingAs($superAdmin)->get(route('tech.impersonation.index'));

        $response->assertOk();
        $response->assertSeeInOrder([
            'Zed Admin',
            'Alpha Mechanic',
            'Beta Operator',
            'Root User',
        ]);
    }

    public function test_impersonation_index_shows_only_active_accounts(): void
    {
        $superAdmin = $this->createSuperAdmin();

        User::factory()->create([
            'name' => 'Active Account',
            'activ' => 1,
        ]);

        User::factory()->create([
            'name' => 'Inactive Account',
            'activ' => 0,
        ]);

        $response = $this->actingAs($superAdmin)->get(route('tech.impersonation.index'));

        $response->assertOk();
        $response->assertSee('Active Account');
        $response->assertDontSee('Inactive Account');
    }

    public function test_user_two_can_access_impersonation_without_super_admin_role(): void
    {
        $userTwo = User::factory()->create([
            'id' => 2,
            'name' => 'Trusted Operator',
            'activ' => 1,
        ]);

        $response = $this->actingAs($userTwo)->get(route('tech.impersonation.index'));

        $response->assertOk();
        $response->assertSee('Impersonare utilizatori');

        $restrictedResponse = $this->actingAs($userTwo)->get(route('tech.migrations.index'));
        $restrictedResponse->assertForbidden();
    }

    private function createSuperAdmin(): User
    {
        $role = Role::firstOrCreate(
            ['slug' => 'super-admin'],
            [
                'name' => 'Super Admin',
                'description' => 'Full access to the technical toolbox.',
            ]
        );

        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }
}
