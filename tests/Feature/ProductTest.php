<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Attribute\Models\Attribute;
use Modules\Attribute\Models\AttributeGroup;
use Modules\Product\Models\Product;
use Modules\Product\Models\ProductAttributeValue;
use Modules\ProductTypes\Models\ProductType;
use Modules\ProductTypes\Models\ProductTypeVariant;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    protected function userWithRole(string $access = 'administration', array $permissions = []): User
    {
        $role = Role::create([
            'name' => 'Admin Role',
            'access' => $access,
            'permissions' => $permissions,
        ]);

        return User::factory()->create([
            'role_id' => $role->id,
        ]);
    }

    public function test_user_can_view_products_page(): void
    {
        $user = $this->userWithRole('administration');

        $response = $this->actingAs($user)->get('/catalog/products');

        $response->assertOk()
            ->assertSee('Products')
            ->assertSee('Create Product');
    }

    public function test_user_can_query_products_grid(): void
    {
        $user = $this->userWithRole('administration');

        $family = ProductType::create(['name' => 'Electronics', 'code' => 'electronics']);
        Product::create([
            'sku' => 'ELEC-001',
            'type' => 'simple',
            'product_type_id' => $family->id,
            'status' => true,
        ]);

        $response = $this->actingAs($user)->getJson('/api/grids/products');

        $response->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.sku', 'ELEC-001')
            ->assertJsonPath('data.0.type', 'simple')
            ->assertJsonPath('data.0.family', 'Electronics');
    }

    public function test_user_can_create_simple_product_via_popup(): void
    {
        $user = $this->userWithRole('administration');
        $family = ProductType::create(['name' => 'Books', 'code' => 'books']);

        $payload = [
            'sku' => 'BOOK-PHP-83',
            'product_type_id' => $family->id,
            'type' => 'simple',
        ];

        $response = $this->actingAs($user)->postJson('/api/catalog/products', $payload);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.sku', 'BOOK-PHP-83')
            ->assertJsonPath('data.type', 'simple');

        $this->assertDatabaseHas('products', [
            'sku' => 'BOOK-PHP-83',
            'type' => 'simple',
            'product_type_id' => $family->id,
            'status' => 1,
        ]);
    }

    public function test_user_can_create_configurable_product_with_variant_level(): void
    {
        $user = $this->userWithRole('administration');
        $family = ProductType::create(['name' => 'Clothing', 'code' => 'clothing']);

        $variant = ProductTypeVariant::create([
            'product_type_id' => $family->id,
            'name' => 'Color & Size',
            'code' => 'color_size',
            'level_count' => 1,
        ]);

        $payload = [
            'sku' => 'TSHIRT-SUMMER-2026',
            'product_type_id' => $family->id,
            'type' => 'configurable',
            'product_type_variant_id' => $variant->id,
        ];

        $response = $this->actingAs($user)->postJson('/api/catalog/products', $payload);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.sku', 'TSHIRT-SUMMER-2026')
            ->assertJsonPath('data.type', 'configurable')
            ->assertJsonPath('data.product_type_variant_id', $variant->id);

        $this->assertDatabaseHas('products', [
            'sku' => 'TSHIRT-SUMMER-2026',
            'type' => 'configurable',
            'product_type_id' => $family->id,
            'product_type_variant_id' => $variant->id,
        ]);
    }

    public function test_cannot_create_configurable_product_if_family_has_no_variant_profiles(): void
    {
        $user = $this->userWithRole('administration');
        // Family with NO variant profiles defined
        $family = ProductType::create(['name' => 'Services', 'code' => 'services']);

        $payload = [
            'sku' => 'SRV-INSTALL',
            'product_type_id' => $family->id,
            'type' => 'configurable',
        ];

        $response = $this->actingAs($user)->postJson('/api/catalog/products', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['type']);
    }

    public function test_cannot_create_product_with_duplicate_sku(): void
    {
        $user = $this->userWithRole('administration');
        $family = ProductType::create(['name' => 'Default', 'code' => 'default']);

        Product::create([
            'sku' => 'DUPLICATE-SKU',
            'type' => 'simple',
            'product_type_id' => $family->id,
        ]);

        $payload = [
            'sku' => 'DUPLICATE-SKU',
            'product_type_id' => $family->id,
            'type' => 'simple',
        ];

        $response = $this->actingAs($user)->postJson('/api/catalog/products', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['sku']);
    }

    public function test_cannot_create_configurable_product_without_variant_profile(): void
    {
        $user = $this->userWithRole('administration');
        $family = ProductType::create(['name' => 'Apparel', 'code' => 'apparel']);
        ProductTypeVariant::create([
            'product_type_id' => $family->id,
            'name' => 'Size Only',
            'code' => 'size_only',
            'level_count' => 1,
        ]);

        $payload = [
            'sku' => 'JEANS-001',
            'product_type_id' => $family->id,
            'type' => 'configurable',
            'product_type_variant_id' => null,
        ];

        $response = $this->actingAs($user)->postJson('/api/catalog/products', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['product_type_variant_id']);
    }

    public function test_cannot_create_configurable_product_with_variant_from_other_family(): void
    {
        $user = $this->userWithRole('administration');
        $familyA = ProductType::create(['name' => 'Family A', 'code' => 'family_a']);
        $familyB = ProductType::create(['name' => 'Family B', 'code' => 'family_b']);

        $variantOfB = ProductTypeVariant::create([
            'product_type_id' => $familyB->id,
            'name' => 'Variant of B',
            'code' => 'var_b',
            'level_count' => 1,
        ]);

        $payload = [
            'sku' => 'PROD-MISMATCH',
            'product_type_id' => $familyA->id,
            'type' => 'configurable',
            'product_type_variant_id' => $variantOfB->id,
        ];

        $response = $this->actingAs($user)->postJson('/api/catalog/products', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['product_type_variant_id']);
    }

    public function test_user_can_view_product_edit_page(): void
    {
        $user = $this->userWithRole('administration');
        $family = ProductType::create(['name' => 'Smartphones', 'code' => 'smartphones']);
        $group = AttributeGroup::create(['name' => 'Specs', 'code' => 'specs']);
        $attr = Attribute::create(['name' => 'RAM', 'code' => 'ram', 'type' => 'text', 'required' => false]);

        $ptGroup = $family->groups()->create(['attribute_group_id' => $group->id, 'position' => 1]);
        $ptGroup->groupAttributes()->create(['attribute_id' => $attr->id, 'position' => 1]);

        $product = Product::create([
            'sku' => 'PHONE-X1',
            'type' => 'simple',
            'product_type_id' => $family->id,
            'status' => true,
        ]);

        $response = $this->actingAs($user)->get("/catalog/products/{$product->id}/edit");

        $response->assertOk()
            ->assertSee('PHONE-X1')
            ->assertSee('Smartphones');
    }

    public function test_user_can_update_product_status_and_attribute_values(): void
    {
        $user = $this->userWithRole('administration');
        $family = ProductType::create(['name' => 'Laptops', 'code' => 'laptops']);
        $attr = Attribute::create(['name' => 'Processor', 'code' => 'processor', 'type' => 'text', 'required' => false]);

        $product = Product::create([
            'sku' => 'LAPTOP-PRO',
            'type' => 'simple',
            'product_type_id' => $family->id,
            'status' => true,
        ]);

        $updatePayload = [
            'status' => false,
            'values' => [
                $attr->id => 'Intel Core Ultra 9',
            ],
        ];

        $response = $this->actingAs($user)->putJson("/api/catalog/products/{$product->id}", $updatePayload);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', false);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'status' => 0,
        ]);

        $this->assertDatabaseHas('product_attribute_values', [
            'product_id' => $product->id,
            'attribute_id' => $attr->id,
            'text_value' => 'Intel Core Ultra 9',
        ]);
    }

    public function test_user_can_delete_product(): void
    {
        $user = $this->userWithRole('administration');
        $family = ProductType::create(['name' => 'Monitors', 'code' => 'monitors']);
        $attr = Attribute::create(['name' => 'Resolution', 'code' => 'resolution', 'type' => 'text', 'required' => false]);

        $product = Product::create([
            'sku' => 'MONITOR-4K',
            'type' => 'simple',
            'product_type_id' => $family->id,
        ]);

        ProductAttributeValue::create([
            'product_id' => $product->id,
            'attribute_id' => $attr->id,
            'text_value' => '3840x2160',
        ]);

        $response = $this->actingAs($user)->deleteJson("/api/catalog/products/{$product->id}");

        $response->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
        $this->assertDatabaseMissing('product_attribute_values', ['product_id' => $product->id]);
    }

    public function test_user_can_filter_products_by_family(): void
    {
        $user = $this->userWithRole('administration');

        $familyPhones = ProductType::create(['name' => 'Phones', 'code' => 'phones']);
        $familyLaptops = ProductType::create(['name' => 'Laptops', 'code' => 'laptops']);

        Product::create([
            'sku' => 'IPHONE-15',
            'type' => 'simple',
            'product_type_id' => $familyPhones->id,
        ]);

        Product::create([
            'sku' => 'MACBOOK-PRO',
            'type' => 'simple',
            'product_type_id' => $familyLaptops->id,
        ]);

        $response = $this->actingAs($user)->getJson("/api/grids/products?filter[family]={$familyPhones->id}");

        $response->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.sku', 'IPHONE-15')
            ->assertJsonMissing(['sku' => 'MACBOOK-PRO']);
    }

    public function test_user_can_filter_products_by_filterable_attribute(): void
    {
        $user = $this->userWithRole('administration');

        $family = ProductType::create(['name' => 'Gadgets', 'code' => 'gadgets']);
        $colorAttr = Attribute::create([
            'name' => 'Color',
            'code' => 'color',
            'type' => 'select',
            'required' => false,
            'is_filterable' => true,
        ]);

        $redOption = $colorAttr->options()->create(['code' => 'red', 'label' => 'Red', 'sort_order' => 1]);
        $blueOption = $colorAttr->options()->create(['code' => 'blue', 'label' => 'Blue', 'sort_order' => 2]);

        $prodRed = Product::create([
            'sku' => 'GADGET-RED',
            'type' => 'simple',
            'product_type_id' => $family->id,
        ]);
        ProductAttributeValue::create([
            'product_id' => $prodRed->id,
            'attribute_id' => $colorAttr->id,
            'integer_value' => $redOption->id,
        ]);

        $prodBlue = Product::create([
            'sku' => 'GADGET-BLUE',
            'type' => 'simple',
            'product_type_id' => $family->id,
        ]);
        ProductAttributeValue::create([
            'product_id' => $prodBlue->id,
            'attribute_id' => $colorAttr->id,
            'integer_value' => $blueOption->id,
        ]);

        // Filter by Red
        $response = $this->actingAs($user)->getJson("/api/grids/products?filter[color]={$redOption->id}");

        $response->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.sku', 'GADGET-RED')
            ->assertJsonMissing(['sku' => 'GADGET-BLUE']);
    }

    public function test_configurable_product_edit_page_provides_scoped_attributes_and_child_variants(): void
    {
        $user = $this->userWithRole('administration');

        $family = ProductType::create(['name' => 'Phones', 'code' => 'phones']);
        $group = AttributeGroup::create(['name' => 'General', 'code' => 'general']);
        $color = Attribute::create(['name' => 'Color', 'code' => 'color', 'type' => 'select']);
        $brand = Attribute::create(['name' => 'Brand', 'code' => 'brand', 'type' => 'text']);
        $price = Attribute::create(['name' => 'Price', 'code' => 'price', 'type' => 'price']);

        $ptGroup = $family->groups()->create(['attribute_group_id' => $group->id, 'position' => 1]);
        $ptGroup->groupAttributes()->create(['attribute_id' => $brand->id, 'position' => 1]);
        $ptGroup->groupAttributes()->create(['attribute_id' => $color->id, 'position' => 2]);
        $ptGroup->groupAttributes()->create(['attribute_id' => $price->id, 'position' => 3]);

        $variantProfile = $family->variants()->create([
            'name' => 'Color Variant',
            'code' => 'color_var',
            'level_count' => 1,
        ]);
        $variantProfile->axes()->create(['level' => 1, 'attribute_id' => $color->id, 'position' => 1]);
        $variantProfile->variantAttributes()->attach($price->id);

        $parentProduct = Product::create([
            'sku' => 'Samsung a20',
            'type' => 'configurable',
            'product_type_id' => $family->id,
            'product_type_variant_id' => $variantProfile->id,
            'status' => true,
        ]);

        $response = $this->actingAs($user)->get("/catalog/products/{$parentProduct->id}/edit");

        $response->assertOk()
            ->assertSee('Samsung a20')
            ->assertSee('Parent Level')
            ->assertSee('Variant Level')
            ->assertSee('Child Variants Matrix');
    }

    public function test_user_can_sync_configurable_product_child_variants_with_sku_tags(): void
    {
        $user = $this->userWithRole('administration');

        $family = ProductType::create(['name' => 'Mobiles', 'code' => 'mobiles']);
        $color = Attribute::create(['name' => 'Color', 'code' => 'color', 'type' => 'select']);
        $blackOpt = $color->options()->create(['code' => 'black', 'label' => 'Black', 'sort_order' => 1]);
        $size = Attribute::create(['name' => 'Size', 'code' => 'size', 'type' => 'select']);
        $sizeOpt = $size->options()->create(['code' => '6inch', 'label' => '6inch', 'sort_order' => 1]);

        $price = Attribute::create(['name' => 'Price', 'code' => 'price', 'type' => 'price']);

        $variantProfile = $family->variants()->create([
            'name' => 'Color and Size',
            'code' => 'color_size',
            'level_count' => 2,
        ]);
        $variantProfile->axes()->create(['level' => 1, 'attribute_id' => $color->id, 'position' => 1]);
        $variantProfile->axes()->create(['level' => 2, 'attribute_id' => $size->id, 'position' => 2]);
        $variantProfile->variantAttributes()->attach($price->id);

        $parentProduct = Product::create([
            'sku' => 'Samsung a20',
            'type' => 'configurable',
            'product_type_id' => $family->id,
            'product_type_variant_id' => $variantProfile->id,
            'status' => true,
        ]);

        $payload = [
            'sku' => 'Samsung a20',
            'status' => true,
            'variants' => [
                [
                    'sku' => 'Samsung a20-black-6inch',
                    'status' => true,
                    'axes_values' => [
                        $color->id => $blackOpt->id,
                        $size->id => $sizeOpt->id,
                    ],
                    'values' => [
                        $price->id => 199.99,
                    ],
                ],
            ],
        ];

        $response = $this->actingAs($user)->putJson("/api/catalog/products/{$parentProduct->id}", $payload);

        $response->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('products', [
            'parent_id' => $parentProduct->id,
            'sku' => 'Samsung a20-black-6inch',
            'type' => 'simple',
            'status' => 1,
        ]);

        $childProduct = Product::where('sku', 'Samsung a20-black-6inch')->first();
        $this->assertNotNull($childProduct);

        // Verify child product attribute values (color, size, price)
        $this->assertDatabaseHas('product_attribute_values', [
            'product_id' => $childProduct->id,
            'attribute_id' => $color->id,
            'integer_value' => $blackOpt->id,
        ]);
        $this->assertDatabaseHas('product_attribute_values', [
            'product_id' => $childProduct->id,
            'attribute_id' => $size->id,
            'integer_value' => $sizeOpt->id,
        ]);
        $this->assertDatabaseHas('product_attribute_values', [
            'product_id' => $childProduct->id,
            'attribute_id' => $price->id,
            'float_value' => 199.99,
        ]);
    }
}
