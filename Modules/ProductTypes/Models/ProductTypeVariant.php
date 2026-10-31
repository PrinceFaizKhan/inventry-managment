<?php

namespace Modules\ProductTypes\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Attribute\Models\Attribute;

#[Fillable(['product_type_id', 'name', 'code', 'level_count'])]
class ProductTypeVariant extends Model
{
    use HasFactory;

    /**
     * Parent Product Type.
     */
    public function productType(): BelongsTo
    {
        return $this->belongsTo(ProductType::class);
    }

    /**
     * All axes (attributes assigned to levels).
     */
    public function axes(): HasMany
    {
        return $this->hasMany(ProductTypeVariantAxis::class)->orderBy('level')->orderBy('position')->orderBy('id');
    }

    /**
     * Level 1 axes (Variant Group level if 2-level, or direct variant if 1-level).
     */
    public function levelOneAxes(): HasMany
    {
        return $this->hasMany(ProductTypeVariantAxis::class)->where('level', 1)->orderBy('position')->orderBy('id');
    }

    /**
     * Level 2 axes (Final Variant level if 2-level).
     */
    public function levelTwoAxes(): HasMany
    {
        return $this->hasMany(ProductTypeVariantAxis::class)->where('level', 2)->orderBy('position')->orderBy('id');
    }

    /**
     * Attributes designated as variant-level (fillable on child variants).
     */
    public function variantAttributes(): BelongsToMany
    {
        return $this->belongsToMany(Attribute::class, 'product_type_variant_attributes')
            ->withTimestamps()
            ->orderBy('attributes.name');
    }
}
