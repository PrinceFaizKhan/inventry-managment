<?php

namespace Modules\ProductTypes\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Attribute\Models\Attribute;

#[Fillable(['product_type_group_id', 'attribute_id', 'position'])]
class ProductTypeGroupAttribute extends Model
{
    use HasFactory;

    /**
     * Parent Product Type Group.
     */
    public function productTypeGroup(): BelongsTo
    {
        return $this->belongsTo(ProductTypeGroup::class);
    }

    /**
     * Assigned Attribute.
     */
    public function attribute(): BelongsTo
    {
        return $this->belongsTo(Attribute::class);
    }
}
