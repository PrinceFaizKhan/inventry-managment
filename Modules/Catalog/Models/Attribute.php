<?php

namespace Modules\Catalog\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Catalog\Database\Factories\AttributeFactory;

#[Fillable(['name', 'code', 'type', 'required', 'settings'])]
class Attribute extends Model
{
    /** @use HasFactory<AttributeFactory> */
    use HasFactory;

    protected static function newFactory(): AttributeFactory
    {
        return AttributeFactory::new();
    }

    protected function casts(): array
    {
        return [
            'required' => 'boolean',
            'settings' => AsArrayObject::class,
        ];
    }

    public function options(): HasMany
    {
        return $this->hasMany(AttributeOption::class)->orderBy('sort_order')->orderBy('id');
    }
}
