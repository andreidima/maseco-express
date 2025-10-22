<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $roleId = DB::table('roles')
            ->where('slug', 'dispecer')
            ->value('id');

        if (! $roleId) {
            return;
        }

        $permissionId = DB::table('permissions')
            ->where(function (Builder $query) {
                $query->where('slug', 'access-facturi-furnizori')
                    ->orWhere('module', 'facturi-furnizori');
            })
            ->value('id');

        if (! $permissionId) {
            return;
        }

        DB::table('permission_role')
            ->where('role_id', $roleId)
            ->where('permission_id', $permissionId)
            ->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $roleId = DB::table('roles')
            ->where('slug', 'dispecer')
            ->value('id');

        $permissionId = DB::table('permissions')
            ->where(function (Builder $query) {
                $query->where('slug', 'access-facturi-furnizori')
                    ->orWhere('module', 'facturi-furnizori');
            })
            ->value('id');

        if (! $roleId || ! $permissionId) {
            return;
        }

        $exists = DB::table('permission_role')
            ->where('role_id', $roleId)
            ->where('permission_id', $permissionId)
            ->exists();

        if ($exists) {
            return;
        }

        DB::table('permission_role')->insert([
            'role_id' => $roleId,
            'permission_id' => $permissionId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
};
