<?php

namespace Modules\Catalog\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Catalog\Models\Attribute;
use Modules\Catalog\Models\AttributeOption;

/**
 * @extends Factory<AttributeOption>
 */
class AttributeOptionFactory extends Factory
{
    protected $model = AttributeOption::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'attribute_id' => Attribute::factory(),
            'code' => fake()->unique()->slug(2),
            'label' => fake()->words(2, true),
            'sort_order' => fake()->numberBetween(0, 99),
        ];
    }
}
