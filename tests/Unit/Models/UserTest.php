<?php

namespace Tests\Unit\Models;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_has_role_checks_ids_and_slugs(): void
    {
        $adminRole = Role::create([
            'name' => 'Administrator',
            'slug' => 'admin',
        ]);
        $mechanicRole = Role::create([
            'name' => 'Mecanic',
            'slug' => 'mecanic',
        ]);

        $user = User::factory()->create();
        $user->assignRole($adminRole);

        $userWithRoles = User::with('roles')->findOrFail($user->id);

        $this->assertTrue($userWithRoles->hasRole($adminRole->id));
        $this->assertTrue($userWithRoles->hasRole('admin'));
        $this->assertTrue($userWithRoles->hasRole('Administrator'));
        $this->assertFalse($userWithRoles->hasRole($mechanicRole->id));
        $this->assertFalse($userWithRoles->hasRole('mecanic'));
    }

    public function test_primary_role_accessor_returns_first_assigned_role(): void
    {
        $firstRole = Role::create([
            'name' => 'Administrator',
            'slug' => 'admin',
        ]);
        $secondRole = Role::create([
            'name' => 'Mecanic',
            'slug' => 'mecanic',
        ]);

        $user = User::factory()->create();

        $user->roles()->attach($firstRole->id, [
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ]);

        $user->roles()->attach($secondRole->id, [
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $user = $user->fresh();

        DB::enableQueryLog();
        $this->assertSame($firstRole->id, $user->primary_role_id);
        $this->assertSame('Administrator', $user->display_role_name);
        $this->assertNotEmpty(DB::getQueryLog());

        DB::flushQueryLog();
        $this->assertSame($firstRole->id, $user->primary_role_id);
        $this->assertSame('Administrator', $user->display_role_name);
        $this->assertCount(0, DB::getQueryLog());
    }

    public function test_display_role_name_falls_back_to_slug_title_case(): void
    {
        $role = Role::create([
            'name' => '',
            'slug' => 'service-tech',
        ]);

        $user = User::factory()->create();
        $user->roles()->attach($role->id, [
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertSame('Service Tech', $user->fresh()->display_role_name);
    }
}
