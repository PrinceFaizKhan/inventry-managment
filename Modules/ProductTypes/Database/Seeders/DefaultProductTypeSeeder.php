<?php

namespace Modules\ProductTypes\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Attribute\Models\Attribute;
use Modules\Attribute\Models\AttributeGroup;
use Modules\ProductTypes\Models\ProductType;
use Modules\ProductTypes\Models\ProductTypeGroup;
use Modules\ProductTypes\Models\ProductTypeGroupAttribute;

class DefaultProductTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultType = ProductType::firstOrCreate(
            ['code' => 'default'],
            ['name' => 'Default']
        );

        $groups = AttributeGroup::query()->orderBy('id')->get();
        $attributes = Attribute::query()->orderBy('id')->get();

        $position = 1;
        foreach ($groups as $group) {
            $typeGroup = ProductTypeGroup::firstOrCreate(
                [
                    'product_type_id' => $defaultType->id,
                    'attribute_group_id' => $group->id,
                ],
                [
                    'position' => $position++,
                ]
            );

            // Assign standard attributes to General group initially
            if ($group->code === 'general' && $attributes->isNotEmpty()) {
                $attrPos = 1;
                foreach ($attributes as $attribute) {
                    ProductTypeGroupAttribute::firstOrCreate(
                        [
                            'product_type_group_id' => $typeGroup->id,
                            'attribute_id' => $attribute->id,
                        ],
                        [
                            'position' => $attrPos++,
                        ]
                    );
                }
            }
        }
    }
}
