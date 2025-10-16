<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $superAdmin = Role::firstOrCreate(
            ['slug' => 'super-admin'],
            [
                'name' => 'Super Admin',
                'description' => 'Full access to the technical toolbox.',
            ]
        );

        $admin = Role::firstOrCreate(
            ['slug' => 'admin'],
            [
                'name' => 'Administrator',
                'description' => 'Legacy administrator role mapped from users.role = 1.',
            ]
        );

        Role::firstOrCreate(
            ['slug' => 'dispecer'],
            [
                'name' => 'Dispecer',
                'description' => 'Legacy dispatcher role mapped from users.role = 2.',
            ]
        );

        Role::firstOrCreate(
            ['slug' => 'mecanic'],
            [
                'name' => 'Mecanic',
                'description' => 'Acces limitat la gestiunea pieselor și service-ul mașinilor.',
            ]
        );

        $user = User::find(1);

        if ($user) {
            $user->assignRole($superAdmin);
            $user->assignRole($admin);
        }
    }
}
