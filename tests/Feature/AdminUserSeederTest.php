<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminUserSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_assigns_administration_access_to_existing_admin_without_resetting_credentials(): void
    {
        $existingPassword = Hash::make('a-custom-password');
        $administrator = User::factory()->create([
            'email' => 'admin@haseentelecom.test',
            'password' => $existingPassword,
            'name' => 'Existing administrator',
        ]);

        app(AdminUserSeeder::class)->run();

        $administrator->refresh();
        $this->assertSame('Existing administrator', $administrator->name);
        $this->assertTrue(Hash::check('a-custom-password', $administrator->password));
        $this->assertSame('administration', $administrator->role->access);
        $this->assertSame([], $administrator->role->permissions->getArrayCopy());
        $this->assertSame(1, User::query()->where('email', 'admin@haseentelecom.test')->count());
        $this->assertSame(1, Role::query()->where('name', 'Administration')->count());

        $this->actingAs($administrator)->getJson('/api/navigation')
            ->assertOk()
            ->assertJsonPath('data.1.children.0.key', 'catalog.attributes');
    }
}
