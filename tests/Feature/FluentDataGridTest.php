<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Attribute\Models\Attribute;
use Tests\TestCase;

class FluentDataGridTest extends TestCase
{
    use RefreshDatabase;

    private function adminUser(): User
    {
        $role = Role::query()->create([
            'name' => 'Admin Role',
            'access' => 'administration',
            'permissions' => [],
        ]);
        $user = User::factory()->create();
        $user->role()->associate($role)->save();

        return $user;
    }

    public function test_attribute_datagrid_returns_json_schema_and_paginated_records(): void
    {
        Attribute::factory()->create(['name' => 'Color Code', 'code' => 'color', 'type' => 'text', 'required' => true, 'is_filterable' => true]);
        Attribute::factory()->create(['name' => 'Size Label', 'code' => 'size', 'type' => 'select', 'required' => false, 'is_filterable' => false]);

        $admin = $this->adminUser();

        $response = $this->actingAs($admin)
            ->getJson('/catalog/attributes');

        $response->assertOk()
            ->assertJsonStructure([
                'data',
                'meta' => [
                    'current_page',
                    'last_page',
                    'per_page',
                    'total',
                    'columns',
                    'filter_options',
                    'actions',
                    'selectable',
                    'bulk_actions',
                ],
            ]);

        $this->assertSame(2, $response->json('meta.total'));
    }

    public function test_attribute_datagrid_supports_search_and_filters(): void
    {
        Attribute::factory()->create(['name' => 'Screen Resolution', 'code' => 'resolution', 'type' => 'text']);
        Attribute::factory()->create(['name' => 'Battery Capacity', 'code' => 'battery', 'type' => 'number']);

        $admin = $this->adminUser();

        // Search test
        $res = $this->actingAs($admin)
            ->getJson('/catalog/attributes?search=battery');

        $res->assertOk();
        $this->assertSame(1, $res->json('meta.total'));
        $this->assertSame('battery', $res->json('data.0.code'));

        // Filter test
        $resFilter = $this->actingAs($admin)
            ->getJson('/catalog/attributes?filter[type]=text');

        $resFilter->assertOk();
        $this->assertSame(1, $resFilter->json('meta.total'));
        $this->assertSame('resolution', $resFilter->json('data.0.code'));
    }

    public function test_user_datagrid_returns_proper_role_and_status_columns(): void
    {
        $role = Role::query()->create(['name' => 'Manager', 'access' => 'custom', 'permissions' => []]);
        $user = User::factory()->create(['name' => 'Haseen Staff', 'email' => 'staff@haseentelecom.com', 'role_id' => $role->id, 'status' => 'active']);

        $response = $this->actingAs($user)
            ->getJson('/settings/users');

        $response->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.name', 'Haseen Staff')
            ->assertJsonPath('data.0.role', 'Manager')
            ->assertJsonPath('data.0.status', 'active');
    }

    public function test_datagrid_renders_html_view_on_standard_browser_get(): void
    {
        $admin = $this->adminUser();

        $response = $this->actingAs($admin)
            ->get('/catalog/attributes');

        $response->assertOk()
            ->assertSee('datagrid-wrapper')
            ->assertSee('Attributes');
    }
}
