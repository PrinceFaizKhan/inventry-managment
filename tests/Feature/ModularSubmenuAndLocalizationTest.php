<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Admin\Providers\AdminServiceProvider;
use Modules\Attribute\Models\Attribute;
use Modules\Attribute\Providers\AttributeServiceProvider;
use Modules\Core\Support\Grid\GridRegistry;
use Modules\Core\Support\ViewEvent\ViewRenderEventManager;
use Modules\Settings\Users\Providers\UsersServiceProvider;
use Tests\TestCase;

class ModularSubmenuAndLocalizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_view_event_render_registers_and_renders_hooks(): void
    {
        $manager = app(ViewRenderEventManager::class);
        $manager->clear();

        $manager->listen('header.actions', fn () => '<button id="hook-btn">Action</button>');

        $rendered = view_event_render('header.actions');
        $this->assertStringContainsString('<button id="hook-btn">Action</button>', (string) $rendered);

        $empty = view_event_render('non_existent_hook');
        $this->assertSame('', (string) $empty);
    }

    public function test_view_event_render_supports_priority_ordering(): void
    {
        $manager = app(ViewRenderEventManager::class);
        $manager->clear();

        $manager->listen('content.slot', fn () => 'A', priority: 10);
        $manager->listen('content.slot', fn () => 'B', priority: 50);
        $manager->listen('content.slot', fn () => 'C', priority: 5);

        $rendered = (string) view_event_render('content.slot');
        $this->assertSame('BAC', $rendered);
    }

    public function test_sub_menu_service_providers_are_registered(): void
    {
        $this->assertTrue($this->app->providerIsLoaded(AdminServiceProvider::class));
        $this->assertTrue($this->app->providerIsLoaded(AttributeServiceProvider::class));
        $this->assertTrue($this->app->providerIsLoaded(UsersServiceProvider::class));
    }

    public function test_translations_api_returns_english(): void
    {
        $responseEn = $this->getJson('/api/translations')->assertOk();
        $responseEn->assertJsonPath('locale', 'en-IN');
        $responseEn->assertJsonPath('direction', 'ltr');
        $messagesEn = $responseEn->json('messages');
        $this->assertSame('Attributes', $messagesEn['catalog.attributes.title'] ?? null);
        $this->assertSame('The provided credentials are incorrect.', $messagesEn['admin.invalid_credentials'] ?? null);
    }

    public function test_grid_registry_resolves_independent_submenu_grids(): void
    {
        $registry = app(GridRegistry::class);

        $attributesGrid = $registry->find('attributes');
        $this->assertSame('attributes', $attributesGrid['key']);
        $this->assertSame('/api/grids/attributes', $attributesGrid['endpoint']);

        $usersGrid = $registry->find('users');
        $this->assertSame('users', $usersGrid['key']);
    }

    public function test_admin_auth_bootstrap_returns_current_admin(): void
    {
        // Unauthenticated returns null
        $this->getJson('/api/bootstrap')->assertOk()->assertJson(['user' => null]);

        // Authenticated returns AdminUserResource
        $user = User::factory()->create(['email' => 'admin@test.com']);
        $this->actingAs($user)
            ->getJson('/api/bootstrap')
            ->assertOk()
            ->assertJsonPath('user.email', 'admin@test.com');
    }

    public function test_attributes_blade_views_render_with_embedded_vue_components(): void
    {
        $role = Role::query()->create(['name' => 'Admin', 'access' => 'administration']);
        $user = User::factory()->create(['role_id' => $role->id]);

        // 1. Index page
        $indexRes = $this->actingAs($user)->get('/catalog/attributes')->assertOk();
        $indexRes->assertSee('<attributes-list></attributes-list>', false);
        $indexRes->assertSee('Khan Group');

        // 2. Create page
        $createRes = $this->actingAs($user)->get('/catalog/attributes/create')->assertOk();
        $createRes->assertSee('<attribute-create-modal', false);

        // 3. Edit page
        $attribute = Attribute::factory()->create(['name' => 'Screen Size', 'code' => 'screen_size', 'type' => 'text']);
        $editRes = $this->actingAs($user)->get("/catalog/attributes/{$attribute->id}/edit")->assertOk();
        $editRes->assertSee('<attribute-edit-form', false);
        $editRes->assertSee('Screen Size');
    }
}
