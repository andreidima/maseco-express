<?php

namespace Tests\Feature\Service;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GestiunePieseTest extends TestCase
{
    use RefreshDatabase;

    public function test_page_is_accessible_even_without_legacy_table(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('gestiune-piese.index'));

        $response->assertOk();
        $response->assertSee('Gestiune piese');
        $response->assertSee('service_gestiune_piese nu sunt disponibile');
    }
}
