<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_when_accessing_dashboard(): void
    {
        $this->get('/dashboard')->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_access_dashboard_html(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk()
            ->assertSee('Admin Workspace')
            ->assertSee('Catalog Attributes')
            ->assertSee('Staff & Administrators', false);
    }

    public function test_authenticated_user_can_access_dashboard_json_stats(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/dashboard');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'total_attributes',
                    'total_users',
                    'total_roles',
                    'filterable_attributes',
                ],
            ]);
    }
}
