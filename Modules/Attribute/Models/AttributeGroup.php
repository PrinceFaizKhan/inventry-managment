<?php

namespace Modules\Attribute\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Attribute\Database\Factories\AttributeGroupFactory;

#[Fillable(['name', 'code'])]
class AttributeGroup extends Model
{
    /** @use HasFactory<AttributeGroupFactory> */
    use HasFactory;

    protected static function newFactory(): AttributeGroupFactory
    {
        return AttributeGroupFactory::new();
    }
}
