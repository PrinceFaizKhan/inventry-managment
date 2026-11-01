<?php

namespace Modules\Attribute\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Modules\Attribute\AttributeCodeGenerator;
use Modules\Attribute\AttributeTypeRegistry;
use Modules\Attribute\Models\Attribute;

class StoreAttributeRequest extends FormRequest
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
        return [
            'name' => ['required', 'string', 'max:120'],
            'type' => ['required', 'string', Rule::in($types->keys())],
            'code' => ['prohibited'],
            'required' => ['prohibited'],
            'is_unique' => ['prohibited'],
            'is_filterable' => ['prohibited'],
            'is_filter' => ['prohibited'],
            'settings' => ['prohibited'],
            'options' => ['prohibited'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $code = AttributeCodeGenerator::fromName($this->string('name')->toString());
            if ($code === '' || ! preg_match('/^[a-z][a-z0-9_]*$/', $code)) {
                $validator->errors()->add('code', 'This name does not produce a valid attribute code.');
            } elseif (Attribute::query()->where('code', $code)->exists()) {
                $validator->errors()->add('code', 'This attribute code already exists. Choose a different name.');
            }
        }];
    }

    public function messages(): array
    {
        return ['name.required' => 'Enter an attribute name.'];
    }
}
