<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $updates = [
            'admin' => [
                'from' => 'Legacy administrator role mapped from users.role = 1.',
                'to' => 'Acces complet la configurarea aplicației și administrarea utilizatorilor.',
            ],
            'dispecer' => [
                'from' => 'Legacy dispatcher role mapped from users.role = 2.',
                'to' => 'Coordonează comenzile și distribuie cursele către șoferi.',
            ],
            'mecanic' => [
                'from' => 'Acces limitat la gestiunea pieselor și service-ul mașinilor.',
                'to' => 'Gestionează intervențiile tehnice și starea vehiculelor.',
            ],
        ];

        foreach ($updates as $slug => $payload) {
            DB::table('roles')
                ->where('slug', $slug)
                ->where('description', $payload['from'])
                ->update(['description' => $payload['to']]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $updates = [
            'admin' => [
                'from' => 'Acces complet la configurarea aplicației și administrarea utilizatorilor.',
                'to' => 'Legacy administrator role mapped from users.role = 1.',
            ],
            'dispecer' => [
                'from' => 'Coordonează comenzile și distribuie cursele către șoferi.',
                'to' => 'Legacy dispatcher role mapped from users.role = 2.',
            ],
            'mecanic' => [
                'from' => 'Gestionează intervențiile tehnice și starea vehiculelor.',
                'to' => 'Acces limitat la gestiunea pieselor și service-ul mașinilor.',
            ],
        ];

        foreach ($updates as $slug => $payload) {
            DB::table('roles')
                ->where('slug', $slug)
                ->where('description', $payload['from'])
                ->update(['description' => $payload['to']]);
        }
    }
};
