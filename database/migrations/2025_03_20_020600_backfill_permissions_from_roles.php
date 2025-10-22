<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('permissions') || ! Schema::hasTable('roles') || ! Schema::hasTable('users') || ! Schema::hasTable('role_user')) {
            return;
        }

        $permissions = DB::table('permissions')->select('id', 'module')->get();

        if ($permissions->isEmpty()) {
            return;
        }

        $permissionsByModule = $permissions->keyBy('module');
        $allPermissionIds = $permissions->pluck('id')->all();
        $now = now();

        $roleDefaults = collect(config('permissions.role_defaults', []));

        $roles = DB::table('roles')->select('id', 'slug')->get();

        foreach ($roles as $role) {
            $modules = $roleDefaults->get($role->slug, []);

            $permissionIds = [];

            if ($modules === '*' || (is_array($modules) && in_array('*', $modules, true))) {
                $permissionIds = $allPermissionIds;
            } else {
                foreach ((array) $modules as $module) {
                    $module = (string) $module;

                    if ($permissionsByModule->has($module)) {
                        $permissionIds[] = (int) $permissionsByModule->get($module)->id;
                    }
                }
            }

            $permissionIds = array_values(array_unique($permissionIds));

            foreach ($permissionIds as $permissionId) {
                DB::table('permission_role')->updateOrInsert(
                    [
                        'role_id' => (int) $role->id,
                        'permission_id' => $permissionId,
                    ],
                    [
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );
            }
        }

        // User permissions are now expected to be inherited from their assigned roles.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('permission_user')) {
            DB::table('permission_user')->truncate();
        }

        if (Schema::hasTable('permission_role')) {
            DB::table('permission_role')->truncate();
        }
    }
};
