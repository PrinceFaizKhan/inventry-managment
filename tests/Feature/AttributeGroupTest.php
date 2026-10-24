<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Attribute\Database\Seeders\AttributeGroupTableSeeder;
use Modules\Attribute\Models\AttributeGroup;
use Tests\TestCase;

class AttributeGroupTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_attribute_groups_page(): void
    {
        $user = $this->userWithRole('administration');

        $response = $this->actingAs($user)->get('/catalog/attribute-groups');

        $response->assertOk()
            ->assertSee('Attribute Groups');
    }

    public function test_user_can_view_create_group_page(): void
    {
        $user = $this->userWithRole('administration');

        $response = $this->actingAs($user)->get('/catalog/attribute-groups/create');

        $response->assertOk()
            ->assertSee('Create Attribute Group');
    }

    public function test_user_can_view_edit_group_page(): void
    {
        $user = $this->userWithRole('administration');
        $group = AttributeGroup::create(['name' => 'General', 'code' => 'general']);

        $response = $this->actingAs($user)->get("/catalog/attribute-groups/{$group->id}/edit");

        $response->assertOk()
            ->assertSee('Edit Attribute Group')
            ->assertSee('General');
    }

    public function test_user_can_query_attribute_groups_grid(): void
    {
        $user = $this->userWithRole('administration');

        AttributeGroup::create(['name' => 'General Information', 'code' => 'general_info']);
        AttributeGroup::create(['name' => 'Pricing Details', 'code' => 'pricing_details']);

        $response = $this->actingAs($user)->getJson('/api/grids/attribute-groups');

        $response->assertOk()
            ->assertJsonPath('meta.total', 2)
            ->assertJsonPath('data.0.code', 'general_info');
    }

    public function test_user_can_create_attribute_group(): void
    {
        $user = $this->userWithRole('administration');

        $response = $this->actingAs($user)->postJson('/api/catalog/attribute-groups', [
            'name' => 'General',
            'code' => 'general',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'General')
            ->assertJsonPath('data.code', 'general');

        $this->assertDatabaseHas('attribute_groups', [
            'name' => 'General',
            'code' => 'general',
        ]);
    }

    public function test_attribute_group_code_auto_generated_if_omitted(): void
    {
        $user = $this->userWithRole('administration');

        $response = $this->actingAs($user)->postJson('/api/catalog/attribute-groups', [
            'name' => 'Technical Specs',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'Technical Specs')
            ->assertJsonPath('data.code', 'technical_specs');

        $this->assertDatabaseHas('attribute_groups', [
            'name' => 'Technical Specs',
            'code' => 'technical_specs',
        ]);
    }

    public function test_attribute_group_requires_name(): void
    {
        $user = $this->userWithRole('administration');

        $response = $this->actingAs($user)->postJson('/api/catalog/attribute-groups', [
            'name' => '',
            'code' => 'blank_name',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    public function test_attribute_group_code_must_be_unique(): void
    {
        $user = $this->userWithRole('administration');

        AttributeGroup::create(['name' => 'General', 'code' => 'general']);

        $response = $this->actingAs($user)->postJson('/api/catalog/attribute-groups', [
            'name' => 'General Duplicate',
            'code' => 'general',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['code']);
    }

    public function test_user_can_view_single_attribute_group(): void
    {
        $user = $this->userWithRole('administration');
        $group = AttributeGroup::create(['name' => 'Description', 'code' => 'description']);

        $response = $this->actingAs($user)->getJson("/api/catalog/attribute-groups/{$group->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $group->id)
            ->assertJsonPath('data.name', 'Description')
            ->assertJsonPath('data.code', 'description');
    }

    public function test_user_can_update_attribute_group(): void
    {
        $user = $this->userWithRole('administration');
        $group = AttributeGroup::create(['name' => 'Money Old', 'code' => 'money']);

        $response = $this->actingAs($user)->putJson("/api/catalog/attribute-groups/{$group->id}", [
            'name' => 'Money',
            'code' => 'money',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.name', 'Money')
            ->assertJsonPath('data.code', 'money');

        $this->assertDatabaseHas('attribute_groups', [
            'id' => $group->id,
            'name' => 'Money',
            'code' => 'money',
        ]);
    }

    public function test_user_can_delete_attribute_group(): void
    {
        $user = $this->userWithRole('administration');
        $group = AttributeGroup::create(['name' => 'Obsolete Group', 'code' => 'obsolete']);

        $response = $this->actingAs($user)->deleteJson("/api/catalog/attribute-groups/{$group->id}");

        $response->assertNoContent();
        $this->assertDatabaseMissing('attribute_groups', [
            'id' => $group->id,
        ]);
    }

    public function test_seeder_creates_default_groups(): void
    {
        $this->seed(AttributeGroupTableSeeder::class);

        $this->assertDatabaseHas('attribute_groups', ['code' => 'general', 'name' => 'General']);
        $this->assertDatabaseHas('attribute_groups', ['code' => 'description', 'name' => 'Description']);
        $this->assertDatabaseHas('attribute_groups', ['code' => 'money', 'name' => 'Money']);
    }

    /** @param list<string> $permissions */
    private function userWithRole(string $access, array $permissions = []): User
    {
        $role = Role::query()->create([
            'name' => fake()->unique()->jobTitle(),
            'access' => $access,
            'permissions' => $permissions,
        ]);
        $user = User::factory()->create();
        $user->role()->associate($role)->save();

        return $user;
    }
}
