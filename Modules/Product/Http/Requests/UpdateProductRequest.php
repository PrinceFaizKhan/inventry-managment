<?php

namespace Modules\Product\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Modules\Attribute\Models\Attribute;
use Modules\Product\Models\Product;
use Modules\Product\Models\ProductAttributeValue;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var Product|null $product */
        $product = $this->route('product');
        $productId = $product instanceof Product ? $product->id : $product;

        return [
            'sku' => [
                'sometimes',
                'required',
                'string',
                'max:120',
                'regex:/^[a-zA-Z0-9_\-\. ]+$/',
                Rule::unique('products', 'sku')->ignore($productId),
            ],
            'status' => [
                'sometimes',
                'boolean',
            ],
            'values' => [
                'sometimes',
                'array',
            ],
            'variants' => [
                'sometimes',
                'array',
            ],
            'variants.*.id' => [
                'nullable',
                'integer',
            ],
            'variants.*.sku' => [
                'required_with:variants',
                'string',
                'max:120',
                'regex:/^[a-zA-Z0-9_\-\. ]+$/',
            ],
            'variants.*.status' => [
                'nullable',
                'boolean',
            ],
            'variants.*.axes_values' => [
                'nullable',
                'array',
            ],
            'variants.*.values' => [
                'nullable',
                'array',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'sku.regex' => 'The SKU may only contain letters, numbers, spaces, dashes, dots, and underscores.',
            'sku.unique' => 'A product with this SKU already exists.',
            'variants.*.sku.regex' => 'Variant SKU may only contain letters, numbers, spaces, dashes, dots, and underscores.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            /** @var Product|null $product */
            $product = $this->route('product');
            $productId = $product instanceof Product ? $product->id : (int) $product;

            $parentValues = $this->input('values', []);
            $variants = $this->input('variants', []);

            $attrIds = [];
            if (is_array($parentValues)) {
                $attrIds = array_keys($parentValues);
            }
            if (is_array($variants)) {
                foreach ($variants as $v) {
                    if (! empty($v['axes_values']) && is_array($v['axes_values'])) {
                        $attrIds = array_merge($attrIds, array_keys($v['axes_values']));
                    }
                    if (! empty($v['values']) && is_array($v['values'])) {
                        $attrIds = array_merge($attrIds, array_keys($v['values']));
                    }
                }
            }

            $attrIds = array_unique(array_filter($attrIds, fn ($id) => is_numeric($id)));
            if (empty($attrIds)) {
                return;
            }

            $uniqueAttributes = Attribute::query()
                ->whereIn('id', $attrIds)
                ->where('is_unique', true)
                ->get()
                ->keyBy('id');

            if ($uniqueAttributes->isEmpty()) {
                return;
            }

            $seenValues = [];

            if (is_array($parentValues)) {
                foreach ($parentValues as $attrId => $val) {
                    if ($val === null || $val === '' || (is_array($val) && empty($val))) {
                        continue;
                    }
                    $uniqueAttr = $uniqueAttributes->get((int) $attrId);
                    if (! $uniqueAttr) {
                        continue;
                    }

                    $normVal = is_scalar($val) ? (string) $val : json_encode($val);
                    $seenValues[$uniqueAttr->id][] = $normVal;

                    $this->checkDatabaseUniqueness($validator, "values.{$attrId}", $uniqueAttr, $val, [$productId]);
                }
            }

            if (is_array($variants)) {
                foreach ($variants as $vIdx => $vData) {
                    $childId = ! empty($vData['id']) ? (int) $vData['id'] : null;
                    $excludedIds = array_filter([$productId, $childId]);

                    $allChildValues = [];
                    if (! empty($vData['axes_values']) && is_array($vData['axes_values'])) {
                        foreach ($vData['axes_values'] as $k => $val) {
                            $allChildValues["axes_values.{$k}"] = [(int) $k, $val];
                        }
                    }
                    if (! empty($vData['values']) && is_array($vData['values'])) {
                        foreach ($vData['values'] as $k => $val) {
                            $allChildValues["values.{$k}"] = [(int) $k, $val];
                        }
                    }

                    foreach ($allChildValues as $fieldKey => [$attrId, $val]) {
                        if ($val === null || $val === '' || (is_array($val) && empty($val))) {
                            continue;
                        }
                        $uniqueAttr = $uniqueAttributes->get($attrId);
                        if (! $uniqueAttr) {
                            continue;
                        }

                        $normVal = is_scalar($val) ? (string) $val : json_encode($val);

                        if (in_array($normVal, $seenValues[$uniqueAttr->id] ?? [], true)) {
                            $validator->errors()->add("variants.{$vIdx}.{$fieldKey}", "The {$uniqueAttr->name} value '{$val}' is duplicated in this request.");
                        } else {
                            $seenValues[$uniqueAttr->id][] = $normVal;
                        }

                        $this->checkDatabaseUniqueness($validator, "variants.{$vIdx}.{$fieldKey}", $uniqueAttr, $val, $excludedIds);
                    }
                }
            }
        });
    }

    /**
     * @param  list<int>  $excludedProductIds
     */
    private function checkDatabaseUniqueness(
        Validator $validator,
        string $errorKey,
        Attribute $attribute,
        mixed $val,
        array $excludedProductIds
    ): void {
        $query = ProductAttributeValue::query()
            ->where('attribute_id', $attribute->id);

        if (! empty($excludedProductIds)) {
            $query->whereNotIn('product_id', $excludedProductIds);
        }

        match ($attribute->type) {
            'boolean' => $query->where('boolean_value', filter_var($val, FILTER_VALIDATE_BOOLEAN)),
            'price', 'money' => $query->where('float_value', (float) $val),
            'select', 'radio' => is_numeric($val)
                ? $query->where('integer_value', (int) $val)
                : $query->where('text_value', (string) $val),
            'date' => $query->where('date_value', Carbon::parse($val)->toDateString()),
            'datetime' => $query->where('datetime_value', Carbon::parse($val)),
            default => $query->where('text_value', (string) $val),
        };

        if ($query->exists()) {
            $validator->errors()->add($errorKey, "The {$attribute->name} value '{$val}' has already been taken.");
        }
    }
}
