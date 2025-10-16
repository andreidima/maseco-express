<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class UserRolesTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_management_access_is_limited_to_admin_roles(): void
    {
        [$superAdminRole, $adminRole, $mechanicRole] = $this->createCoreRoles();

        $admin = User::factory()->create(['role' => $adminRole->id]);
        $admin->assignRole($adminRole);

        $superAdmin = User::factory()->create(['role' => $superAdminRole->id]);
        $superAdmin->assignRole($superAdminRole);

        $mechanic = User::factory()->create(['role' => $mechanicRole->id]);
        $mechanic->assignRole($mechanicRole);

        $this->actingAs($admin)->get('/utilizatori')->assertOk();
        $this->actingAs($superAdmin)->get('/utilizatori')->assertOk();
        $this->actingAs($mechanic)->get('/utilizatori')->assertForbidden();
    }

    public function test_super_admin_role_cannot_be_assigned_through_user_forms(): void
    {
        [$superAdminRole, $adminRole] = $this->createCoreRoles();

        $admin = User::factory()->create(['role' => $adminRole->id]);
        $admin->assignRole($adminRole);

        $response = $this->actingAs($admin)->post('/utilizatori', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'telefon' => '0123456789',
            'role' => $superAdminRole->id,
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'activ' => 1,
        ]);

        $response->assertSessionHasErrors('role');
        $this->assertDatabaseMissing('users', ['email' => 'test@example.com']);

        $user = User::factory()->create([
            'name' => 'Existing User',
            'email' => 'existing@example.com',
            'role' => $adminRole->id,
        ]);
        $user->assignRole($adminRole);

        $updateResponse = $this->actingAs($admin)->put("/utilizatori/{$user->id}", [
            'id' => $user->id,
            'name' => 'Existing User',
            'email' => 'existing@example.com',
            'telefon' => '0123456789',
            'role' => $superAdminRole->id,
            'activ' => 1,
        ]);

        $updateResponse->assertSessionHasErrors('role');
        $this->assertFalse($user->fresh()->hasRole('super-admin'));
    }

    public function test_legacy_super_admin_without_pivot_role_can_access_user_management(): void
    {
        [$superAdminRole, $adminRole] = $this->createCoreRoles();

        $legacySuperAdmin = User::factory()->create([
            'role' => $superAdminRole->id,
        ]);

        $this->actingAs($legacySuperAdmin)->get('/utilizatori')->assertOk();
    }

    public function test_mechanics_are_restricted_to_service_sections(): void
    {
        [, , $mechanicRole] = $this->createCoreRoles();

        $mechanic = User::factory()->create(['role' => $mechanicRole->id]);
        $mechanic->assignRole($mechanicRole);

        $this->actingAs($mechanic)->get('/gestiune-piese')->assertOk();
        $this->actingAs($mechanic)->get('/service-masini')->assertOk();
        $this->actingAs($mechanic)->get('/acasa')->assertForbidden();
    }

    public function test_mechanic_is_redirected_to_parts_after_login(): void
    {
        [, , $mechanicRole] = $this->createCoreRoles();

        $mechanic = User::factory()->create([
            'role' => $mechanicRole->id,
            'email' => 'mechanic@example.com',
            'cod_email' => 'login-code',
        ]);
        $mechanic->assignRole($mechanicRole);

        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        $response = $this->withSession(['url.intended' => '/acasa'])
            ->post('/login', [
                'email' => 'mechanic@example.com',
                'password' => 'password',
                'cod_email' => 'login-code',
            ]);

        $response->assertRedirect(route('gestiune-piese.index'));
        $this->assertAuthenticatedAs($mechanic->fresh());
    }

    public function test_mechanic_interface_hides_supplier_invoices(): void
    {
        [, $adminRole, $mechanicRole] = $this->createCoreRoles();

        if (! Schema::hasTable('service_ff_facturi')) {
            Schema::create('service_ff_facturi', function (Blueprint $table) {
                $table->id();
                $table->date('data_factura')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('service_gestiune_piese')) {
            Schema::create('service_gestiune_piese', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('factura_id')->nullable();
                $table->string('denumire')->nullable();
                $table->string('cod')->nullable();
                $table->decimal('nr_bucati', 12, 2)->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasColumn('service_gestiune_piese', 'factura_id')) {
            $this->markTestSkipped('Factura relation column missing from service_gestiune_piese table.');
        }

        DB::table('service_ff_facturi')->delete();
        $invoiceId = DB::table('service_ff_facturi')->insertGetId([
            'data_factura' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('service_gestiune_piese')->delete();
        DB::table('service_gestiune_piese')->insert([
            'factura_id' => $invoiceId,
            'denumire' => 'Filtru ulei',
            'cod' => 'FO-001',
            'nr_bucati' => 10,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $mechanic = User::factory()->create(['role' => $mechanicRole->id]);
        $mechanic->assignRole($mechanicRole);

        $mechanicResponse = $this->actingAs($mechanic)->get('/gestiune-piese');
        $mechanicResponse->assertOk();
        $mechanicResponse->assertDontSee('Factură', false);
        $mechanicResponse->assertDontSee('Facturi furnizori', false);
        $mechanicResponse->assertSee('href="' . route('gestiune-piese.index') . '"', false);

        $admin = User::factory()->create(['role' => $adminRole->id]);
        $admin->assignRole($adminRole);

        $adminResponse = $this->actingAs($admin)->get('/gestiune-piese');
        $adminResponse->assertOk();
        $adminResponse->assertSee('Factură', false);
        $adminResponse->assertSee('Facturi furnizori', false);
    }

    public function test_primary_admin_user_is_not_mistaken_for_mechanic_when_role_is_missing(): void
    {
        $adminRole = Role::firstOrCreate(
            ['slug' => 'admin'],
            [
                'name' => 'Administrator',
                'description' => 'Administrator access.',
            ]
        );

        $admin = User::factory()->create([
            'id' => 1,
            'role' => $adminRole->id,
        ]);

        $admin->assignRole($adminRole);

        $this->actingAs($admin)->get('/acasa')->assertOk();
    }

    /**
     * @return array{0: Role, 1: Role, 2: Role}
     */
    private function createCoreRoles(): array
    {
        $superAdmin = Role::firstOrCreate(
            ['slug' => 'super-admin'],
            [
                'name' => 'Super Admin',
                'description' => 'Full access.',
            ]
        );

        $admin = Role::firstOrCreate(
            ['slug' => 'admin'],
            [
                'name' => 'Administrator',
                'description' => 'Administrator access.',
            ]
        );

        $mechanic = Role::firstOrCreate(
            ['slug' => 'mecanic'],
            [
                'name' => 'Mecanic',
                'description' => 'Acces limitat la gestiune piese și service mașini.',
            ]
        );

        return [$superAdmin, $admin, $mechanic];
    }
}
