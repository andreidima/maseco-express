<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesUsersWithRoles;
use Tests\TestCase;

class UserNavigationTest extends TestCase
{
    use RefreshDatabase;
    use CreatesUsersWithRoles;

    public function test_mechanic_navigation_updates_with_new_permissions(): void
    {
        $mechanic = $this->createUserWithRoles('mecanic');

        $response = $this->actingAs($mechanic)->get(route('gestiune-piese.index'));

        $response->assertOk();
        $response->assertSee('href="' . route('gestiune-piese.index') . '"', false);
        $response->assertSee('href="' . route('service-masini.index') . '"', false);
        $response->assertDontSee('href="/comenzi"', false);
        $response->assertDontSee('href="/file-manager-personalizat"', false);

        $mechanic->assignRole($this->ensureRole('dispecer'));

        $updatedResponse = $this->actingAs($mechanic->fresh())->get(route('gestiune-piese.index'));

        $updatedResponse->assertOk();
        $updatedResponse->assertSee('href="/comenzi"', false);
        $updatedResponse->assertDontSee('href="/file-manager-personalizat"', false);
    }
}
