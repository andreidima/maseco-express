<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('role_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['role_id', 'user_id']);
        });

        if (Schema::hasTable('users')) {
            $now = now();

            $legacyRoles = [
                1 => [
                    'name' => 'Administrator',
                    'slug' => 'admin',
                    'description' => 'Legacy administrator role mapped from users.role = 1.',
                ],
                2 => [
                    'name' => 'Dispecer',
                    'slug' => 'dispecer',
                    'description' => 'Legacy dispatcher role mapped from users.role = 2.',
                ],
            ];

            foreach ($legacyRoles as $id => $payload) {
                DB::table('roles')->updateOrInsert(
                    ['id' => $id],
                    array_merge($payload, [
                        'created_at' => $now,
                        'updated_at' => $now,
                    ])
                );
            }

            $users = DB::table('users')
                ->select('id', 'role')
                ->whereNotNull('role')
                ->get();

            foreach ($users as $user) {
                $roleId = (int) $user->role;

                if (! array_key_exists($roleId, $legacyRoles)) {
                    continue;
                }

                DB::table('role_user')->updateOrInsert(
                    [
                        'role_id' => $roleId,
                        'user_id' => (int) $user->id,
                    ],
                    [
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('role_user');
        Schema::dropIfExists('roles');
    }
};
