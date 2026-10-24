<?php

namespace Modules\ProductTypes\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Attribute\Models\Attribute;
use Modules\Attribute\Models\AttributeGroup;

#[Fillable(['product_type_id', 'attribute_group_id', 'position'])]
class ProductTypeGroup extends Model
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
     * Associated Attribute Group definition.
     */
    public function attributeGroup(): BelongsTo
    {
        return $this->belongsTo(AttributeGroup::class);
    }

    /**
     * Assigned attribute mapping pivot records.
     */
    public function groupAttributes(): HasMany
    {
        return $this->hasMany(ProductTypeGroupAttribute::class)->orderBy('position')->orderBy('id');
    }

    /**
     * Direct attributes relationship.
     */
    public function attributes(): BelongsToMany
    {
        return $this->belongsToMany(Attribute::class, 'product_type_group_attributes')
            ->withPivot(['id', 'position'])
            ->withTimestamps()
            ->orderByPivot('position');
    }
}
