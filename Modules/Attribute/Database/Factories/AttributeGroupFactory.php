<?php

namespace Modules\Attribute\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Attribute\Models\AttributeGroup;

/**
 * @extends Factory<AttributeGroup>
 */
class AttributeGroupFactory extends Factory
{
    protected $model = AttributeGroup::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'name' => Str::title($name),
            'code' => Str::slug($name, '_'),
        ];
    }
}
