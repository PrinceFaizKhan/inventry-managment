<?php

namespace Modules\Product\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'product_id',
    'connector_code',
    'status',
    'sync_status',
    'synced_at',
    'settings',
])]
class ProductConnector extends Model
{
    protected $table = 'product_connectors';

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
            'synced_at' => 'datetime',
            'settings' => 'array',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
