<?php

namespace Modules\Product\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Product\Models\Product;
use Modules\ProductTypes\Models\ProductType;
use Modules\ProductTypes\Models\ProductTypeVariant;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'sku' => 'PRD-'.strtoupper(Str::random(8)),
            'type' => 'simple',
            'product_type_id' => ProductType::factory(),
            'product_type_variant_id' => null,
            'parent_id' => null,
            'status' => true,
        ];
    }

    public function configurable(?ProductTypeVariant $variant = null): self
    {
        return $this->state(function (array $attributes) use ($variant) {
            $productTypeId = $variant ? $variant->product_type_id : $attributes['product_type_id'];

            return [
                'type' => 'configurable',
                'product_type_id' => $productTypeId,
                'product_type_variant_id' => $variant?->id,
            ];
        });
    }

    public function inactive(): self
    {
        return $this->state(fn () => ['status' => false]);
    }
}
