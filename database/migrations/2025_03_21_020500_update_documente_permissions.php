<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('permissions')) {
            return;
        }

        $moduleKey = 'documente-manage';
        $now = now();
        $moduleConfig = config('permissions.modules.' . $moduleKey, []);

        $name = (string) ($moduleConfig['name'] ?? Str::title(str_replace('-', ' ', $moduleKey)));
        $description = $moduleConfig['description'] ?? null;
        $slug = 'access-documente-manage';

        $permission = DB::table('permissions')
            ->select('id')
            ->where('module', 'documente-admin')
            ->first();

        if ($permission === null) {
            $permission = DB::table('permissions')
                ->select('id')
                ->where('module', $moduleKey)
                ->where('slug', $slug)
                ->first();
        }

        if ($permission === null) {
            $permissionId = DB::table('permissions')->insertGetId([
                'name' => $name,
                'slug' => $slug,
                'module' => $moduleKey,
                'description' => $description,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $permission = (object) ['id' => $permissionId];
        }

        $duplicateManageIds = DB::table('permissions')
            ->where('module', $moduleKey)
            ->where('id', '!=', $permission->id)
            ->pluck('id')
            ->all();

        if (! empty($duplicateManageIds)) {
            DB::table('permission_role')->whereIn('permission_id', $duplicateManageIds)->delete();

            if (Schema::hasTable('permission_user')) {
                DB::table('permission_user')->whereIn('permission_id', $duplicateManageIds)->delete();
            }

            DB::table('permissions')->whereIn('id', $duplicateManageIds)->delete();
        }

        DB::table('permissions')
            ->where('id', $permission->id)
            ->update([
                'name' => $name,
                'slug' => $slug,
                'module' => $moduleKey,
                'description' => $description,
                'updated_at' => $now,
            ]);

        $this->syncRolePermissions();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('permissions')) {
            return;
        }

        $now = now();
        $permission = DB::table('permissions')
            ->select('id')
            ->where('module', 'documente-manage')
            ->where('slug', 'access-documente-manage')
            ->first();

        if ($permission !== null) {
            DB::table('permissions')
                ->where('id', $permission->id)
                ->update([
                    'name' => 'Documente (Admin)',
                    'slug' => 'access-documente-admin',
                    'module' => 'documente-admin',
                    'description' => null,
                    'updated_at' => $now,
                ]);
        }

        $this->syncRolePermissions();
    }

    private function syncRolePermissions(): void
    {
        if (! Schema::hasTable('roles') || ! Schema::hasTable('permission_role')) {
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
};
