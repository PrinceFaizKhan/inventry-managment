<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\Support\Connector\ConnectorRegistry;
use Modules\Core\Support\ViewEvent\ViewRenderEventManager;
use Modules\Product\Models\Product;
use Modules\Product\Models\ProductConnector;
use Modules\ProductTypes\Models\ProductType;
use Tests\TestCase;

class ProductConnectorTest extends TestCase
{
    use RefreshDatabase;

    protected function adminUser(): User
    {
        $role = Role::create([
            'name' => 'Admin Role',
            'access' => 'administration',
            'permissions' => [],
        ]);

        return User::factory()->create([
            'role_id' => $role->id,
        ]);
    }

    public function test_product_edit_page_renders_connectors_container(): void
    {
        $user = $this->adminUser();

        app(ConnectorRegistry::class)->register([
            'code' => 'pos_terminal',
            'name' => 'POS Terminal',
            'description' => 'Retail counter billing terminal',
            'badge' => 'POS',
        ]);

        $family = ProductType::create(['name' => 'Electronics', 'code' => 'electronics']);
        $product = Product::create([
            'sku' => 'PHONE-X1',
            'type' => 'simple',
            'product_type_id' => $family->id,
            'status' => true,
        ]);

        $response = $this->actingAs($user)->get("/catalog/products/{$product->id}/edit");

        $response->assertOk()
            ->assertSee('Published Modules')
            ->assertSee('Publish product to installed independent modules')
            ->assertSee('v-dropdown-multiselect', false)
            ->assertSee('available-connectors', false)
            ->assertSee('pos_terminal');
    }

    public function test_product_can_be_assigned_multiple_connectors_via_api(): void
    {
        $user = $this->adminUser();

        $family = ProductType::create(['name' => 'Footwear', 'code' => 'footwear']);
        $product = Product::create([
            'sku' => 'SHOE-001',
            'type' => 'simple',
            'product_type_id' => $family->id,
            'status' => true,
        ]);

        $payload = [
            'status' => true,
            'values' => [],
            'connectors' => ['web_store', 'shopify', 'amazon'],
        ];

        $response = $this->actingAs($user)->putJson("/api/catalog/products/{$product->id}", $payload);

        $response->assertOk()
            ->assertJsonPath('success', true);

        $this->assertEqualsCanonicalizing(
            ['web_store', 'shopify', 'amazon'],
            $response->json('data.connectors')
        );

        $this->assertDatabaseHas('product_connectors', [
            'product_id' => $product->id,
            'connector_code' => 'web_store',
        ]);
        $this->assertDatabaseHas('product_connectors', [
            'product_id' => $product->id,
            'connector_code' => 'shopify',
        ]);
        $this->assertDatabaseHas('product_connectors', [
            'product_id' => $product->id,
            'connector_code' => 'amazon',
        ]);

        $fresh = $product->fresh();
        $this->assertTrue($fresh->hasConnector('shopify'));
        $this->assertTrue($fresh->hasConnector('web_store'));
        $this->assertFalse($fresh->hasConnector('pos_counter'));
        $this->assertEqualsCanonicalizing(['web_store', 'shopify', 'amazon'], $fresh->connectorCodes());
    }

    public function test_product_connectors_can_be_updated_and_removed(): void
    {
        $user = $this->adminUser();

        $family = ProductType::create(['name' => 'Apparel', 'code' => 'apparel']);
        $product = Product::create([
            'sku' => 'HOODIE-RED',
            'type' => 'simple',
            'product_type_id' => $family->id,
            'status' => true,
        ]);

        // Pre-assign connectors
        ProductConnector::create([
            'product_id' => $product->id,
            'connector_code' => 'shopify',
            'status' => 'active',
        ]);
        ProductConnector::create([
            'product_id' => $product->id,
            'connector_code' => 'web_store',
            'status' => 'active',
        ]);

        $this->assertCount(2, $product->connectors);

        // Update with only pos_counter
        $response = $this->actingAs($user)->putJson("/api/catalog/products/{$product->id}", [
            'status' => true,
            'connectors' => ['pos_counter'],
        ]);

        $response->assertOk()
            ->assertJsonPath('data.connectors', ['pos_counter']);

        $this->assertDatabaseMissing('product_connectors', [
            'product_id' => $product->id,
            'connector_code' => 'shopify',
        ]);
        $this->assertDatabaseMissing('product_connectors', [
            'product_id' => $product->id,
            'connector_code' => 'web_store',
        ]);
        $this->assertDatabaseHas('product_connectors', [
            'product_id' => $product->id,
            'connector_code' => 'pos_counter',
        ]);

        $this->assertEquals(['pos_counter'], $product->fresh()->connectorCodes());
    }

    public function test_independent_connector_module_can_register_new_connector(): void
    {
        $user = $this->adminUser();

        $registry = app(ConnectorRegistry::class);
        $registry->register([
            'code' => 'daraz_pk',
            'name' => 'Daraz PK Marketplace',
            'description' => 'Publish products to Daraz South Asia marketplace',
            'badge' => 'Daraz',
            'active' => true,
        ]);

        $this->assertTrue($registry->has('daraz_pk'));
        $this->assertEquals('Daraz PK Marketplace', $registry->get('daraz_pk')['name']);

        $family = ProductType::create(['name' => 'Gadgets', 'code' => 'gadgets']);
        $product = Product::create([
            'sku' => 'EARBUDS-PRO',
            'type' => 'simple',
            'product_type_id' => $family->id,
            'status' => true,
        ]);

        // Assign newly registered connector to product
        $response = $this->actingAs($user)->putJson("/api/catalog/products/{$product->id}", [
            'connectors' => ['daraz_pk'],
        ]);

        $response->assertOk()
            ->assertJsonPath('data.connectors', ['daraz_pk']);

        $this->assertTrue($product->fresh()->hasConnector('daraz_pk'));
    }

    public function test_view_event_render_right_sidebar_connectors_hook_fires(): void
    {
        $user = $this->adminUser();

        $family = ProductType::create(['name' => 'Cosmetics', 'code' => 'cosmetics']);
        $product = Product::create([
            'sku' => 'LIPSTICK-01',
            'type' => 'simple',
            'product_type_id' => $family->id,
            'status' => true,
        ]);

        $manager = app(ViewRenderEventManager::class);
        $manager->listen('catalog.product.edit.right_sidebar.connectors', function ($params) use ($product) {
            $this->assertEquals($product->id, $params['product']->id);

            return '<div id="injected-connector-sync-status">All connectors synchronized</div>';
        });

        $response = $this->actingAs($user)->get("/catalog/products/{$product->id}/edit");

        $response->assertOk()
            ->assertSee('injected-connector-sync-status')
            ->assertSee('All connectors synchronized');
    }
}
