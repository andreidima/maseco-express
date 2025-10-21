<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $superAdmin = Role::firstOrCreate(
            ['slug' => 'super-admin'],
            [
                'name' => 'Super Admin',
                'description' => 'Full access to the technical toolbox.',
            ]
        );

        $admin = Role::firstOrCreate(
            ['slug' => 'admin'],
            [
                'name' => 'Administrator',
                'description' => 'Legacy administrator role mapped from users.role = 1.',
            ]
        );

        $dispecer = Role::firstOrCreate(
            ['slug' => 'dispecer'],
            [
                'name' => 'Dispecer',
                'description' => 'Legacy dispatcher role mapped from users.role = 2.',
            ]
        );

        $mecanic = Role::firstOrCreate(
            ['slug' => 'mecanic'],
            [
                'name' => 'Mecanic',
                'description' => 'Acces limitat la gestiunea pieselor și service-ul mașinilor.',
            ]
        );

        $permissions = Permission::all();
        $permissionMap = $permissions->keyBy('module');
        $roleDefaults = collect(config('permissions.role_defaults', []));

        $syncPermissions = function (Role $role) use ($permissions, $permissionMap, $roleDefaults): void {
            $modules = $roleDefaults->get($role->slug, []);

            if ($modules === '*' || (is_array($modules) && in_array('*', $modules, true))) {
                $role->syncPermissions($permissions);

                return;
            }

            $ids = collect((array) $modules)
                ->map(function (string $module) use ($permissionMap) {
                    return optional($permissionMap->get($module))->id;
                })
                ->filter()
                ->values();

            $role->syncPermissions($ids);
        };

        $syncPermissions($superAdmin);
        $syncPermissions($admin);
        $syncPermissions($dispecer);
        $syncPermissions($mecanic);

        $user = User::find(1);

        if ($user) {
            $user->assignRole($superAdmin);
            $user->assignRole($admin);
            $user->syncPermissions($permissions);
        }
    }
}
