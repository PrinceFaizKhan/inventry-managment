<?php

namespace Tests\Feature;

use Tests\TestCase;

class SpaFallbackTest extends TestCase
{
    public function test_spa_pages_render_when_requested_directly(): void
    {
        foreach (['/settings/users', '/settings/roles/create'] as $path) {
            $this->get($path)
                ->assertOk()
                ->assertSee('<div id="app"></div>', false);
        }
    }

    public function test_fallback_does_not_capture_unknown_api_routes_or_non_get_requests(): void
    {
        $this->getJson('/api/not-a-route')->assertNotFound();
        $this->post('/not-a-page')->assertMethodNotAllowed();
    }
}
