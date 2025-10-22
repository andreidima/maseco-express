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
        if (! Schema::hasTable('permissions') || ! Schema::hasTable('roles') || ! Schema::hasTable('permission_role')) {
            return;
        }

        if (Schema::hasTable('permission_user')) {
            DB::table('permission_user')->delete();
        }

        $permissions = DB::table('permissions')->select('id', 'module')->get();
        $roles = DB::table('roles')->select('id', 'slug')->get();

        if ($permissions->isEmpty() || $roles->isEmpty()) {
            return;
        }

        $permissionMap = $permissions->keyBy('module');
        $roleDefaults = collect(config('permissions.role_defaults', []));
        $allPermissionIds = $permissions->pluck('id')->map(fn ($id) => (int) $id)->all();

        foreach ($roles as $role) {
            $modules = $roleDefaults->get($role->slug, []);

            if ($modules === '*' || (is_array($modules) && in_array('*', $modules, true))) {
                $permissionIds = $allPermissionIds;
            } else {
                $permissionIds = collect((array) $modules)
                    ->map(function ($module) use ($permissionMap) {
                        $permission = $permissionMap->get((string) $module);

                        return $permission ? (int) $permission->id : null;
                    })
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();
            }

            DB::table('permission_role')->where('role_id', (int) $role->id)->delete();

            if (empty($permissionIds)) {
                continue;
            }

            $now = now();
            $rows = array_map(function ($permissionId) use ($role, $now) {
                return [
                    'role_id' => (int) $role->id,
                    'permission_id' => (int) $permissionId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }, $permissionIds);

            DB::table('permission_role')->insert($rows);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('permission_user')) {
            DB::table('permission_user')->delete();
        }
    }
};
