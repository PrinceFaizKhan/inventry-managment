<?php

namespace Modules\ProductTypes\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class StoreProductTypeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Prepare inputs for validation.
     */
    protected function prepareForValidation(): void
    {
        if (empty($this->code) && filled($this->name)) {
            $this->merge([
                'code' => Str::slug($this->name, '_'),
            ]);
        } elseif (is_string($this->code)) {
            $this->merge([
                'code' => Str::lower(trim($this->code)),
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'code' => ['required', 'string', 'max:120', 'regex:/^[a-zA-Z0-9_-]+$/', 'unique:product_types,code'],
            'groups' => ['nullable', 'array'],
            'groups.*.attribute_group_id' => ['required', 'exists:attribute_groups,id'],
            'groups.*.position' => ['nullable', 'integer'],
            'groups.*.attributes' => ['nullable', 'array'],
            'groups.*.attributes.*.id' => ['required', 'exists:attributes,id'],
            'groups.*.attributes.*.position' => ['nullable', 'integer'],
            'variants' => ['nullable', 'array'],
            'variants.*.name' => ['required', 'string', 'max:120'],
            'variants.*.code' => ['required', 'string', 'max:120'],
            'variants.*.level_count' => ['required', 'integer', 'in:1,2'],
            'variants.*.level_1_attributes' => ['nullable', 'array'],
            'variants.*.level_1_attributes.*.id' => ['required', 'exists:attributes,id'],
            'variants.*.level_1_attributes.*.position' => ['nullable', 'integer'],
            'variants.*.level_2_attributes' => ['nullable', 'array'],
            'variants.*.level_2_attributes.*.id' => ['required', 'exists:attributes,id'],
            'variants.*.level_2_attributes.*.position' => ['nullable', 'integer'],
        ];
    }
}
