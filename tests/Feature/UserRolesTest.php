<?php

namespace Tests\Feature;

use App\Models\DocumentWord;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Concerns\CreatesUsersWithRoles;
use Tests\TestCase;

class UserRolesTest extends TestCase
{
    use RefreshDatabase;
    use CreatesUsersWithRoles;

    public function test_user_management_access_is_limited_to_admin_roles(): void
    {
        [$superAdminRole, $adminRole, $mechanicRole] = $this->createCoreRoles();

        $admin = $this->createUserWithRoles($adminRole);
        $superAdmin = $this->createUserWithRoles($superAdminRole);
        $mechanic = $this->createUserWithRoles($mechanicRole);

        $this->actingAs($admin)->get('/utilizatori')->assertOk();
        $this->actingAs($superAdmin)->get('/utilizatori')->assertOk();
        $this->actingAs($mechanic)->get('/utilizatori')->assertForbidden();
    }

    public function test_super_admin_role_cannot_be_assigned_through_user_forms(): void
    {
        [$superAdminRole, $adminRole] = $this->createCoreRoles();

        $admin = $this->createUserWithRoles($adminRole);

        $response = $this->actingAs($admin)->post('/utilizatori', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'telefon' => '0123456789',
            'roles' => [$superAdminRole->id],
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'activ' => 1,
        ]);

        $response->assertSessionHasErrors('roles.0');
        $this->assertDatabaseMissing('users', ['email' => 'test@example.com']);

        $user = $this->createUserWithRoles($adminRole, [
            'name' => 'Existing User',
            'email' => 'existing@example.com',
        ]);

        $updateResponse = $this->actingAs($admin)->put("/utilizatori/{$user->id}", [
            'id' => $user->id,
            'name' => 'Existing User',
            'email' => 'existing@example.com',
            'telefon' => '0123456789',
            'roles' => [$superAdminRole->id],
            'activ' => 1,
        ]);

