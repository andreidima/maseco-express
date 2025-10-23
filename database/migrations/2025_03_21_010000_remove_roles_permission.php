<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('permissions')) {
            return;
        }

        $permissionId = DB::table('permissions')
            ->where('module', 'roles')
            ->value('id');

        if (! $permissionId) {
            return;
        }

        if (Schema::hasTable('permission_role')) {
            DB::table('permission_role')
                ->where('permission_id', (int) $permissionId)
                ->delete();
        }

        DB::table('permissions')->where('id', (int) $permissionId)->delete();
    }

    public function down(): void
    {
        if (! Schema::hasTable('permissions')) {
            return;
        }

        $permission = DB::table('permissions')
            ->where('module', 'roles')
            ->first();

        if ($permission) {
            return;
        }

        $now = now();

        $permissionId = DB::table('permissions')->insertGetId([
            'name' => 'Gestionare Roluri',
            'slug' => 'access-roles',
            'module' => 'roles',
            'description' => 'Acoperă configurarea rolurilor, asocierile lor și revizuirea accesului implicit.',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        if (! Schema::hasTable('permission_role')) {
            return;
        }

        $roles = DB::table('roles')
            ->whereIn('slug', ['super-admin', 'admin'])
            ->pluck('id');

        if ($roles->isEmpty()) {
            return;
        }

        $rows = $roles
            ->map(function ($roleId) use ($permissionId, $now) {
                return [
                    'role_id' => (int) $roleId,
                    'permission_id' => (int) $permissionId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            })
            ->all();

        DB::table('permission_role')->insert($rows);
    }
};
