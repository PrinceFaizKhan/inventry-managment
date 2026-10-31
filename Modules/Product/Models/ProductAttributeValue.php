<?php

namespace Modules\Product\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Attribute\Models\Attribute;

#[Fillable([
    'product_id',
    'attribute_id',
    'text_value',
    'boolean_value',
    'integer_value',
    'float_value',
    'datetime_value',
    'date_value',
    'json_value',
])]
class ProductAttributeValue extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'boolean_value' => 'boolean',
            'json_value' => 'array',
            'datetime_value' => 'datetime',
            'date_value' => 'date',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(Attribute::class);
    }

    /**
     * Get typed value based on attribute type.
     */
    public function getValue(): mixed
    {
        $type = $this->attribute?->type ?? 'text';

        return match ($type) {
            'boolean' => $this->boolean_value,
            'select', 'radio' => $this->integer_value ?? $this->text_value,
            'multiselect', 'checkbox', 'gallery' => $this->json_value ?? [],
            'price', 'money' => $this->float_value,
            'date' => $this->date_value?->format('Y-m-d'),
            'datetime' => $this->datetime_value?->toIso8601String(),
            default => $this->text_value,
        };
    }

    /**
     * Store raw value into the appropriate typed column based on attribute type.
     */
    public function setValue(mixed $val, ?string $type = null): void
    {
        $type = $type ?? $this->attribute?->type ?? 'text';

        // Reset columns
        $this->text_value = null;
        $this->boolean_value = null;
        $this->integer_value = null;
        $this->float_value = null;
        $this->datetime_value = null;
        $this->date_value = null;
        $this->json_value = null;

        if ($val === null || $val === '') {
            return;
        }

        switch ($type) {
            case 'boolean':
                $this->boolean_value = filter_var($val, FILTER_VALIDATE_BOOLEAN);
                break;

            case 'select':
            case 'radio':
                if (is_numeric($val)) {
                    $this->integer_value = (int) $val;
                } else {
                    $this->text_value = (string) $val;
                }
                break;

            case 'multiselect':
            case 'checkbox':
            case 'gallery':
                $this->json_value = is_array($val) ? array_values($val) : [$val];
                break;

            case 'price':
            case 'money':
                $this->float_value = (float) $val;
                break;

            case 'date':
                $this->date_value = Carbon::parse($val)->toDateString();
                break;

            case 'datetime':
                $this->datetime_value = Carbon::parse($val);
                break;

            default:
                $this->text_value = is_scalar($val) ? (string) $val : json_encode($val);
                break;
        }
    }
}
