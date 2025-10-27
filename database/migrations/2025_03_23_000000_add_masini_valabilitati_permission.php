<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('permissions')) {
            return;
        }

        $moduleKey = 'masini-valabilitati';
        $config = config('permissions.modules.' . $moduleKey, []);
        $now = now();

        $name = (string) ($config['name'] ?? Str::title(str_replace('-', ' ', $moduleKey)));
        $slug = (string) ($config['slug'] ?? 'access-' . Str::slug($moduleKey));
        $description = $config['description'] ?? null;

        $permissionId = DB::table('permissions')
            ->where('module', $moduleKey)
            ->value('id');

        if ($permissionId) {
            DB::table('permissions')
                ->where('id', (int) $permissionId)
                ->update([
                    'name' => $name,
                    'slug' => $slug,
                    'module' => $moduleKey,
                    'description' => $description,
                    'updated_at' => $now,
                ]);
        } else {
            $permissionId = DB::table('permissions')->insertGetId([
                'name' => $name,
                'slug' => $slug,
                'module' => $moduleKey,
                'description' => $description,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $this->syncRolePermissions();
    }

    public function down(): void
    {
        if (! Schema::hasTable('permissions')) {
            return;
        }

        $moduleKey = 'masini-valabilitati';

        $permissionId = DB::table('permissions')
            ->where('module', $moduleKey)
            ->value('id');

        if (! $permissionId) {
            return;
        }

        if (Schema::hasTable('permission_user')) {
            DB::table('permission_user')
                ->where('permission_id', (int) $permissionId)
                ->delete();
        }

        if (Schema::hasTable('permission_role')) {
            DB::table('permission_role')
                ->where('permission_id', (int) $permissionId)
                ->delete();
        }

        DB::table('permissions')
            ->where('id', (int) $permissionId)
            ->delete();

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
