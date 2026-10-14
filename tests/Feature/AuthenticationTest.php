<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_sign_in_with_valid_credentials(): void
    {
        $user = User::factory()->create(['password' => Hash::make('correct-password')]);

        $this->postJson('/login', [
            'email' => $user->email,
            'password' => 'correct-password',
        ])->assertOk()->assertJsonPath('user.id', $user->id);

        $this->assertAuthenticatedAs($user);
    }

    public function test_login_requires_an_email_and_password(): void
    {
        $this->postJson('/login', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_browser_form_submission_logs_in_and_redirects_to_dashboard(): void
    {
        $user = User::factory()->create(['password' => Hash::make('correct-password')]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'correct-password',
        ])->assertRedirect('/dashboard');

        $this->assertAuthenticatedAs($user);
    }

    public function test_authenticated_user_visiting_home_redirects_to_dashboard(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/')
            ->assertRedirect('/dashboard');
    }

    public function test_browser_logout_redirects_to_root(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/logout')
            ->assertRedirect('/');

        $this->assertGuest();
    }
}
