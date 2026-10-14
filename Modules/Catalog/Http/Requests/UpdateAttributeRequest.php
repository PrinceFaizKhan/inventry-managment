<?php

namespace Modules\Catalog\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use Modules\Catalog\Attributes\AttributeTypeRegistry;
use Modules\Catalog\Models\Attribute;

class UpdateAttributeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(AttributeTypeRegistry $types): array
    {
        /** @var Attribute $attribute */
        $attribute = $this->route('attribute');
        $rules = [
            'name' => ['required', 'string', 'max:120'],
            'required' => ['required', 'boolean'],
            'is_filterable' => ['sometimes', 'boolean'],
            'is_filter' => ['sometimes', 'boolean'],
            'code' => ['prohibited'],
            'type' => ['prohibited'],
            'settings' => ['sometimes', 'array'],
        ];

        $settingsFields = $types->definitions()[$attribute->type]['settings'] ?? [];
        foreach (array_keys($settingsFields) as $field) {
            $rules["settings.$field"] = ['sometimes'];
        }

        if (in_array($attribute->type, ['select', 'multiselect', 'radio', 'checkbox'], true)) {
            $rules['options'] = ['required', 'array', 'max:100'];
            $rules['options.*.id'] = ['sometimes', 'nullable', 'integer', 'min:1', 'distinct'];
            $rules['options.*.code'] = ['required', 'string', 'max:120', 'regex:/^[a-z][a-z0-9_]*$/', 'distinct'];
            $rules['options.*.label'] = ['required', 'string', 'max:120'];
            $rules['options.*.sort_order'] = ['required', 'integer', 'min:0', 'distinct'];
        } else {
            $rules['options'] = ['prohibited'];
        }

        return $rules;
    }

    public function after(AttributeTypeRegistry $types): array
    {
        return [function (Validator $validator) use ($types): void {
            if ($validator->errors()->isNotEmpty() || ! $this->has('settings')) {
                return;
            }

            /** @var Attribute $attribute */
            $attribute = $this->route('attribute');
            $types->validateSettings($attribute->type, $this->input('settings', []));
        }];
    }
}
