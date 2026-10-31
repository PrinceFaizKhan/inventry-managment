<?php

namespace Modules\Product\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Modules\ProductTypes\Models\ProductType;
use Modules\ProductTypes\Models\ProductTypeVariant;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sku' => [
                'required',
                'string',
                'max:120',
                'regex:/^[a-zA-Z0-9_\-\. ]+$/',
                Rule::unique('products', 'sku'),
            ],
            'product_type_id' => [
                'required',
                'integer',
                Rule::exists('product_types', 'id'),
            ],
            'type' => [
                'required',
                'string',
                Rule::in(['simple', 'configurable']),
            ],
            'product_type_variant_id' => [
                'nullable',
                'integer',
                Rule::requiredIf(fn () => $this->input('type') === 'configurable'),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'sku.regex' => 'The SKU may only contain letters, numbers, spaces, dashes, dots, and underscores.',
            'sku.unique' => 'A product with this SKU already exists.',
            'product_type_id.required' => 'Please select a product family.',
            'product_type_id.exists' => 'The selected product family is invalid.',
            'product_type_variant_id.required_if' => 'Please select a variant type for the configurable product.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $productTypeId = $this->input('product_type_id');
            $type = $this->input('type');
            $variantId = $this->input('product_type_variant_id');

            if (! $productTypeId) {
                return;
            }

            /** @var ProductType|null $family */
            $family = ProductType::withCount('variants')->find($productTypeId);

            if (! $family) {
                return;
            }

            // If the family has no variant levels, only "simple" product is allowed
            if ($family->variants_count === 0 && $type === 'configurable') {
                $validator->errors()->add(
                    'type',
                    'The selected family has no variant levels configured and can only be created as a Simple product.'
                );
            }

            // If configurable, ensure the variant profile belongs to the selected family
            if ($type === 'configurable' && $variantId) {
                $variantExists = ProductTypeVariant::query()
                    ->where('id', $variantId)
                    ->where('product_type_id', $productTypeId)
                    ->exists();

                if (! $variantExists) {
                    $validator->errors()->add(
                        'product_type_variant_id',
                        'The selected variant level does not belong to the selected family.'
                    );
                }
            }
        });
    }
}
