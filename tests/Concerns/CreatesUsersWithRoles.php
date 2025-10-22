<?php

namespace Tests\Concerns;

use App\Models\Role;
use App\Models\User;
use Tests\Fixtures\RoleFixtures;

trait CreatesUsersWithRoles
{
    protected function ensureRole(string $slug, ?array $modules = null, array $attributes = []): Role
    {
        return RoleFixtures::createRole($slug, $modules, $attributes);
    }

    /**
     * @param  array<int, Role|string>|Role|string  $roles
     */
    protected function createUserWithRoles(Role|array|string $roles, array $attributes = []): User
    {
        $user = User::factory()->create($attributes);

        $roleCollection = collect(is_array($roles) ? $roles : [$roles])
            ->map(function ($role) {
                if ($role instanceof Role) {
                    return $role;
                }

                return $this->ensureRole($role);
            });

        $roleCollection->each(fn (Role $role) => $user->assignRole($role));

        return $user->fresh();
    }
}
