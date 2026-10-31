<?php

namespace Modules\Product\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Product\Models\Product;

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
}
