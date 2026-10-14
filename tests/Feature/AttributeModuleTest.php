<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Catalog\Models\Attribute;
use Modules\Catalog\Models\AttributeOption;
use Tests\TestCase;

class AttributeModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_administration_user_creates_attribute_with_authoritative_normalized_code(): void
    {
        $user = $this->userWithRole('administration');

        $response = $this->actingAs($user)->postJson('/api/catalog/attributes', [
            'name' => 'Short Description',
            'type' => 'textarea',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.code', 'short_description')
            ->assertJsonPath('data.required', false);

        $attribute = Attribute::query()->where('code', 'short_description')->firstOrFail();
        $this->assertModelExists($attribute);
        $this->assertSame('textarea', $attribute->type);
    }

    public function test_create_type_endpoint_returns_only_selectable_type_labels(): void
    {
        $user = $this->userWithRole('administration');

        $response = $this->actingAs($user)->getJson('/api/catalog/attribute-types')->assertOk();

        $this->assertSame([
            'text' => 'Text',
            'textarea' => 'Textarea',
            'boolean' => 'Boolean',
            'checkbox' => 'Checkbox',
            'select' => 'Select',
            'multiselect' => 'Multiselect',
            'image' => 'Image',
            'gallery' => 'Gallery',
            'radio' => 'Radio',
            'money' => 'Money',
        ], $response->json('data'));
    }

    public function test_create_rejects_a_name_that_generates_an_invalid_code_with_a_field_error(): void
    {
        $user = $this->userWithRole('administration');

        $response = $this->actingAs($user)->postJson('/api/catalog/attributes', [
            'name' => '123 !!!',
            'type' => 'text',
        ]);

        $response->assertUnprocessable()->assertJsonValidationErrors(['code']);
        $this->assertStringContainsString(
            'This name does not produce a valid attribute code.',
            $response->json('errors.code.0') ?? '',
        );

        $this->assertDatabaseCount('attributes', 0);
    }

    public function test_create_rejects_duplicate_generated_code_with_a_field_error(): void
    {
        $user = $this->userWithRole('administration');
        Attribute::factory()->create(['name' => 'Short Description', 'code' => 'short_description']);

        $response = $this->actingAs($user)->postJson('/api/catalog/attributes', [
            'name' => 'Short Description',
            'type' => 'text',
        ]);

        $response->assertUnprocessable()->assertJsonValidationErrors(['code']);
        $this->assertSame('This attribute code already exists. Choose a different name.', $response->json('errors.code.0'));

        $this->assertDatabaseCount('attributes', 1);
    }

    public function test_create_rejects_submitted_code_so_backend_remains_authoritative(): void
    {
        $user = $this->userWithRole('administration');

        $this->actingAs($user)->postJson('/api/catalog/attributes', [
            'name' => 'Short Description',
            'type' => 'text',
            'code' => 'caller_supplied',
        ])->assertUnprocessable()->assertJsonValidationErrors(['code']);

        $this->assertDatabaseCount('attributes', 0);
    }

    public function test_custom_user_without_create_permission_receives_forbidden_response(): void
    {
        $user = $this->userWithRole('custom', []);

        $this->actingAs($user)->postJson('/api/catalog/attributes', [
            'name' => 'Color',
            'type' => 'select',
        ])->assertForbidden();

        $this->assertDatabaseCount('attributes', 0);
    }

    public function test_view_only_permission_hides_create_action_and_rejects_type_configuration_request(): void
    {
        $user = $this->userWithRole('custom', ['catalog.attributes.view']);
        $attribute = Attribute::factory()->create(['name' => 'Visible field']);

        $this->actingAs($user)->getJson('/api/grids/attributes')
            ->assertOk()
            ->assertJsonPath('meta.actions.create', false)
            ->assertJsonPath('meta.actions.edit', false)
            ->assertJsonPath('meta.actions.delete', false);

        $this->getJson('/api/catalog/attribute-types')->assertForbidden();
        $this->putJson("/api/catalog/attributes/{$attribute->id}", [
            'name' => 'Changed name',
            'required' => true,
            'settings' => ['validation' => 'any'],
        ])->assertForbidden();

        $this->assertDatabaseHas('attributes', ['id' => $attribute->id, 'name' => 'Visible field']);
    }

    public function test_update_changes_name_and_settings_without_regenerating_code_or_type(): void
    {
        $user = $this->userWithRole('administration');
        $attribute = Attribute::factory()->create([
            'name' => 'Old name',
            'code' => 'permanent_code',
            'type' => 'text',
            'settings' => ['validation' => 'any'],
        ]);

        $this->actingAs($user)->putJson("/api/catalog/attributes/{$attribute->id}", [
            'name' => 'New Display Name',
            'required' => true,
            'settings' => ['validation' => 'email', 'min_length' => 4],
        ])->assertOk()->assertJsonPath('data.code', 'permanent_code');

        $this->assertDatabaseHas('attributes', [
            'id' => $attribute->id,
            'name' => 'New Display Name',
            'code' => 'permanent_code',
            'type' => 'text',
            'required' => true,
        ]);
    }

    public function test_update_rejects_attempt_to_change_the_read_only_code(): void
    {
        $user = $this->userWithRole('administration');
        $attribute = Attribute::factory()->create(['code' => 'permanent_code']);

        $this->actingAs($user)->putJson("/api/catalog/attributes/{$attribute->id}", [
            'name' => $attribute->name,
            'required' => false,
            'settings' => ['validation' => 'any'],
            'code' => 'changed_code',
        ])->assertUnprocessable()->assertJsonValidationErrors(['code']);

        $this->assertDatabaseHas('attributes', ['id' => $attribute->id, 'code' => 'permanent_code']);
    }

    public function test_select_option_updates_preserve_option_identity_and_save_order(): void
    {
        $user = $this->userWithRole('administration');
        $attribute = Attribute::factory()->create(['type' => 'select']);
        $first = AttributeOption::factory()->for($attribute)->create(['code' => 'red', 'label' => 'Red', 'sort_order' => 0]);
        $second = AttributeOption::factory()->for($attribute)->create(['code' => 'blue', 'label' => 'Blue', 'sort_order' => 1]);

        $this->actingAs($user)->putJson("/api/catalog/attributes/{$attribute->id}", [
            'name' => $attribute->name,
            'required' => false,
            'settings' => [],
            'options' => [
                ['id' => $second->id, 'code' => 'blue', 'label' => 'Navy', 'sort_order' => 0],
                ['id' => $first->id, 'code' => 'red', 'label' => 'Red', 'sort_order' => 1],
            ],
        ])->assertOk();

        $this->assertDatabaseHas('attribute_options', ['id' => $second->id, 'code' => 'blue', 'label' => 'Navy', 'sort_order' => 0]);
        $this->assertDatabaseHas('attribute_options', ['id' => $first->id, 'code' => 'red', 'sort_order' => 1]);
    }

    public function test_multiselect_and_radio_attributes_save_configured_options(): void
    {
        $user = $this->userWithRole('administration');
        $this->actingAs($user);

        foreach (['multiselect', 'radio'] as $type) {
            $attribute = Attribute::factory()->create(['type' => $type]);

            $this->putJson("/api/catalog/attributes/{$attribute->id}", [
                'name' => $attribute->name,
                'required' => false,
                'settings' => [],
                'options' => [
                    ['code' => 'first', 'label' => 'First', 'sort_order' => 0],
                    ['code' => 'second', 'label' => 'Second', 'sort_order' => 1],
                ],
            ])->assertOk();

            $this->assertDatabaseHas('attribute_options', [
                'attribute_id' => $attribute->id,
                'code' => 'first',
                'label' => 'First',
            ]);
        }
    }

    public function test_update_rejects_duplicate_option_codes_without_changing_saved_options(): void
    {
        $user = $this->userWithRole('administration');
        $attribute = Attribute::factory()->create(['type' => 'checkbox']);
        $first = AttributeOption::factory()->for($attribute)->create(['code' => 'one']);
        $second = AttributeOption::factory()->for($attribute)->create(['code' => 'two']);

        $this->actingAs($user)->putJson("/api/catalog/attributes/{$attribute->id}", [
            'name' => $attribute->name,
            'required' => false,
            'settings' => [],
            'options' => [
                ['id' => $first->id, 'code' => 'one', 'label' => 'One', 'sort_order' => 0],
                ['id' => $second->id, 'code' => 'one', 'label' => 'Two', 'sort_order' => 1],
            ],
        ])->assertUnprocessable()->assertJsonValidationErrors(['options.1.code']);

        $this->assertDatabaseHas('attribute_options', ['id' => $second->id, 'code' => 'two']);
    }

    public function test_update_rejects_settings_not_supported_by_attribute_type(): void
    {
        $user = $this->userWithRole('administration');
        $attribute = Attribute::factory()->create(['type' => 'boolean']);

        $this->actingAs($user)->putJson("/api/catalog/attributes/{$attribute->id}", [
            'name' => $attribute->name,
            'required' => false,
            'settings' => ['max_images' => 4],
        ])->assertUnprocessable()->assertJsonValidationErrors(['settings.max_images']);
    }

    public function test_url_validation_rejects_inapplicable_text_length_settings(): void
    {
        $user = $this->userWithRole('administration');
        $attribute = Attribute::factory()->create(['type' => 'text']);

        $this->actingAs($user)->putJson("/api/catalog/attributes/{$attribute->id}", [
            'name' => $attribute->name,
            'required' => false,
            'settings' => ['validation' => 'url', 'min_length' => 5],
        ])->assertUnprocessable()->assertJsonValidationErrors(['settings.min_length']);
    }

    public function test_attribute_grid_searches_and_filters_using_allowlisted_fields(): void
    {
        $user = $this->userWithRole('administration');
        Attribute::factory()->create(['name' => 'Primary shade', 'code' => 'primary_shade', 'type' => 'select', 'required' => true]);
        Attribute::factory()->create(['name' => 'Details', 'code' => 'details', 'type' => 'textarea', 'required' => false]);

        $this->actingAs($user)->getJson('/api/grids/attributes?search=shade&filter[type]=select&filter[required]=1&sort=code&direction=asc')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.code', 'primary_shade')
            ->assertJsonPath('meta.actions.create', true);

        $this->actingAs($user)->getJson('/api/grids/attributes?sort=settings')
            ->assertUnprocessable()->assertJsonValidationErrors(['sort']);
    }

    public function test_attribute_delete_removes_its_options_and_requires_delete_permission(): void
    {
        $user = $this->userWithRole('administration');
        $attribute = Attribute::factory()->create(['type' => 'select']);
        $option = AttributeOption::factory()->for($attribute)->create();

        $this->actingAs($user)->deleteJson("/api/catalog/attributes/{$attribute->id}")->assertNoContent();

        $this->assertDatabaseMissing('attributes', ['id' => $attribute->id]);
        $this->assertDatabaseMissing('attribute_options', ['id' => $option->id]);
    }

    public function test_update_can_toggle_is_filterable_flag(): void
    {
        $user = $this->userWithRole('administration');
        $attribute = Attribute::factory()->create(['name' => 'Brand', 'code' => 'brand', 'is_filterable' => false]);

        $this->actingAs($user)->putJson("/api/catalog/attributes/{$attribute->id}", [
            'name' => 'Brand',
            'required' => false,
            'is_filterable' => true,
            'settings' => ['validation' => 'any'],
        ])->assertOk();

        $this->assertDatabaseHas('attributes', [
            'id' => $attribute->id,
            'is_filterable' => true,
        ]);
    }

    public function test_attribute_grid_can_filter_by_is_filterable(): void
    {
        $user = $this->userWithRole('administration');
        Attribute::factory()->create(['name' => 'Brand', 'code' => 'brand', 'is_filterable' => true]);
        Attribute::factory()->create(['name' => 'Internal Note', 'code' => 'internal_note', 'is_filterable' => false]);

        $this->actingAs($user)->getJson('/api/grids/attributes?filter[is_filterable]=1')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.code', 'brand')
            ->assertJsonPath('data.0.is_filterable', true);

        $this->actingAs($user)->getJson('/api/grids/attributes?filter[is_filterable]=0')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.code', 'internal_note')
            ->assertJsonPath('data.0.is_filterable', false);
    }

    public function test_filterable_endpoint_returns_only_filterable_attributes(): void
    {
        $user = $this->userWithRole('administration');
        $filterable = Attribute::factory()->create(['name' => 'Color', 'code' => 'color', 'type' => 'select', 'is_filterable' => true]);
        AttributeOption::factory()->for($filterable)->create(['code' => 'red', 'label' => 'Red', 'sort_order' => 0]);
        Attribute::factory()->create(['name' => 'Internal Note', 'code' => 'internal_note', 'type' => 'textarea', 'is_filterable' => false]);

        $this->actingAs($user)->getJson('/api/catalog/attributes/filterable')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.code', 'color')
            ->assertJsonPath('data.0.is_filterable', true)
            ->assertJsonCount(1, 'data.0.options')
            ->assertJsonPath('data.0.options.0.code', 'red');
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
