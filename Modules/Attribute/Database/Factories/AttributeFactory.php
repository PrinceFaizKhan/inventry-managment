<?php

namespace Modules\Attribute\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Attribute\AttributeCodeGenerator;
use Modules\Attribute\Models\Attribute;

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
            'is_filterable' => false,
            'settings' => ['validation' => 'any'],
        ];
    }

    public function filterable(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_filterable' => true,
        ]);
    }
}
