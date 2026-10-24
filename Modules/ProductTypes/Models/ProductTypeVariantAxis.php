<?php

namespace Modules\ProductTypes\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Attribute\Models\Attribute;

#[Fillable(['product_type_variant_id', 'level', 'attribute_id', 'position'])]
class ProductTypeVariantAxis extends Model
{
    use HasFactory;

    /**
     * Parent Product Type Variant.
     */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductTypeVariant::class, 'product_type_variant_id');
    }

    /**
     * Target Attribute.
     */
    public function attribute(): BelongsTo
    {
        return $this->belongsTo(Attribute::class);
    }
}
