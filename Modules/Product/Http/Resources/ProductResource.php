<?php

namespace Modules\Product\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Product\Models\Product;

/**
 * @mixin Product
 */
class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sku' => $this->sku,
            'type' => $this->type,
            'status' => (bool) $this->status,
            'product_type_id' => $this->product_type_id,
            'product_type' => $this->relationLoaded('productType') && $this->productType ? [
                'id' => $this->productType->id,
                'name' => $this->productType->name,
                'code' => $this->productType->code,
            ] : null,
            'product_type_variant_id' => $this->product_type_variant_id,
            'product_type_variant' => $this->relationLoaded('productTypeVariant') && $this->productTypeVariant ? [
                'id' => $this->productTypeVariant->id,
                'name' => $this->productTypeVariant->name,
                'code' => $this->productTypeVariant->code,
                'level_count' => $this->productTypeVariant->level_count,
            ] : null,
            'parent_id' => $this->parent_id,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
