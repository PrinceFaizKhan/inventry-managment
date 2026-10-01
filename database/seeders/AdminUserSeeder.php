<?php

namespace Database\Seeders;

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
        User::query()->updateOrCreate(
            ['email' => 'admin@haseentelecom.test'],
            [
                'name' => 'Haseen Ahmed',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ],
        );
    }
}
