<?php

namespace Tests\Feature\Permissions;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class PermissionMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @dataProvider routePermissions
     */
    public function test_routes_define_permission_middleware(string $method, string $uri, string $permission): void
    {
        $route = Route::getRoutes()->match(Request::create($uri, $method));

        $this->assertContains("permission:$permission", $route->gatherMiddleware());
    }

    /**
     * @dataProvider routePermissions
     */
    public function test_permission_middleware_blocks_unauthorized_users(string $method, string $uri, string $permission): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->json($method, $uri);

        $response->assertForbidden();
    }

    public static function routePermissions(): array
    {
        return [
            'dashboard' => ['GET', '/acasa', 'dashboard'],
            'document manager' => ['GET', '/file-manager-personalizat', 'documente'],
            'document word library' => ['GET', '/documente-word', 'documente-word'],
            'companies' => ['GET', '/firme/transportatori', 'firme'],
            'trucks' => ['GET', '/camioane', 'camioane'],
            'operation sites' => ['GET', '/locuri-operare', 'locuri-operare'],
            'orders' => ['GET', '/comenzi', 'comenzi'],
            'supplier invoices' => ['GET', '/facturi-furnizori/facturi', 'facturi-furnizori'],
            'invoices' => ['GET', '/facturi', 'facturi'],
            'messages' => ['GET', '/mesaje-trimise-sms', 'mesagerie'],
            'reminders' => ['GET', '/mementouri/internal/mementouri', 'mementouri'],
            'reports' => ['GET', '/rapoarte/incasari-utilizatori', 'rapoarte'],
            'service inventory' => ['GET', '/gestiune-piese', 'gestiune-piese'],
            'vehicle service' => ['GET', '/service-masini', 'service-masini'],
            'user management' => ['GET', '/utilizatori', 'users'],
            'tech impersonation' => ['GET', '/tech/impersonation', 'tech-tools'],
            'fuel stations' => ['GET', '/statii-peco', 'comenzi'],
        ];
    }
}
