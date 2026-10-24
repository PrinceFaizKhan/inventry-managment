<?php

namespace Modules\ProductTypes\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\ProductTypes\Models\ProductType;

/**
 * @extends Factory<ProductType>
 */
class ProductTypeFactory extends Factory
{
    protected $model = ProductType::class;

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
