<?php

namespace Modules\Catalog\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Catalog\Database\Factories\AttributeOptionFactory;

#[Fillable(['attribute_id', 'code', 'label', 'sort_order'])]
class AttributeOption extends Model
{
    /** @use HasFactory<AttributeOptionFactory> */
    use HasFactory;

    protected static function newFactory(): AttributeOptionFactory
    {
        return AttributeOptionFactory::new();
    }

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(Attribute::class);
    }
}
