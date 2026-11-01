<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminSessionTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_admin_login_page(): void
    {
        $response = $this->get('/login');

        $response->assertOk()
            ->assertSee('Admin Workspace')
            ->assertSee('Sign in')
            ->assertSee('name="email"', false)
            ->assertSee('action="http://localhost:8000/login"', false);
    }

    public function test_authenticated_admin_visiting_login_redirects_to_dashboard(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/login')
            ->assertRedirect('/dashboard');
    }

    public function test_admin_can_authenticate_via_session_store_and_redirect_to_dashboard(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@haseentelecom.test',
            'password' => Hash::make('password'),
        ]);

        $response = $this->post('/login', [
            'email' => 'admin@haseentelecom.test',
            'password' => 'password',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_admin_receives_validation_error_on_invalid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@haseentelecom.test',
            'password' => Hash::make('correct-password'),
        ]);

        $response = $this->from('/login')->post('/login', [
            'email' => 'admin@haseentelecom.test',
            'password' => 'wrong-password',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_admin_can_logout_and_redirects_to_root(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect('/');
        $this->assertGuest();
    }
}
