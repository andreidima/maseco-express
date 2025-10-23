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

        $now = now();
        $moduleKeys = ['statii-peco', 'statii-peco-manage'];

        $records = collect($moduleKeys)
            ->map(function (string $moduleKey) use ($now) {
                $config = config('permissions.modules.' . $moduleKey, []);

                return [
                    'module' => $moduleKey,
                    'name' => (string) ($config['name'] ?? Str::title(str_replace('-', ' ', $moduleKey))),
                    'slug' => (string) ($config['slug'] ?? 'access-' . Str::slug($moduleKey)),
                    'description' => $config['description'] ?? null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            })
            ->all();

        DB::table('permissions')->upsert(
            $records,
            ['module'],
            ['name', 'slug', 'description', 'updated_at']
        );

        $permissionIds = DB::table('permissions')
            ->whereIn('module', $moduleKeys)
            ->pluck('id', 'module');

        if ($permissionIds->isEmpty()) {
            return;
        }

        if (Schema::hasTable('permission_user')) {
            DB::table('permission_user')
                ->whereIn('permission_id', $permissionIds->values()->all())
                ->delete();
        }

        if (! Schema::hasTable('permission_role')) {
            return;
        }

        $roleIds = DB::table('roles')
            ->whereIn('slug', ['super-admin', 'admin', 'dispecer'])
            ->pluck('id', 'slug');

        if ($roleIds->isEmpty()) {
            return;
        }

        $comenziPermissionId = DB::table('permissions')
            ->where('module', 'comenzi')
            ->value('id');

        if ($comenziPermissionId) {
            DB::table('permission_role')
                ->where('permission_id', (int) $comenziPermissionId)
                ->whereIn('role_id', $roleIds->values()->all())
                ->delete();
        }

        DB::table('permission_role')
            ->whereIn('permission_id', $permissionIds->values()->all())
            ->whereIn('role_id', $roleIds->values()->all())
            ->delete();

        $rows = [];

        foreach ($roleIds as $roleSlug => $roleId) {
            if ($permissionIds->has('statii-peco')) {
                $rows[] = [
                    'role_id' => (int) $roleId,
                    'permission_id' => (int) $permissionIds['statii-peco'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            if ($permissionIds->has('statii-peco-manage') && in_array($roleSlug, ['admin', 'super-admin'], true)) {
                $rows[] = [
                    'role_id' => (int) $roleId,
                    'permission_id' => (int) $permissionIds['statii-peco-manage'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        if (! empty($rows)) {
            DB::table('permission_role')->insert($rows);
        }

        if (! $comenziPermissionId) {
            return;
        }

        $comenziRows = [];

        foreach ($roleIds as $roleId) {
            $comenziRows[] = [
                'role_id' => (int) $roleId,
                'permission_id' => (int) $comenziPermissionId,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('permission_role')->insert($comenziRows);
    }

    public function down(): void
    {
        if (! Schema::hasTable('permissions')) {
            return;
        }

        $permissionIds = DB::table('permissions')
            ->whereIn('module', ['statii-peco', 'statii-peco-manage'])
            ->pluck('id');

        if ($permissionIds->isEmpty()) {
            return;
        }

        if (Schema::hasTable('permission_user')) {
            DB::table('permission_user')
                ->whereIn('permission_id', $permissionIds->all())
                ->delete();
        }

        if (Schema::hasTable('permission_role')) {
            DB::table('permission_role')
                ->whereIn('permission_id', $permissionIds->all())
                ->delete();
        }

        DB::table('permissions')
            ->whereIn('id', $permissionIds->all())
            ->delete();
    }
};
