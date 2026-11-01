<?php

namespace Modules\Product\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Attribute\Models\Attribute;
use Modules\Product\Database\Factories\ProductFactory;
use Modules\ProductTypes\Models\ProductType;
use Modules\ProductTypes\Models\ProductTypeVariant;

#[Fillable([
    'sku',
    'type',
    'product_type_id',
    'product_type_variant_id',
    'parent_id',
    'status',
])]
class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    protected static function newFactory(): ProductFactory
    {
        return ProductFactory::new();
    }

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
        ];
    }

    /**
     * Product family / type relation.
     */
    public function productType(): BelongsTo
    {
        return $this->belongsTo(ProductType::class, 'product_type_id');
    }

    /**
     * Configurable variant profile relation (if type is configurable).
     */
    public function productTypeVariant(): BelongsTo
    {
        return $this->belongsTo(ProductTypeVariant::class, 'product_type_variant_id');
    }

    /**
     * Parent product (if this is a child variant).
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * Child variants (if this is a configurable product).
     */
    public function variants(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /**
     * Attribute values for this product.
     */
    public function attributeValues(): HasMany
    {
        return $this->hasMany(ProductAttributeValue::class);
    }

    /**
     * Assigned independent connectors / channels for this product.
     */
    public function connectors(): HasMany
    {
        return $this->hasMany(ProductConnector::class, 'product_id');
    }

    /**
     * Get list of assigned connector codes.
     *
     * @return array<int, string>
     */
    public function connectorCodes(): array
    {
        return $this->connectors->pluck('connector_code')->values()->all();
    }

    /**
     * Check if product is assigned to a specific connector.
     */
    public function hasConnector(string $code): bool
    {
        return $this->connectors->contains('connector_code', $code);
    }

    /**
     * Sync assigned connectors.
     *
     * @param  array<int, string>  $codes
     */
    public function syncConnectors(array $codes): void
    {
        $uniqueCodes = array_values(array_unique(array_filter($codes)));

        // Remove connectors not in submitted list
        $this->connectors()->whereNotIn('connector_code', $uniqueCodes)->delete();

        // Add or keep connectors
        foreach ($uniqueCodes as $code) {
            $this->connectors()->firstOrCreate(
                ['connector_code' => $code],
                ['status' => true, 'sync_status' => 'synced']
            );
        }
    }

    public function isSimple(): bool
    {
        return $this->type === 'simple';
    }

    public function isConfigurable(): bool
    {
        return $this->type === 'configurable';
    }

    /**
     * Scope for active products.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true);
    }

    /**
     * Scope for parent products only (excluding child variants from top-level catalog list).
     */
    public function scopeParentsOnly(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Get single custom product attribute value by code or attribute model.
     */
    public function getProductAttributeValue(Attribute|string $attribute): mixed
    {
        $code = $attribute instanceof Attribute ? $attribute->code : $attribute;

        $record = $this->attributeValues->first(function ($val) use ($code) {
            return $val->attribute?->code === $code;
        });

        return $record?->getValue();
    }
}
