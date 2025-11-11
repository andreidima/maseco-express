<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Registers the "valabilitati" permission and syncs it to the roles that
 * advertise the module inside the permissions configuration.
 */
class ValabilitatiPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $moduleKey = 'valabilitati';
        $config = config('permissions.modules.' . $moduleKey, []);

        $permission = Permission::updateOrCreate(
            ['module' => $moduleKey],
            [
                'name' => $config['name'] ?? Str::title(str_replace('-', ' ', $moduleKey)),
                'slug' => $config['slug'] ?? 'access-' . Str::slug($moduleKey),
                'description' => $config['description'] ?? null,
            ]
        );

        $roleDefaults = collect(config('permissions.role_defaults', []));

        Role::query()
            ->with('permissions')
            ->get()
            ->each(function (Role $role) use ($permission, $moduleKey, $roleDefaults): void {
                $modules = $roleDefaults->get($role->slug, []);

                if ($modules === '*' || (is_array($modules) && in_array('*', $modules, true))) {
                    $role->permissions()->syncWithoutDetaching([$permission->id]);

                    return;
                }

                if (in_array($moduleKey, (array) $modules, true)) {
                    $role->permissions()->syncWithoutDetaching([$permission->id]);
                }
            });
    }
}
