<?php

namespace Modules\Catalog\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Catalog\Attributes\AttributeCodeGenerator;
use Modules\Catalog\Models\Attribute;

/**
 * @extends Factory<Attribute>
 */
class AttributeFactory extends Factory
{
    protected $model = Attribute::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'name' => $name,
            'code' => AttributeCodeGenerator::fromName($name),
            'type' => 'text',
            'required' => false,
            'settings' => ['validation' => 'any'],
        ];
    }
}
