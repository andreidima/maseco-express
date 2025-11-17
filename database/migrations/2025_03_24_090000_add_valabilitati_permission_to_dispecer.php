<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('permissions') || ! Schema::hasTable('roles') || ! Schema::hasTable('permission_role')) {
            return;
        }

        $permissionId = DB::table('permissions')
            ->where('module', 'valabilitati')
            ->value('id');

        $roleId = DB::table('roles')
            ->where('slug', 'dispecer')
            ->value('id');

        if (! $permissionId || ! $roleId) {
            return;
        }

        $now = now();

        DB::table('permission_role')->updateOrInsert(
            [
                'role_id' => (int) $roleId,
                'permission_id' => (int) $permissionId,
            ],
            [
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
    }

    public function down(): void
    {
        if (! Schema::hasTable('permissions') || ! Schema::hasTable('roles') || ! Schema::hasTable('permission_role')) {
            return;
        }

        $permissionId = DB::table('permissions')
            ->where('module', 'valabilitati')
            ->value('id');

        $roleId = DB::table('roles')
            ->where('slug', 'dispecer')
            ->value('id');

        if (! $permissionId || ! $roleId) {
            return;
        }

        DB::table('permission_role')
            ->where('permission_id', (int) $permissionId)
            ->where('role_id', (int) $roleId)
            ->delete();
    }
};
