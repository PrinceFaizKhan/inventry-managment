<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_receives_configured_navigation(): void
    {
        $response = $this->actingAs(User::factory()->create())->getJson('/api/navigation');

        $response->assertOk()->assertJsonPath('data.0.key', 'dashboard');
        $this->assertSame('settings', $response->json('data.4.key'));
        $this->assertSame('settings.users', $response->json('data.4.children.0.key'));
    }

    public function test_navigation_requires_authentication(): void
    {
        $this->getJson('/api/navigation')->assertUnauthorized();
    }
}
