<?php

namespace Modules\Attribute\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Attribute\Database\Factories\AttributeGroupFactory;
use Modules\ProductTypes\Models\ProductTypeGroup;

#[Fillable(['name', 'code'])]
class AttributeGroup extends Model
{
    /** @use HasFactory<AttributeGroupFactory> */
    use HasFactory;

    protected static function newFactory(): AttributeGroupFactory
    {
        return AttributeGroupFactory::new();
    }

    /**
     * Product type group mappings.
     */
    public function productTypeGroups(): HasMany
    {
        return $this->hasMany(ProductTypeGroup::class, 'attribute_group_id');
    }
}
