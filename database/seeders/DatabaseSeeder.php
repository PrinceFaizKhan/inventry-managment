<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Attribute\Database\Seeders\AttributeGroupTableSeeder;
use Modules\ProductTypes\Database\Seeders\DefaultProductTypeSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AdminUserSeeder::class,
            AttributeGroupTableSeeder::class,
            DefaultProductTypeSeeder::class,
        ]);
    }
}
