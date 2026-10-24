<?php

namespace Modules\ProductTypes\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Modules\Attribute\Models\Attribute;
use Modules\Attribute\Models\AttributeGroup;
use Modules\ProductTypes\Database\Factories\ProductTypeFactory;

#[Fillable(['name', 'code'])]
class ProductType extends Model
{
    /** @use HasFactory<ProductTypeFactory> */
    use HasFactory;

    protected static function newFactory(): ProductTypeFactory
    {
        return ProductTypeFactory::new();
    }

    /**
     * Get the ordered groups configured on this product type.
     */
    public function groups(): HasMany
    {
        return $this->hasMany(ProductTypeGroup::class)->orderBy('position')->orderBy('id');
    }

    /**
     * Get the attribute groups attached to this product type.
     */
    public function attributeGroups(): BelongsToMany
    {
        return $this->belongsToMany(AttributeGroup::class, 'product_type_groups')
            ->withPivot(['id', 'position'])
            ->withTimestamps()
            ->orderByPivot('position');
    }

    /**
     * Get all attribute mappings through groups for fast counting/relations.
     */
    public function allGroupAttributes(): HasManyThrough
    {
        return $this->hasManyThrough(
            ProductTypeGroupAttribute::class,
            ProductTypeGroup::class,
            'product_type_id',
            'product_type_group_id',
            'id',
            'id'
        );
    }

    /**
     * Get all attributes associated with this product type across all groups.
     */
    public function attributes(): BelongsToMany
    {
        return $this->belongsToMany(Attribute::class, 'product_type_group_attributes', 'product_type_group_id', 'attribute_id', 'id', 'id')
            ->join('product_type_groups', 'product_type_groups.id', '=', 'product_type_group_attributes.product_type_group_id')
            ->where('product_type_groups.product_type_id', $this->id);
    }

    /**
     * Get the variant types (family variants) configured for this product type.
     */
    public function variants(): HasMany
    {
        return $this->hasMany(ProductTypeVariant::class)->orderBy('id');
    }
}
