<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_registered_permission_tree(): void
    {
        $this->actingAs(User::factory()->create())
            ->getJson('/api/permissions')
            ->assertOk()
            ->assertJsonPath('count', 20)
            ->assertJsonPath('data.0.key', 'catalog');
    }

    public function test_it_persists_a_custom_role_using_registered_permissions(): void
    {
        $this->actingAs(User::factory()->create())
            ->postJson('/api/roles', [
                'name' => 'Store manager',
                'access' => 'custom',
                'permissions' => ['catalog.attributes.view', 'settings.users.view'],
            ])
            ->assertCreated()
            ->assertJsonPath('data.name', 'Store manager');

        $this->assertDatabaseHas('roles', ['name' => 'Store manager', 'access' => 'custom']);
        $this->assertSame(['catalog.attributes.view', 'settings.users.view'], Role::firstOrFail()->permissions->getArrayCopy());
    }

    public function test_it_rejects_unregistered_custom_permissions(): void
    {
        $this->actingAs(User::factory()->create())
            ->postJson('/api/roles', [
                'name' => 'Invalid role',
                'access' => 'custom',
                'permissions' => ['inventory.approve'],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['permissions.0']);

        $this->assertDatabaseMissing('roles', ['name' => 'Invalid role']);
    }
}
