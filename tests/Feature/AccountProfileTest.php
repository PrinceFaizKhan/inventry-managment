<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AccountProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_view_account_page(): void
    {
        $this->get('/account')->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_account_page(): void
    {
        $user = User::factory()->create([
            'name' => 'Faizan Khan',
            'email' => 'faizan@test.com',
        ]);

        $this->actingAs($user)
            ->get('/account')
            ->assertOk()
            ->assertSee('My Account')
            ->assertSee('Faizan Khan')
            ->assertSee('faizan@test.com');
    }

    public function test_user_can_update_profile_name_and_email(): void
    {
        $user = User::factory()->create([
            'name' => 'Original Name',
            'email' => 'original@test.com',
        ]);

        $response = $this->actingAs($user)->put('/account', [
            'name' => 'Updated Name',
            'email' => 'updated@test.com',
        ]);

        $response->assertRedirect('/account')
            ->assertSessionHas('success', 'Profile updated successfully.');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'email' => 'updated@test.com',
        ]);
    }

    public function test_user_can_update_password_with_valid_current_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('old-secret-password'),
        ]);

        $response = $this->actingAs($user)->put('/account', [
            'name' => $user->name,
            'email' => $user->email,
            'current_password' => 'old-secret-password',
            'password' => 'new-secret-password',
            'password_confirmation' => 'new-secret-password',
        ]);

        $response->assertRedirect('/account')
            ->assertSessionHas('success');

        $this->assertTrue(Hash::check('new-secret-password', $user->fresh()->password));
    }

    public function test_user_cannot_update_password_with_invalid_current_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('old-secret-password'),
        ]);

        $response = $this->actingAs($user)->put('/account', [
            'name' => $user->name,
            'email' => $user->email,
            'current_password' => 'wrong-password',
            'password' => 'new-secret-password',
            'password_confirmation' => 'new-secret-password',
        ]);

        $response->assertSessionHasErrors(['current_password']);
        $this->assertTrue(Hash::check('old-secret-password', $user->fresh()->password));
    }
}
