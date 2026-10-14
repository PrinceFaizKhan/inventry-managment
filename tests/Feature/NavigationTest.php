<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_receives_configured_navigation(): void
    {
        $response = $this->actingAs(User::factory()->create())->getJson('/api/navigation');

        $response->assertOk()->assertJsonPath('data.0.key', 'dashboard');
        $this->assertSame('settings', $response->json('data.4.key'));
        $this->assertSame('settings.users', $response->json('data.4.children.0.key'));
    }

    public function test_navigation_requires_authentication(): void
    {
        $this->getJson('/api/navigation')->assertUnauthorized();
    }

    public function test_parent_linked_submenus_are_assembled_under_their_parents(): void
    {
        $role = Role::query()->create(['name' => 'Full Admin', 'access' => 'administration']);
        $user = User::factory()->create(['role_id' => $role->id]);

        $response = $this->actingAs($user)->getJson('/api/navigation')->assertOk();

        // Catalog root contains independent sub-menus
        $catalog = collect($response->json('data'))->firstWhere('key', 'catalog');
        $this->assertNotNull($catalog);
        $catalogChildren = array_column($catalog['children'], 'key');
        $this->assertContains('catalog.attributes', $catalogChildren);
        $this->assertContains('catalog.product-types', $catalogChildren);
        $this->assertContains('catalog.products', $catalogChildren);

        // Settings root contains independent sub-menus
        $settings = collect($response->json('data'))->firstWhere('key', 'settings');
        $this->assertNotNull($settings);
        $settingsChildren = array_column($settings['children'], 'key');
        $this->assertContains('settings.users', $settingsChildren);
        $this->assertContains('settings.roles', $settingsChildren);
    }
}
