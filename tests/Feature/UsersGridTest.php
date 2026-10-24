<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\Support\Grid\GridRegistry;
use Tests\Support\SelectableUsersGridRegistry;
use Tests\TestCase;

class UsersGridTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_search_filter_sort_and_paginate_users(): void
    {
        $manager = Role::query()->create(['name' => 'Manager', 'access' => 'custom', 'permissions' => []]);
        $viewer = Role::query()->create(['name' => 'Viewer', 'access' => 'custom', 'permissions' => []]);
        $currentUser = User::factory()->create();
        User::factory()->create(['name' => 'Zara Merchant', 'email' => 'zara@example.test', 'role_id' => $manager->id]);
        User::factory()->create(['name' => 'Adeel Khan', 'email' => 'adeel@example.test', 'role_id' => $manager->id]);
        User::factory()->create(['name' => 'Inactive User', 'status' => 'inactive', 'role_id' => $viewer->id]);

        $response = $this->actingAs($currentUser)->getJson('/api/grids/users?search=example.test&filter[role_id]='.$manager->id.'&sort=name&direction=desc&per_page=10&page=1');

        $response->assertOk()
            ->assertJsonPath('meta.total', 2)
            ->assertJsonPath('meta.current_page', 1)
            ->assertJsonPath('meta.per_page', 10)
            ->assertJsonPath('data.0.name', 'Zara Merchant')
            ->assertJsonPath('data.1.name', 'Adeel Khan')
            ->assertJsonPath('data.0.role', 'Manager')
            ->assertJsonPath('meta.filter_options.role_id.0.label', 'Manager');
    }

    public function test_users_grid_rejects_unknown_sort_and_filter_values(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->getJson('/api/grids/users?sort=password')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['sort']);

        $this->actingAs($user)->getJson('/api/grids/users?filter[status]=suspended')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['filter.status']);

        $this->actingAs($user)->getJson('/api/grids/users?per_page=100&page=0')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['per_page', 'page']);
    }

    public function test_users_grid_returns_honest_empty_pagination(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->getJson('/api/grids/users?search=does-not-exist')
            ->assertOk()
            ->assertJsonPath('data', [])
            ->assertJsonPath('meta.total', 0)
            ->assertJsonPath('meta.last_page', 1);
    }

    public function test_users_grid_requires_authentication(): void
    {
        $this->getJson('/api/grids/users')->assertUnauthorized();
    }

    public function test_users_grid_configuration_keeps_selection_disabled(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->getJson('/api/grids/users')
            ->assertOk()
            ->assertJsonPath('meta.selectable', false)
            ->assertJsonPath('data.0.selectable', false);
    }

    public function test_bulk_edit_updates_only_submitted_fields_for_a_test_selectable_grid(): void
    {
        $actor = User::factory()->create();
        $selected = User::factory()->create(['name' => 'Selected user', 'status' => 'active']);
        $this->app->instance(GridRegistry::class, new SelectableUsersGridRegistry);

        $this->actingAs($actor)->postJson('/api/grids/users/bulk/edit_selected', [
            'ids' => [$selected->id],
            'changes' => ['status' => 'inactive'],
        ])->assertOk()->assertJsonPath('updated', 1);

        $this->assertDatabaseHas('users', ['id' => $selected->id, 'status' => 'inactive', 'role_id' => null]);
    }

    public function test_bulk_endpoint_rejects_unsafe_fields_and_duplicate_ids(): void
    {
        $actor = User::factory()->create();
        $selected = User::factory()->create();
        $this->app->instance(GridRegistry::class, new SelectableUsersGridRegistry);

        $this->actingAs($actor)->postJson('/api/grids/users/bulk/edit_selected', [
            'ids' => [$selected->id],
            'changes' => ['email' => 'changed@example.test'],
        ])->assertUnprocessable()->assertJsonValidationErrors(['changes.email']);

        $this->actingAs($actor)->postJson('/api/grids/users/bulk/deactivate_selected', [
            'ids' => [$selected->id, $selected->id],
        ])->assertUnprocessable()->assertJsonValidationErrors(['ids.1']);

        $this->assertDatabaseHas('users', ['id' => $selected->id, 'email' => $selected->email, 'status' => 'active']);
    }

    public function test_bulk_endpoint_rejects_unresolved_action_permissions(): void
    {
        $actor = User::factory()->create();
        $selected = User::factory()->create();
        $this->app->instance(GridRegistry::class, new SelectableUsersGridRegistry(deletePermission: 'settings.users.delete'));

        $this->actingAs($actor)->postJson('/api/grids/users/bulk/delete_selected', ['ids' => [$selected->id]])
            ->assertForbidden();

        $this->assertDatabaseHas('users', ['id' => $selected->id]);
    }

    public function test_bulk_edit_rejects_an_unresolved_field_permission(): void
    {
        $actor = User::factory()->create();
        $selected = User::factory()->create();
        $this->app->instance(GridRegistry::class, new SelectableUsersGridRegistry(fieldPermission: 'settings.users.assign-role'));

        $this->actingAs($actor)->postJson('/api/grids/users/bulk/edit_selected', [
            'ids' => [$selected->id],
            'changes' => ['role_id' => null],
        ])->assertForbidden();

        $this->assertDatabaseHas('users', ['id' => $selected->id, 'role_id' => null]);
    }

    public function test_bulk_endpoint_rejects_the_current_users_record(): void
    {
        $actor = User::factory()->create();
        $this->app->instance(GridRegistry::class, new SelectableUsersGridRegistry);

        $this->actingAs($actor)->postJson('/api/grids/users/bulk/deactivate_selected', ['ids' => [$actor->id]])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['ids']);

        $this->assertDatabaseHas('users', ['id' => $actor->id, 'status' => 'active']);
    }

    public function test_bulk_delete_removes_only_the_selected_records(): void
    {
        $actor = User::factory()->create();
        $selected = User::factory()->create();
        $unselected = User::factory()->create();
        $this->app->instance(GridRegistry::class, new SelectableUsersGridRegistry);

        $this->actingAs($actor)->postJson('/api/grids/users/bulk/delete_selected', ['ids' => [$selected->id]])
            ->assertOk()
            ->assertJsonPath('updated', 1)
            ->assertJsonPath('message', '1 selected record deleted.');

        $this->assertDatabaseMissing('users', ['id' => $selected->id]);
        $this->assertDatabaseHas('users', ['id' => $unselected->id]);
    }

    public function test_user_status_can_be_toggled_between_active_and_inactive(): void
    {
        $actor = User::factory()->create();
        $target = User::factory()->create(['status' => 'active']);

        $res1 = $this->actingAs($actor)->postJson("/api/settings/users/{$target->id}/toggle-status");
        $res1->assertOk()
            ->assertJsonPath('status', 'inactive')
            ->assertJsonPath('message', "User {$target->name} deactivated successfully.");

        $this->assertDatabaseHas('users', ['id' => $target->id, 'status' => 'inactive']);

        $res2 = $this->actingAs($actor)->postJson("/api/settings/users/{$target->id}/toggle-status");
        $res2->assertOk()
            ->assertJsonPath('status', 'active')
            ->assertJsonPath('message', "User {$target->name} activated successfully.");

        $this->assertDatabaseHas('users', ['id' => $target->id, 'status' => 'active']);
    }

    public function test_user_cannot_deactivate_their_own_account(): void
    {
        $actor = User::factory()->create(['status' => 'active']);

        $res = $this->actingAs($actor)->postJson("/api/settings/users/{$actor->id}/toggle-status");
        $res->assertStatus(422)
            ->assertJsonPath('message', 'You cannot deactivate your own account.');

        $this->assertDatabaseHas('users', ['id' => $actor->id, 'status' => 'active']);
    }

    public function test_cannot_deactivate_last_active_super_admin(): void
    {
        $superAdminRole = Role::query()->create(['name' => 'Super Admin', 'access' => 'all', 'permissions' => []]);
        $actor = User::factory()->create();
        $soloSuperAdmin = User::factory()->create(['status' => 'active', 'role_id' => $superAdminRole->id]);

        $res = $this->actingAs($actor)->postJson("/api/settings/users/{$soloSuperAdmin->id}/toggle-status");
        $res->assertStatus(422)
            ->assertJsonPath('message', 'At least one active Super Admin must remain.');

        $this->assertDatabaseHas('users', ['id' => $soloSuperAdmin->id, 'status' => 'active']);
    }

    public function test_user_status_can_be_set_with_explicit_status_payload(): void
    {
        $actor = User::factory()->create();
        $target = User::factory()->create(['status' => 'active']);

        $res1 = $this->actingAs($actor)->postJson("/api/settings/users/{$target->id}/toggle-status", [
            'status' => 'inactive',
        ]);
        $res1->assertOk()
            ->assertJsonPath('status', 'inactive')
            ->assertJsonPath('message', "User {$target->name} deactivated successfully.");

        $this->assertDatabaseHas('users', ['id' => $target->id, 'status' => 'inactive']);

        $res2 = $this->actingAs($actor)->postJson("/api/settings/users/{$target->id}/toggle-status", [
            'status' => 'active',
        ]);
        $res2->assertOk()
            ->assertJsonPath('status', 'active')
            ->assertJsonPath('message', "User {$target->name} activated successfully.");

        $this->assertDatabaseHas('users', ['id' => $target->id, 'status' => 'active']);
    }

    public function test_user_status_can_be_activated_and_deactivated_via_dedicated_routes(): void
    {
        $actor = User::factory()->create();
        $target = User::factory()->create(['status' => 'active']);

        $res1 = $this->actingAs($actor)->postJson("/api/settings/users/{$target->id}/deactivate");
        $res1->assertOk()
            ->assertJsonPath('status', 'inactive');

        $this->assertDatabaseHas('users', ['id' => $target->id, 'status' => 'inactive']);

        $res2 = $this->actingAs($actor)->postJson("/api/settings/users/{$target->id}/activate");
        $res2->assertOk()
            ->assertJsonPath('status', 'active');

        $this->assertDatabaseHas('users', ['id' => $target->id, 'status' => 'active']);
    }

    public function test_user_create_page_renders_cleanly_with_roles(): void
    {
        $actor = User::factory()->create();
        Role::query()->create(['name' => 'Support Staff', 'access' => 'custom', 'permissions' => []]);

        $res = $this->actingAs($actor)->get('/settings/users/create');
        $res->assertOk()
            ->assertViewIs('admin::settings.users.create')
            ->assertViewHas('roles');
    }

    public function test_user_edit_page_renders_cleanly_with_user_and_roles(): void
    {
        $actor = User::factory()->create();
        $target = User::factory()->create(['name' => 'Target User']);

        $res = $this->actingAs($actor)->get("/settings/users/{$target->id}/edit");
        $res->assertOk()
            ->assertViewIs('admin::settings.users.edit')
            ->assertViewHas('user')
            ->assertViewHas('roles');
    }

    public function test_user_can_be_created_via_api_with_valid_data(): void
    {
        $actor = User::factory()->create();
        $role = Role::query()->create(['name' => 'Custom Role', 'access' => 'custom', 'permissions' => []]);

        $res = $this->actingAs($actor)->postJson('/api/settings/users', [
            'name' => 'New Staff User',
            'email' => 'newstaff@example.test',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'role_id' => $role->id,
            'status' => 'active',
        ]);

        $res->assertCreated()
            ->assertJsonPath('data.name', 'New Staff User')
            ->assertJsonPath('data.email', 'newstaff@example.test')
            ->assertJsonPath('data.role', 'Custom Role')
            ->assertJsonPath('data.status', 'active');

        $this->assertDatabaseHas('users', [
            'name' => 'New Staff User',
            'email' => 'newstaff@example.test',
            'role_id' => $role->id,
            'status' => 'active',
        ]);
    }

    public function test_user_creation_validates_required_fields_and_unique_email(): void
    {
        $actor = User::factory()->create(['email' => 'existing@example.test']);

        $res = $this->actingAs($actor)->postJson('/api/settings/users', [
            'name' => '',
            'email' => 'existing@example.test',
            'password' => '123',
            'password_confirmation' => '456',
            'status' => 'invalid-status',
        ]);

        $res->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'email', 'password', 'status']);
    }

    public function test_user_can_be_updated_without_changing_password(): void
    {
        $actor = User::factory()->create();
        $target = User::factory()->create([
            'name' => 'Old Name',
            'password' => 'original_secret',
        ]);
        $originalHash = $target->password;

        $res = $this->actingAs($actor)->putJson("/api/settings/users/{$target->id}", [
            'name' => 'Updated Name',
            'email' => $target->email,
            'status' => 'active',
        ]);

        $res->assertOk()
            ->assertJsonPath('user.name', 'Updated Name');

        $target->refresh();
        $this->assertSame('Updated Name', $target->name);
        $this->assertSame($originalHash, $target->password);
    }

    public function test_user_can_be_updated_with_new_password(): void
    {
        $actor = User::factory()->create();
        $target = User::factory()->create([
            'name' => 'Target User',
            'password' => 'initial_password',
        ]);
        $initialHash = $target->password;

        $res = $this->actingAs($actor)->putJson("/api/settings/users/{$target->id}", [
            'name' => 'Target User',
            'email' => $target->email,
            'status' => 'active',
            'password' => 'new_super_secret',
            'password_confirmation' => 'new_super_secret',
        ]);

        $res->assertOk();

        $target->refresh();
        $this->assertNotSame($initialHash, $target->password);
    }
}
