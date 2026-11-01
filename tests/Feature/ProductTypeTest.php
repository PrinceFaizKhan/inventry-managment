<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Attribute\Models\Attribute;
use Modules\Attribute\Models\AttributeGroup;
use Modules\ProductTypes\Database\Seeders\DefaultProductTypeSeeder;
use Modules\ProductTypes\Models\ProductType;
use Tests\TestCase;

class ProductTypeTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_product_types_page(): void
    {
        $user = $this->userWithRole('administration');

        $response = $this->actingAs($user)->get('/catalog/product-types');

        $response->assertOk()
            ->assertSee('Product Types');
    }

    public function test_user_can_view_create_product_type_page(): void
    {
        $user = $this->userWithRole('administration');

        $response = $this->actingAs($user)->get('/catalog/product-types/create');

        $response->assertOk()
            ->assertSee('Create Product Type');
    }

    public function test_user_can_view_edit_product_type_page(): void
    {
        $user = $this->userWithRole('administration');
        $productType = ProductType::create(['name' => 'Mobile Phones', 'code' => 'mobile_phones']);

        $response = $this->actingAs($user)->get("/catalog/product-types/{$productType->id}/edit");

        $response->assertOk()
            ->assertSee('Edit Product Type')
            ->assertSee('Mobile Phones');
    }

    public function test_user_can_query_product_types_grid(): void
    {
        $user = $this->userWithRole('administration');

        $pt1 = ProductType::create(['name' => 'Default', 'code' => 'default']);
        $pt2 = ProductType::create(['name' => 'Computers', 'code' => 'computers']);

        $group = AttributeGroup::create(['name' => 'General', 'code' => 'general']);
        $pt1->groups()->create(['attribute_group_id' => $group->id, 'position' => 1]);

        $response = $this->actingAs($user)->getJson('/api/grids/product-types');

        $response->assertOk()
            ->assertJsonPath('meta.total', 2)
            ->assertJsonPath('data.0.name', 'Computers')
            ->assertJsonPath('data.1.name', 'Default')
            ->assertJsonPath('data.1.groups_count', 1);
    }

    public function test_user_can_filter_product_types_grid_by_groups_and_variants(): void
    {
        $user = $this->userWithRole('administration');

        $pt1 = ProductType::create(['name' => 'Default', 'code' => 'default']);
        $pt2 = ProductType::create(['name' => 'Smartphones', 'code' => 'smartphones']);

        $group = AttributeGroup::create(['name' => 'General', 'code' => 'general']);
        $pt1->groups()->create(['attribute_group_id' => $group->id, 'position' => 1]);

        $response = $this->actingAs($user)->getJson('/api/grids/product-types?filter[has_groups]=1');
        $response->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.name', 'Default');

        $responseEmpty = $this->actingAs($user)->getJson('/api/grids/product-types?filter[has_groups]=0');
        $responseEmpty->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.name', 'Smartphones');
    }

    public function test_user_can_create_product_type_with_groups_and_attributes(): void
    {
        $user = $this->userWithRole('administration');

        $group1 = AttributeGroup::create(['name' => 'General', 'code' => 'general']);
        $group2 = AttributeGroup::create(['name' => 'Description', 'code' => 'description']);

        $attr1 = Attribute::create(['name' => 'SKU', 'code' => 'sku', 'type' => 'text']);
        $attr2 = Attribute::create(['name' => 'Product Name', 'code' => 'name', 'type' => 'text']);
        $attr3 = Attribute::create(['name' => 'Short Description', 'code' => 'short_description', 'type' => 'textarea']);

        $payload = [
            'name' => 'Mobile Phones',
            'code' => 'mobile_phones',
            'groups' => [
                [
                    'attribute_group_id' => $group1->id,
                    'position' => 1,
                    'attributes' => [
                        ['id' => $attr1->id, 'position' => 1],
                        ['id' => $attr2->id, 'position' => 2],
                    ],
                ],
                [
                    'attribute_group_id' => $group2->id,
                    'position' => 2,
                    'attributes' => [
                        ['id' => $attr3->id, 'position' => 1],
                    ],
                ],
            ],
        ];

        $response = $this->actingAs($user)->postJson('/api/catalog/product-types', $payload);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'Mobile Phones')
            ->assertJsonPath('data.code', 'mobile_phones')
            ->assertJsonPath('data.groups_count', 2)
            ->assertJsonPath('data.attributes_count', 3);

        $this->assertDatabaseHas('product_types', [
            'name' => 'Mobile Phones',
            'code' => 'mobile_phones',
        ]);

        $pt = ProductType::where('code', 'mobile_phones')->first();
        $this->assertNotNull($pt);
        $this->assertCount(2, $pt->groups);
        $this->assertCount(3, $pt->allGroupAttributes);
    }

    public function test_product_type_code_auto_generated_if_omitted(): void
    {
        $user = $this->userWithRole('administration');

        $response = $this->actingAs($user)->postJson('/api/catalog/product-types', [
            'name' => 'Smart Watches',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'Smart Watches')
            ->assertJsonPath('data.code', 'smart_watches');

        $this->assertDatabaseHas('product_types', [
            'name' => 'Smart Watches',
            'code' => 'smart_watches',
        ]);
    }

    public function test_product_type_requires_name(): void
    {
        $user = $this->userWithRole('administration');

        $response = $this->actingAs($user)->postJson('/api/catalog/product-types', [
            'name' => '',
            'code' => 'invalid',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    public function test_product_type_code_must_be_unique(): void
    {
        $user = $this->userWithRole('administration');
        ProductType::create(['name' => 'Default', 'code' => 'default']);

        $response = $this->actingAs($user)->postJson('/api/catalog/product-types', [
            'name' => 'Another Default',
            'code' => 'default',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['code']);
    }

    public function test_user_can_view_single_product_type(): void
    {
        $user = $this->userWithRole('administration');
        $pt = ProductType::create(['name' => 'Tablets', 'code' => 'tablets']);
        $group = AttributeGroup::create(['name' => 'General', 'code' => 'general']);
        $pt->groups()->create(['attribute_group_id' => $group->id, 'position' => 1]);

        $response = $this->actingAs($user)->getJson("/api/catalog/product-types/{$pt->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $pt->id)
            ->assertJsonPath('data.name', 'Tablets')
            ->assertJsonPath('data.code', 'tablets')
            ->assertJsonPath('data.groups_count', 1);
    }

    public function test_user_can_update_product_type_and_sync_mappings(): void
    {
        $user = $this->userWithRole('administration');

        $group1 = AttributeGroup::create(['name' => 'General', 'code' => 'general']);
        $group2 = AttributeGroup::create(['name' => 'Technical', 'code' => 'technical']);
        $attr1 = Attribute::create(['name' => 'SKU', 'code' => 'sku', 'type' => 'text']);
        $attr2 = Attribute::create(['name' => 'RAM', 'code' => 'ram', 'type' => 'text']);

        $pt = ProductType::create(['name' => 'Old Name', 'code' => 'old_code']);
        $g = $pt->groups()->create(['attribute_group_id' => $group1->id, 'position' => 1]);
        $g->groupAttributes()->create(['attribute_id' => $attr1->id, 'position' => 1]);

        // Update product type with new group and moved attribute
        $payload = [
            'name' => 'Updated Laptops',
            'code' => 'laptops',
            'groups' => [
                [
                    'attribute_group_id' => $group2->id,
                    'position' => 1,
                    'attributes' => [
                        ['id' => $attr1->id, 'position' => 1],
                        ['id' => $attr2->id, 'position' => 2],
                    ],
                ],
            ],
        ];

        $response = $this->actingAs($user)->putJson("/api/catalog/product-types/{$pt->id}", $payload);

        $response->assertOk()
            ->assertJsonPath('data.name', 'Updated Laptops')
            ->assertJsonPath('data.code', 'laptops')
            ->assertJsonPath('data.groups_count', 1)
            ->assertJsonPath('data.attributes_count', 2);

        $this->assertDatabaseHas('product_types', [
            'id' => $pt->id,
            'name' => 'Updated Laptops',
            'code' => 'laptops',
        ]);
    }

    public function test_user_can_delete_product_type_and_cascades(): void
    {
        $user = $this->userWithRole('administration');

        $pt = ProductType::create(['name' => 'Obsolete Type', 'code' => 'obsolete']);
        $group = AttributeGroup::create(['name' => 'General', 'code' => 'general']);
        $ptg = $pt->groups()->create(['attribute_group_id' => $group->id, 'position' => 1]);
        $attr = Attribute::create(['name' => 'SKU', 'code' => 'sku', 'type' => 'text']);
        $ptg->groupAttributes()->create(['attribute_id' => $attr->id, 'position' => 1]);

        $response = $this->actingAs($user)->deleteJson("/api/catalog/product-types/{$pt->id}");

        $response->assertNoContent();
        $this->assertDatabaseMissing('product_types', ['id' => $pt->id]);
        $this->assertDatabaseMissing('product_type_groups', ['product_type_id' => $pt->id]);
        $this->assertDatabaseMissing('product_type_group_attributes', ['product_type_group_id' => $ptg->id]);

        // Base attribute and attribute group still exist
        $this->assertDatabaseHas('attributes', ['id' => $attr->id]);
        $this->assertDatabaseHas('attribute_groups', ['id' => $group->id]);
    }

    public function test_default_seeder_creates_default_product_type(): void
    {
        // First ensure default groups exist
        AttributeGroup::create(['name' => 'General', 'code' => 'general']);
        AttributeGroup::create(['name' => 'Description', 'code' => 'description']);
        AttributeGroup::create(['name' => 'Money', 'code' => 'money']);

        $this->seed(DefaultProductTypeSeeder::class);

        $this->assertDatabaseHas('product_types', [
            'name' => 'Default',
            'code' => 'default',
        ]);
    }

    public function test_user_can_create_product_type_with_multi_level_variants(): void
    {
        $user = $this->userWithRole('administration');

        $colorAttr = Attribute::create(['name' => 'Color', 'code' => 'color', 'type' => 'select']);
        $sizeAttr = Attribute::create(['name' => 'Size', 'code' => 'size', 'type' => 'select']);
        $group = AttributeGroup::create(['name' => 'General', 'code' => 'general']);

        $payload = [
            'name' => 'T-Shirts',
            'code' => 't_shirts',
            'groups' => [
                [
                    'attribute_group_id' => $group->id,
                    'position' => 1,
                    'attributes' => [
                        ['id' => $colorAttr->id, 'position' => 1],
                        ['id' => $sizeAttr->id, 'position' => 2],
                    ],
                ],
            ],
            'variants' => [
                [
                    'name' => '1-Level Color and Size',
                    'code' => 'color_size_1level',
                    'level_count' => 1,
                    'level_1_attributes' => [
                        ['id' => $colorAttr->id, 'position' => 1],
                        ['id' => $sizeAttr->id, 'position' => 2],
                    ],
                    'level_2_attributes' => [],
                ],
                [
                    'name' => '2-Level Color Group with Size SKU',
                    'code' => 'color_size_2level',
                    'level_count' => 2,
                    'level_1_attributes' => [
                        ['id' => $colorAttr->id, 'position' => 1],
                    ],
                    'level_2_attributes' => [
                        ['id' => $sizeAttr->id, 'position' => 1],
                    ],
                ],
            ],
        ];

        $response = $this->actingAs($user)->postJson('/api/catalog/product-types', $payload);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'T-Shirts')
            ->assertJsonPath('data.variants_count', 2)
            ->assertJsonPath('data.variants.0.code', 'color_size_1level')
            ->assertJsonPath('data.variants.0.level_count', 1)
            ->assertJsonPath('data.variants.0.level_1_attributes.0.code', 'color')
            ->assertJsonPath('data.variants.0.level_1_attributes.1.code', 'size')
            ->assertJsonPath('data.variants.1.code', 'color_size_2level')
            ->assertJsonPath('data.variants.1.level_count', 2)
            ->assertJsonPath('data.variants.1.level_1_attributes.0.code', 'color')
            ->assertJsonPath('data.variants.1.level_2_attributes.0.code', 'size');

        $pt = ProductType::where('code', 't_shirts')->first();
        $this->assertNotNull($pt);
        $this->assertCount(2, $pt->variants);

        $this->assertDatabaseHas('product_type_variants', [
            'product_type_id' => $pt->id,
            'code' => 'color_size_1level',
            'level_count' => 1,
        ]);

        $this->assertDatabaseHas('product_type_variants', [
            'product_type_id' => $pt->id,
            'code' => 'color_size_2level',
            'level_count' => 2,
        ]);
    }

    public function test_user_can_update_product_type_and_sync_variants(): void
    {
        $user = $this->userWithRole('administration');

        $color = Attribute::create(['name' => 'Color', 'code' => 'color', 'type' => 'select']);
        $storage = Attribute::create(['name' => 'Storage', 'code' => 'storage', 'type' => 'select']);

        $pt = ProductType::create(['name' => 'Smartphones', 'code' => 'smartphones']);
        $v1 = $pt->variants()->create([
            'name' => 'Initial Variant',
            'code' => 'initial_var',
            'level_count' => 1,
        ]);
        $v1->axes()->create(['level' => 1, 'attribute_id' => $color->id, 'position' => 1]);

        // Update to 2-level variant: Group by Storage, Variant by Color
        $response = $this->actingAs($user)->putJson("/api/catalog/product-types/{$pt->id}", [
            'name' => 'Smartphones',
            'code' => 'smartphones',
            'groups' => [],
            'variants' => [
                [
                    'name' => 'Storage Group with Color SKU',
                    'code' => 'storage_color_2level',
                    'level_count' => 2,
                    'level_1_attributes' => [
                        ['id' => $storage->id, 'position' => 1],
                    ],
                    'level_2_attributes' => [
                        ['id' => $color->id, 'position' => 1],
                    ],
                ],
            ],
        ]);

        $response->assertOk()
            ->assertJsonPath('data.variants_count', 1)
            ->assertJsonPath('data.variants.0.code', 'storage_color_2level')
            ->assertJsonPath('data.variants.0.level_count', 2)
            ->assertJsonPath('data.variants.0.level_1_attributes.0.code', 'storage')
            ->assertJsonPath('data.variants.0.level_2_attributes.0.code', 'color');

        $this->assertDatabaseHas('product_type_variants', [
            'product_type_id' => $pt->id,
            'code' => 'storage_color_2level',
            'level_count' => 2,
        ]);
        $this->assertDatabaseMissing('product_type_variants', [
            'product_type_id' => $pt->id,
            'code' => 'initial_var',
        ]);
    }

    public function test_deleting_product_type_cascades_multi_level_variants_and_axes(): void
    {
        $user = $this->userWithRole('administration');

        $color = Attribute::create(['name' => 'Color', 'code' => 'color', 'type' => 'select']);
        $pt = ProductType::create(['name' => 'Apparel', 'code' => 'apparel']);
        $v = $pt->variants()->create([
            'name' => 'Apparel Variant',
            'code' => 'apparel_var',
            'level_count' => 1,
        ]);
        $axis = $v->axes()->create(['level' => 1, 'attribute_id' => $color->id, 'position' => 1]);

        $response = $this->actingAs($user)->deleteJson("/api/catalog/product-types/{$pt->id}");

        $response->assertNoContent();
        $this->assertDatabaseMissing('product_types', ['id' => $pt->id]);
        $this->assertDatabaseMissing('product_type_variants', ['id' => $v->id]);
        $this->assertDatabaseMissing('product_type_variant_axes', ['id' => $axis->id]);

        // Attribute itself is untouched
        $this->assertDatabaseHas('attributes', ['id' => $color->id]);
    }

    public function test_user_can_create_variant_profile_with_variant_level_attributes(): void
    {
        $user = $this->userWithRole('administration');

        $color = Attribute::create(['name' => 'Color', 'code' => 'color', 'type' => 'select']);
        $size = Attribute::create(['name' => 'Size', 'code' => 'size', 'type' => 'select']);
        $price = Attribute::create(['name' => 'Price', 'code' => 'price', 'type' => 'price']);
        $weight = Attribute::create(['name' => 'Weight', 'code' => 'weight', 'type' => 'text']);

        $payload = [
            'name' => 'Smartphones',
            'code' => 'smartphones',
            'groups' => [],
            'variants' => [
                [
                    'name' => 'Color & Size with Pricing',
                    'code' => 'color_size_pricing',
                    'level_count' => 2,
                    'level_1_attributes' => [['id' => $color->id, 'position' => 1]],
                    'level_2_attributes' => [['id' => $size->id, 'position' => 1]],
                    'variant_attributes' => [
                        ['id' => $price->id],
                        ['id' => $weight->id],
                    ],
                ],
            ],
        ];

        $response = $this->actingAs($user)->postJson('/api/catalog/product-types', $payload);

        $response->assertCreated()
            ->assertJsonPath('data.variants.0.code', 'color_size_pricing')
            ->assertJsonPath('data.variants.0.level_count', 2)
            ->assertJsonPath('data.variants.0.variant_attributes.0.code', 'price')
            ->assertJsonPath('data.variants.0.variant_attributes.1.code', 'weight');

        $this->assertDatabaseHas('product_type_variant_attributes', [
            'attribute_id' => $price->id,
        ]);
        $this->assertDatabaseHas('product_type_variant_attributes', [
            'attribute_id' => $weight->id,
        ]);
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
