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

        $now = now();
        $modules = ['documente-word', 'documente-word-manage'];

        foreach ($modules as $moduleKey) {
            $moduleConfig = config('permissions.modules.' . $moduleKey, []);
            $name = (string) ($moduleConfig['name'] ?? Str::title(str_replace('-', ' ', $moduleKey)));
            $slug = $moduleConfig['slug'] ?? 'access-' . Str::slug($moduleKey);
            $description = $moduleConfig['description'] ?? null;

            $permission = DB::table('permissions')
                ->select('id')
                ->where('module', $moduleKey)
                ->where('slug', $slug)
                ->first();

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
            } else {
                DB::table('permissions')
                    ->where('id', $permission->id)
                    ->update([
                        'name' => $name,
                        'module' => $moduleKey,
                        'description' => $description,
                        'updated_at' => $now,
                    ]);
            }
        }

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

        $permissionIds = DB::table('permissions')
            ->whereIn('module', ['documente-word', 'documente-word-manage'])
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        if (! empty($permissionIds)) {
            if (Schema::hasTable('permission_user')) {
                DB::table('permission_user')->whereIn('permission_id', $permissionIds)->delete();
            }

            if (Schema::hasTable('permission_role')) {
                DB::table('permission_role')->whereIn('permission_id', $permissionIds)->delete();
            }

            DB::table('permissions')->whereIn('id', $permissionIds)->delete();
        }

        $this->syncRolePermissions();
    }

    private function syncRolePermissions(): void
    {
        if (! Schema::hasTable('roles') || ! Schema::hasTable('permission_role') || ! Schema::hasTable('permissions')) {
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
