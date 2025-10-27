<?php

namespace Tests\Feature\Auth;

use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesUsersWithRoles;
use Tests\TestCase;

class LoginRedirectTest extends TestCase
{
    use RefreshDatabase;
    use CreatesUsersWithRoles;

    public function test_login_redirects_to_intended_url_when_present(): void
    {
        $dispatcherRole = $this->ensureRole('dispecer');
        $mechanicRole = $this->ensureRole('mecanic');

        $user = $this->createUserWithRoles([$dispatcherRole, $mechanicRole], [
            'email' => 'dispatcher@example.com',
        ]);

        $this->withoutMiddleware(VerifyCsrfToken::class);

        $response = $this->withSession(['url.intended' => '/comenzi'])
            ->post('/login', [
                'email' => 'dispatcher@example.com',
                'password' => 'password',
            ]);

        $response->assertRedirect('/comenzi');
        $this->assertAuthenticatedAs($user->fresh());
    }

    public function test_login_redirects_to_dashboard_when_available(): void
    {
        $dispatcherRole = $this->ensureRole('dispecer');
        $mechanicRole = $this->ensureRole('mecanic');

        $user = $this->createUserWithRoles([$dispatcherRole, $mechanicRole], [
            'email' => 'dashboard-user@example.com',
        ]);

        $this->withoutMiddleware(VerifyCsrfToken::class);

        $response = $this->post('/login', [
            'email' => 'dashboard-user@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user->fresh());
    }

    public function test_login_redirects_to_first_accessible_menu_entry_as_fallback(): void
    {
        $mechanicRole = $this->ensureRole('mecanic');

        $user = $this->createUserWithRoles($mechanicRole, [
            'email' => 'mechanic@example.com',
        ]);

        $this->withoutMiddleware(VerifyCsrfToken::class);

        $response = $this->post('/login', [
            'email' => 'mechanic@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('gestiune-piese.index'));
        $this->assertAuthenticatedAs($user->fresh());
    }
}
