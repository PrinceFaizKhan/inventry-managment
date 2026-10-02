<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Create the initial local administrator account.
     */
    public function run(): void
    {
        $administrationRole = Role::query()->updateOrCreate(
            ['name' => 'Administration'],
            ['access' => 'administration', 'permissions' => []],
        );

        $administrator = User::query()->firstOrCreate(
            ['email' => 'admin@haseentelecom.test'],
            [
                'name' => 'Haseen Ahmed',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ],
        );

        $administrator->role()->associate($administrationRole);
        $administrator->save();
    }
}
