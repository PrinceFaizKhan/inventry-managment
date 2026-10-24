<?php

namespace Modules\Attribute\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Attribute\Models\AttributeGroup;

class AttributeGroupTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $groups = [
            [
                'code' => 'general',
                'name' => 'General',
            ],
            [
                'code' => 'description',
                'name' => 'Description',
            ],
            [
                'code' => 'money',
                'name' => 'Money',
            ],
        ];

        foreach ($groups as $group) {
            AttributeGroup::firstOrCreate(
                ['code' => $group['code']],
                ['name' => $group['name']]
            );
        }
    }
}