        $updateResponse->assertSessionHasErrors('roles.0');
        $this->assertFalse($user->fresh()->hasRole('super-admin'));
    }

    public function test_user_creation_syncs_primary_role_and_hides_legacy_column(): void
    {
        [, $adminRole, $mechanicRole] = $this->createCoreRoles();

        $admin = $this->createUserWithRoles($adminRole);

        $response = $this->actingAs($admin)->post('/utilizatori', [
            'name' => 'Mechanic User',
            'email' => 'mechanic@example.com',
            'telefon' => '0123456789',
            'roles' => [$mechanicRole->id],
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'activ' => 1,
        ]);

        $response->assertRedirect('/utilizatori');

        $createdUser = User::where('email', 'mechanic@example.com')->firstOrFail();

        $this->assertSame($mechanicRole->id, $createdUser->primary_role_id);
        $this->assertTrue($createdUser->hasRole($mechanicRole->id));
        $this->assertNull($createdUser->getRawOriginal('role'));
    }

    public function test_user_creation_syncs_multiple_roles_without_direct_permissions(): void
    {
        [, $adminRole, $mechanicRole] = $this->createCoreRoles();

        $dispatcherRole = $this->ensureRole('dispecer', null, [
            'name' => 'Dispecer',
            'description' => 'Acces specific dispecerilor.',
        ]);

        $admin = $this->createUserWithRoles($adminRole);

        $dashboardPermission = Permission::where('module', 'dashboard')->firstOrFail();
        $documentsPermission = Permission::where('module', 'documente')->firstOrFail();

        $response = $this->actingAs($admin)->post('/utilizatori', [
            'name' => 'Advanced User',
            'email' => 'advanced@example.com',
            'telefon' => '0123456789',
            'roles' => [$adminRole->id, $dispatcherRole->id, $mechanicRole->id],
            'permissions' => [$dashboardPermission->id, $documentsPermission->id],
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'activ' => 1,
        ]);

        $response->assertRedirect('/utilizatori');

        $createdUser = User::where('email', 'advanced@example.com')->with(['roles', 'permissions'])->firstOrFail();

        $this->assertCount(3, $createdUser->roles);
        $this->assertEqualsCanonicalizing(
            [$adminRole->id, $dispatcherRole->id, $mechanicRole->id],
            $createdUser->roles->pluck('id')->map(fn ($value) => (int) $value)->all()
        );
        $this->assertTrue($createdUser->permissions->isEmpty());
        $this->assertContains($createdUser->primary_role_id, [$adminRole->id, $dispatcherRole->id, $mechanicRole->id]);
    }

    public function test_user_update_syncs_primary_role_and_hides_legacy_column(): void
    {
        [, $adminRole, $mechanicRole] = $this->createCoreRoles();

        $admin = $this->createUserWithRoles($adminRole);
        $user = $this->createUserWithRoles($mechanicRole, [
            'name' => 'Legacy Mechanic',
            'email' => 'legacy-mechanic@example.com',
        ]);

        $response = $this->actingAs($admin)->put("/utilizatori/{$user->id}", [
            'id' => $user->id,
            'name' => 'Legacy Mechanic',
            'email' => 'legacy-mechanic@example.com',
            'telefon' => '0123456789',
            'roles' => [$adminRole->id],
            'password' => null,
            'password_confirmation' => null,
            'activ' => 1,
        ]);

        $response->assertRedirect('/utilizatori');

        $updatedUser = $user->fresh();

        $this->assertSame($adminRole->id, $updatedUser->primary_role_id);
        $this->assertTrue($updatedUser->hasRole($adminRole->id));
        $this->assertNull($updatedUser->getRawOriginal('role'));
    }

    public function test_user_update_syncs_roles_and_preserves_manual_module_roles(): void
    {
        [, $adminRole, $mechanicRole] = $this->createCoreRoles();

        $dispatcherRole = $this->ensureRole('dispecer', null, [
            'name' => 'Dispecer',
            'description' => 'Acces specific dispecerilor.',
        ]);

        $customPartsRole = $this->ensureRole('gestiune-piese-manual', ['gestiune-piese'], [
            'name' => 'Gestiune Piese Manual',
            'description' => 'Rol suplimentar pentru accesul la gestiune piese.',
        ]);

        $admin = $this->createUserWithRoles($adminRole);
        $user = $this->createUserWithRoles([$mechanicRole, $customPartsRole], [
            'name' => 'Advanced Existing',
            'email' => 'advanced-existing@example.com',
        ]);

        $documentsPermission = Permission::where('module', 'documente')->firstOrFail();
        $techToolsPermission = Permission::where('module', 'tech-tools')->firstOrFail();

        $response = $this->actingAs($admin)->put("/utilizatori/{$user->id}", [
            'id' => $user->id,
            'name' => 'Advanced Existing',
            'email' => 'advanced-existing@example.com',
            'telefon' => '0123456789',
            'roles' => [$dispatcherRole->id, $adminRole->id, $customPartsRole->id],
            'permissions' => [$documentsPermission->id, $techToolsPermission->id],
            'password' => null,
            'password_confirmation' => null,
            'activ' => 1,
        ]);

        $response->assertRedirect('/utilizatori');

        $updatedUser = $user->fresh()->load(['roles', 'permissions']);

        $this->assertEqualsCanonicalizing(
            [$dispatcherRole->id, $adminRole->id, $customPartsRole->id],
            $updatedUser->roles->pluck('id')->map(fn ($value) => (int) $value)->all()
        );
        $this->assertEqualsCanonicalizing(
            [$documentsPermission->id, $techToolsPermission->id],
            $updatedUser->permissions->pluck('id')->map(fn ($value) => (int) $value)->all()
        );
    }

    public function test_user_without_pivot_role_cannot_access_user_management(): void
    {
        [$superAdminRole, $adminRole] = $this->createCoreRoles();

        $legacySuperAdmin = User::factory()->create();
        $legacySuperAdmin->forceFill(['role' => $superAdminRole->id])->save();

        $this->actingAs($legacySuperAdmin)->get('/utilizatori')->assertForbidden();
    }

    public function test_mechanics_are_restricted_to_service_sections(): void
    {
        [, , $mechanicRole] = $this->createCoreRoles();

        $mechanic = $this->createUserWithRoles($mechanicRole);

        $this->actingAs($mechanic)->get('/gestiune-piese')->assertOk();
        $this->actingAs($mechanic)->get('/service-masini')->assertOk();
        $this->actingAs($mechanic)->get('/acasa')->assertForbidden();
        $this->actingAs($mechanic)->get(route('facturi-furnizori.facturi.index'))->assertForbidden();
        $this->actingAs($mechanic)->get('/comenzi')->assertForbidden();
    }

    public function test_regular_user_must_submit_email_code_to_login(): void
    {
        [$_superAdminRole, $adminRole, $_mechanicRole] = $this->createCoreRoles();

        $user = $this->createUserWithRoles($adminRole, [
            'email' => 'user@example.com',
            'cod_email' => 'login-code',
        ]);

        $response = $this->from('/login')->post('/login', [
            'email' => 'user@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('cod_email');
        $this->assertGuest();
    }

    public function test_mechanic_is_redirected_to_parts_after_login(): void
    {
        [, , $mechanicRole] = $this->createCoreRoles();

        $mechanic = $this->createUserWithRoles($mechanicRole, [
            'email' => 'mechanic@example.com',
            'cod_email' => 'login-code',
        ]);

        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        $response = $this->withSession(['url.intended' => '/acasa'])
            ->post('/login', [
                'email' => 'mechanic@example.com',
                'password' => 'password',
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

        $mechanic = $this->createUserWithRoles($mechanicRole);

        $mechanicResponse = $this->actingAs($mechanic)->get('/gestiune-piese');
        $mechanicResponse->assertOk();
        $mechanicResponse->assertDontSee('Factură', false);
        $mechanicResponse->assertDontSee('Facturi furnizori', false);
        $mechanicResponse->assertSee('href="' . route('gestiune-piese.index') . '"', false);

        $admin = $this->createUserWithRoles($adminRole);

        $adminResponse = $this->actingAs($admin)->get('/gestiune-piese');
        $adminResponse->assertOk();
        $adminResponse->assertSee('Factură', false);
        $adminResponse->assertSee('Facturi furnizori', false);
    }

    public function test_mechanic_with_document_permissions_gains_document_access(): void
    {
        [, , $mechanicRole] = $this->createCoreRoles();

        $documentWordViewerRole = $this->ensureRole('documente-word-operator', ['documente-word'], [
            'name' => 'Operator Documente Word',
            'description' => 'Poate vizualiza documentele Word disponibile.',
        ]);

        $documentWordManagerRole = $this->ensureRole('documente-word-administrator', ['documente-word', 'documente-word-manage'], [
            'name' => 'Administrator Documente Word',
            'description' => 'Poate gestiona documentele Word disponibile.',
        ]);

        $mechanic = $this->createUserWithRoles($mechanicRole);

        $operatorDocument = DocumentWord::create([
            'nume' => 'Manual Operator',
            'nivel_acces' => 2,
            'continut' => json_encode(['content' => 'operator']),
        ]);

        $adminDocument = DocumentWord::create([
            'nume' => 'Procedura Administrativa',
            'nivel_acces' => 1,
            'continut' => json_encode(['content' => 'admin']),
        ]);

        $this->actingAs($mechanic)->get('/documente-word')->assertForbidden();

        $mechanic->assignRole($documentWordViewerRole);

        $indexResponse = $this->actingAs($mechanic->fresh())->get('/documente-word');
        $indexResponse->assertOk();
        $indexResponse->assertSee($operatorDocument->nume, false);
        $indexResponse->assertDontSee($adminDocument->nume, false);

        $editResponse = $this->actingAs($mechanic->fresh())->get($adminDocument->path() . '/modifica');
        $editResponse->assertForbidden();

        $mechanic->assignRole($documentWordManagerRole);

        $indexResponse = $this->actingAs($mechanic->fresh())->get('/documente-word');
        $indexResponse->assertOk();
        $indexResponse->assertSee($operatorDocument->nume, false);
        $indexResponse->assertSee($adminDocument->nume, false);

        $editResponse = $this->actingAs($mechanic->fresh())->get($adminDocument->path() . '/modifica');
        $editResponse->assertOk();
        $editResponse->assertSee($adminDocument->nume, false);
    }

    public function test_primary_admin_user_is_not_mistaken_for_mechanic_when_role_is_missing(): void
    {
        $adminRole = $this->ensureRole('admin', null, [
            'name' => 'Administrator',
            'description' => 'Administrator access.',
        ]);

        $admin = User::factory()->create(['id' => 1]);
        $admin->assignRole($adminRole);
        $admin->forceFill(['role' => null])->save();

        $this->actingAs($admin)->get('/acasa')->assertOk();
    }

    /**
     * @return array{0: Role, 1: Role, 2: Role}
     */
    private function createCoreRoles(): array
    {
        return [
            $this->ensureRole('super-admin', null, [
                'name' => 'Super Admin',
                'description' => 'Full access.',
            ]),
            $this->ensureRole('admin', null, [
                'name' => 'Administrator',
                'description' => 'Administrator access.',
            ]),
            $this->ensureRole('mecanic', null, [
                'name' => 'Mecanic',
                'description' => 'Acces limitat la gestiune piese și service mașini.',
            ]),
        ];
    }

}
