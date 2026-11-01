<?php

namespace Modules\Attribute\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Attribute\Database\Factories\AttributeFactory;

#[Fillable(['name', 'code', 'type', 'required', 'is_unique', 'is_filterable', 'settings'])]
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
            'is_unique' => 'boolean',
            'is_filterable' => 'boolean',
            'settings' => AsArrayObject::class,
        ];
    }

    /**
     * Scope query to only include attributes configured for unique enforcement.
     *
     * @param  Builder<Attribute>  $query
     * @return Builder<Attribute>
     */
    public function scopeUnique(Builder $query): Builder
    {
        return $query->where('is_unique', true);
    }

    /**
     * Scope query to only include attributes configured for filtering.
     *
     * @param  Builder<Attribute>  $query
     * @return Builder<Attribute>
     */
    public function scopeFilterable(Builder $query): Builder
    {
        return $query->where('is_filterable', true);
    }

    public function getIsFilterAttribute(): bool
    {
        return (bool) $this->is_filterable;
    }

    public function setIsFilterAttribute(bool $value): void
    {
        $this->attributes['is_filterable'] = $value;
    }

    public function options(): HasMany
    {
        return $this->hasMany(AttributeOption::class)->orderBy('sort_order')->orderBy('id');
    }
}
